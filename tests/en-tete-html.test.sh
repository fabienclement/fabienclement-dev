#!/usr/bin/env bash
#
# Éprouve le HTML que le site Local sert, sur une page par gabarit du thème.
# Éprouve aussi les en-têtes HTTP « Link » de la réponse à chaque page.
# Éprouve aussi que chaque page précharge la police romaine du thème.
# Le site doit tourner dans Local. Le test ne fait que lire les pages.
# Chaque marque interdite est un texte que WordPress écrit par défaut,
# dans la page ou dans ses en-têtes HTTP, et que le thème retire dans functions.php.
#
# Usage : tests/en-tete-html.test.sh

set -euo pipefail

theme="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=../bin/environnement-local.sh
. "$theme/bin/environnement-local.sh"
# shellcheck source=../bin/pages-controlees.sh
. "$theme/bin/pages-controlees.sh"

marques_interdites=(
	# Le script des emojis porte un identifiant en « wp-emoji ».
	"wp-emoji"
	# Le style des emojis vise « img.emoji », avec ou sans identifiant.
	"img.emoji"
	# La balise qui affiche la version de WordPress.
	'name="generator"'
	# Le lien vers xmlrpc.php, pour les logiciels qui publient à distance.
	'rel="EditURI"'
	# Le lien court d'une page, en « ?p= ».
	"rel='shortlink'"
	# Le lien vers l'API REST, puis celui vers la page lue dans cette API.
	# « href= » écarte les balises script, que WordPress écrit avec le même type.
	'rel="https://api.w.org/"'
	'type="application/json" href='
)

# WordPress envoie aussi ces liens dans un en-tête HTTP « Link », hors du HTML.
liens_interdits_dans_les_en_tetes=(
	# Le lien vers l'API REST.
	'rel="https://api.w.org/"'
	# Le lien court d'une page, en « ?p= ».
	"rel=shortlink"
)

# Le seul flux RSS annoncé est celui des articles : aucun flux de commentaires.
flux_attendus=1

echecs=0

fichier_en_tetes="$(mktemp)"
trap 'rm -f "$fichier_en_tetes"' EXIT

for page in "${pages[@]}"; do
	html="$(curl --silent --show-error --dump-header "$fichier_en_tetes" "$page")" || html=""

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

	en_tetes_link="$(grep -i '^link:' "$fichier_en_tetes" || true)"
	for lien in "${liens_interdits_dans_les_en_tetes[@]}"; do
		if grep -qF "$lien" <<< "$en_tetes_link"; then
			echo "$page : un en-tête HTTP « Link » porte « $lien »." >&2
			echecs=1
		fi
	done

	# La balise de préchargement doit porter l'adresse du @font-face.
	# Sinon, le navigateur télécharge la police deux fois.
	police="$(grep -oE "url\('[^']*hanken-grotesk-romain\.woff2'\)" <<< "$html" | head -n 1 | sed -E "s/^url\('//; s/'\)$//" || true)"
	prechargement="<link rel=\"preload\" href=\"$police\" as=\"font\" type=\"font/woff2\" crossorigin>"
	if [ -z "$police" ] || ! grep -qF "$prechargement" <<< "$html"; then
		echo "$page : le HTML ne précharge pas la police romaine du @font-face." >&2
		echecs=1
	fi

	flux="$(grep -oF 'type="application/rss+xml"' <<< "$html" | wc -l | tr -d ' ')"
	if [ "$flux" -ne "$flux_attendus" ]; then
		echo "$page : le HTML annonce $flux flux RSS au lieu de $flux_attendus." >&2
		echecs=1
	fi
done

if [ "$echecs" -ne 0 ]; then
	exit 1
fi

echo "en-tete-html : vert sur ${#pages[@]} pages."
