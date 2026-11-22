#!/usr/bin/env bash
#
# Éprouve la fermeture des commentaires sur le site Local, pour un article
# et pour une page. Le site doit tourner dans Local.
# Le test envoie un commentaire vide, que WordPress n'écrit jamais en base :
# WordPress répond 403 quand les commentaires sont fermés, et 200 sinon.
#
# Usage : tests/commentaires.test.sh

set -euo pipefail

theme="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=../bin/environnement-local.sh
. "$theme/bin/environnement-local.sh"

depot_des_commentaires="$(wp option get siteurl)/wp-comments-post.php"

echecs=0
controles=0

for type in post page; do
	identifiant="$(wp post list --post_type="$type" --post_status=publish --posts_per_page=1 --orderby=ID --order=ASC --field=ID)"
	if [ -z "$identifiant" ]; then
		echo "Aucun contenu publié de type $type : ses commentaires ne sont pas contrôlés." >&2
		continue
	fi
	adresse="$(wp post list --post__in="$identifiant" --post_type="$type" --field=url)"
	controles=$((controles + 1))

	code="$(curl --silent --output /dev/null --write-out '%{http_code}' \
		--data "comment_post_ID=$identifiant" --data "comment=" "$depot_des_commentaires")" || code="000"
	if [ "$code" != "403" ]; then
		echo "$adresse : un commentaire reçoit la réponse $code au lieu de 403." >&2
		echecs=1
	fi

	# WordPress annonce l'adresse des rétroliens par l'en-tête HTTP X-Pingback.
	entetes="$(curl --silent --show-error --output /dev/null --dump-header - "$adresse")" || entetes=""
	if ! grep -qi '^HTTP/' <<< "$entetes"; then
		echo "$adresse : les en-têtes de la réponse ne sont pas arrivés." >&2
		echecs=1
	elif grep -qi '^x-pingback:' <<< "$entetes"; then
		echo "$adresse : la réponse annonce l'adresse des rétroliens." >&2
		echecs=1
	fi
done

if [ "$controles" -eq 0 ]; then
	echo "Aucun contenu publié : le test n'a rien contrôlé." >&2
	exit 1
fi

if [ "$echecs" -ne 0 ]; then
	exit 1
fi

echo "commentaires : fermés sur $controles contenus."
