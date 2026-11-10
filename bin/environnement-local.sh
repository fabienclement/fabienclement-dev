#!/usr/bin/env bash
#
# Prépare le shell pour lancer le WP-CLI de Local sur ce site.
# Les scripts du thème lisent ce fichier par `.` : il définit `site_local`,
# exporte l'environnement que Local écrit dans app/.envrc, puis définit `wp`.

theme="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
site_local="$(cd "$theme/../../../../.." && pwd)"
envrc="$site_local/app/.envrc"

if [ ! -f "$envrc" ]; then
	echo "Aucun $envrc : Local l'écrit au premier démarrage du site. Démarre le site dans Local, puis relance." >&2
	exit 2
fi

# shellcheck source=/dev/null
. "$envrc"

socket="$(sed -n 's/^[[:space:]]*socket[[:space:]]*=[[:space:]]*//p' "$MYSQL_HOME/my.cnf" | head -1)"
if [ ! -S "$socket" ]; then
	echo "La base du site ne répond pas : $socket n'existe pas. Démarre le site dans Local, puis relance." >&2
	exit 2
fi
export MYSQL_UNIX_PORT="$socket"

wp() {
	command wp --path="$site_local/app/public" "$@"
}
