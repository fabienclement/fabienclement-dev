<?php
/**
 * Liste les copies de gabarits et de styles globaux que la base garde.
 *
 * WP-CLI lance ce fichier par `wp eval-file`. Le script sort en 1 quand une
 * copie masque un fichier du thème, ou n'existe que dans la base.
 *
 * @package fabienclement-dev
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 1 );
}

require_once __DIR__ . '/surcharges-fonctions.php';

$fabienclement_dev_gabarits = array();
foreach ( array( 'wp_template', 'wp_template_part' ) as $fabienclement_dev_type ) {
	foreach ( get_block_templates( array(), $fabienclement_dev_type ) as $fabienclement_dev_gabarit ) {
		$fabienclement_dev_gabarits[] = array(
			'type'           => $fabienclement_dev_type,
			'slug'           => $fabienclement_dev_gabarit->slug,
			'source'         => $fabienclement_dev_gabarit->source,
			'has_theme_file' => (bool) $fabienclement_dev_gabarit->has_theme_file,
		);
	}
}

$fabienclement_dev_styles = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme() );
$fabienclement_dev_lignes = fabienclement_dev_surcharges(
	$fabienclement_dev_gabarits,
	is_string( $fabienclement_dev_styles['post_content'] ?? null ) ? $fabienclement_dev_styles['post_content'] : ''
);

if ( array() === $fabienclement_dev_lignes ) {
	WP_CLI::success( 'Aucune copie de la base ne masque le thème.' );
	return;
}

foreach ( $fabienclement_dev_lignes as $fabienclement_dev_ligne ) {
	WP_CLI::line( $fabienclement_dev_ligne );
}
WP_CLI::line( 'Pour chaque copie : exporte-la dans le thème avec Create Block Theme, ou réinitialise le gabarit dans l\'éditeur de site.' );
WP_CLI::halt( 1 );
