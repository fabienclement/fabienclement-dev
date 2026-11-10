<?php
/**
 * Lit un rapport JSON d'axe ou de Lighthouse, puis affiche ses échecs.
 *
 * WP-CLI lance ce fichier par `wp eval-file bin/mesure.php <outil> <rapport> [accueil]`.
 * L'outil vaut `axe` ou `lighthouse`. Le script sort en 1 quand le rapport
 * porte un échec.
 *
 * @package fabienclement-dev
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 1 );
}

require_once __DIR__ . '/mesures-fonctions.php';

/**
 * Les arguments que `wp eval-file` passe au script.
 *
 * @var list<string> $args
 */
$fabienclement_dev_outil   = $args[0] ?? '';
$fabienclement_dev_fichier = $args[1] ?? '';
$fabienclement_dev_role    = $args[2] ?? '';

// Le rapport est un fichier local, que l'outil vient d'écrire.
$fabienclement_dev_rapport = is_readable( $fabienclement_dev_fichier ) ? (string) file_get_contents( $fabienclement_dev_fichier ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

if ( 'axe' === $fabienclement_dev_outil ) {
	$fabienclement_dev_echecs = fabienclement_dev_violations_axe( $fabienclement_dev_rapport );
} elseif ( 'lighthouse' === $fabienclement_dev_outil ) {
	$fabienclement_dev_mesures = fabienclement_dev_mesures_lighthouse( $fabienclement_dev_rapport, 'accueil' === $fabienclement_dev_role );
	$fabienclement_dev_echecs  = $fabienclement_dev_mesures['echecs'];
	if ( '' !== $fabienclement_dev_mesures['mesures'] ) {
		WP_CLI::line( $fabienclement_dev_mesures['mesures'] );
	}
} else {
	$fabienclement_dev_echecs = array( 'Outil inconnu : bin/mesure.php attend axe ou lighthouse.' );
}

foreach ( $fabienclement_dev_echecs as $fabienclement_dev_echec ) {
	WP_CLI::line( $fabienclement_dev_echec );
}
if ( array() !== $fabienclement_dev_echecs ) {
	WP_CLI::halt( 1 );
}
