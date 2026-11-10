#!/usr/bin/env bash
#
# Éprouve bin/controle-surcharges sur la base du site Local.
# Le site doit tourner dans Local. Le test exporte la base dans app/sql/,
# crée une copie du gabarit index, puis supprime cette copie.
#
# Usage : tests/controle-surcharges.test.sh

set -euo pipefail

theme="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=../bin/environnement-local.sh
. "$theme/bin/environnement-local.sh"

echecs=0

code=0
sortie="$("$theme/bin/controle-surcharges" 2>&1)" || code=$?
if [ "$code" -ne 0 ]; then
	echo "Base propre : sortie en $code au lieu de 0." >&2
	echo "$sortie" >&2
	echecs=1
fi

wp db export "$site_local/app/sql/$(date +%Y-%m-%d-%H%M%S)-test-surcharges.sql" --quiet

copie="$(wp eval '
	$id = wp_insert_post(
		array(
			"post_type"    => "wp_template",
			"post_name"    => "index",
			"post_title"   => "Index",
			"post_status"  => "publish",
			"post_content" => "<!-- wp:paragraph --><p>Copie de test</p><!-- /wp:paragraph -->",
		)
	);
	wp_set_object_terms( $id, get_stylesheet(), "wp_theme" );
	echo $id;
')"
trap 'wp post delete "$copie" --force --quiet' EXIT

code=0
sortie="$("$theme/bin/controle-surcharges" 2>&1)" || code=$?
if [ "$code" -ne 1 ]; then
	echo "Copie du gabarit index : sortie en $code au lieu de 1." >&2
	echecs=1
fi
if ! grep -qF 'Le gabarit index masque templates/index.html.' <<< "$sortie"; then
	echo "Copie du gabarit index : la sortie ne la nomme pas." >&2
	echo "$sortie" >&2
	echecs=1
fi

if [ "$echecs" -ne 0 ]; then
	exit 1
fi

echo "controle-surcharges : vert."
