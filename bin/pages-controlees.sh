#!/usr/bin/env bash
#
# Choisit une page du site Local par gabarit du thème.
# Les scripts de contrôle lisent ce fichier par `.`, après environnement-local.sh.
# Il définit `accueil`, `introuvable` et le tableau `pages` : l'accueil, une page,
# un article, puis `introuvable`, une adresse qui n'existe pas, pour le gabarit 404.

accueil="$(wp option get home)/"
pages=("$accueil")

for type in page post; do
	url="$(wp post list --post_type="$type" --post_status=publish --posts_per_page=1 --orderby=ID --order=ASC --field=url)"
	if [ -n "$url" ]; then
		pages+=("$url")
	else
		echo "Aucun contenu publié de type $type : son gabarit n'est pas contrôlé." >&2
	fi
done

introuvable="${accueil}page-introuvable-avant-mise-en-ligne/"
pages+=("$introuvable")
