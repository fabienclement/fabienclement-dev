#!/usr/bin/env bash
#
# Éprouve le HTML que le site Local sert, sur une page par gabarit du thème.
# Le site doit tourner dans Local. Le test ne fait que lire les pages.
# Chaque marque interdite est un texte que WordPress écrit dans la page
# par défaut, et que le thème retire dans functions.php.
#
# Usage : tests/en-tete-html.test.sh

set -euo pipefail

theme="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=../bin/environnement-local.sh
. "$theme/bin/environnement-local.sh"
# shellcheck source=../bin/pages-controlees.sh
. "$theme/bin/pages-controlees.sh"

# Le script et le style des emojis portent tous un identifiant en « wp-emoji ».
marques_interdites=("wp-emoji")

echecs=0

for page in "${pages[@]}"; do
	html="$(curl --silent --show-error "$page")" || html=""

	# Une page vide ou coupée ne porte aucune marque : elle ne prouve rien.
	if ! grep -qF '</html>' <<< "$html"; then
		echo "$page : la page n'est pas arrivée entière." >&2
		echecs=1
		continue
	fi

	for marque in "${marques_interdites[@]}"; do
		if grep -qF "$marque" <<< "$html"; then
			echo "$page : le HTML porte « $marque »." >&2
			echecs=1
		fi
	done
done

if [ "$echecs" -ne 0 ]; then
	exit 1
fi

echo "en-tete-html : vert sur ${#pages[@]} pages."
