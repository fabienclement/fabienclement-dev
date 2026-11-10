<?php
/**
 * Décide quelles copies de la base masquent un fichier du thème.
 *
 * Ce fichier ne touche pas WordPress : `bin/surcharges.php` lit la base,
 * puis passe ce qu'il a lu à ces fonctions.
 *
 * @package fabienclement-dev
 */

/**
 * Rend une ligne par surcharge que la base garde.
 *
 * @param list<array{type: string, slug: string, source: string, has_theme_file: bool}> $gabarits Les gabarits et parties de gabarit du thème actif.
 * @param string                                                                        $styles   Le contenu JSON des styles globaux de la base, ou une chaîne vide.
 * @return list<string>
 */
function fabienclement_dev_surcharges( array $gabarits, string $styles ): array {
	$lignes = array();
	foreach ( $gabarits as $gabarit ) {
		if ( 'custom' !== $gabarit['source'] ) {
			continue;
		}
		$partie  = 'wp_template_part' === $gabarit['type'];
		$nom     = sprintf( $partie ? 'La partie de gabarit %s' : 'Le gabarit %s', $gabarit['slug'] );
		$fichier = sprintf( $partie ? 'parts/%s.html' : 'templates/%s.html', $gabarit['slug'] );

		$lignes[] = $gabarit['has_theme_file']
			? sprintf( '%s masque %s.', $nom, $fichier )
			: sprintf( "%s n'existe que dans la base.", $nom );
	}

	$donnees = json_decode( $styles, true );
	if ( is_array( $donnees ) && ( ! empty( $donnees['styles'] ) || ! empty( $donnees['settings'] ) ) ) {
		$lignes[] = 'Les styles globaux de la base masquent theme.json.';
	}
	return $lignes;
}
