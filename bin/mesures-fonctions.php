<?php
/**
 * Lit les rapports JSON d'axe et de Lighthouse, puis rend leurs échecs.
 *
 * Ce fichier ne touche ni WordPress ni le navigateur : `bin/mesure.php` lit
 * le rapport sur le disque, puis passe son contenu à ces fonctions.
 *
 * @package fabienclement-dev
 */

/**
 * Rend une ligne par élément fautif d'une violation d'axe.
 *
 * @param string $rapport Le contenu du fichier que `axe --save` écrit.
 * @return list<string>
 */
function fabienclement_dev_violations_axe( string $rapport ): array {
	$pages = json_decode( $rapport, true );
	if ( ! is_array( $pages ) ) {
		return array( "Le rapport d'axe est illisible." );
	}

	$lignes = array();
	foreach ( $pages as $page ) {
		if ( ! is_array( $page ) || ! is_array( $page['violations'] ?? null ) ) {
			return array( "Le rapport d'axe est illisible." );
		}
		$url = is_string( $page['url'] ?? null ) ? $page['url'] : '?';
		foreach ( $page['violations'] as $violation ) {
			if ( ! is_array( $violation ) || ! is_array( $violation['nodes'] ?? null ) ) {
				return array( "Le rapport d'axe est illisible." );
			}
			$regle = sprintf(
				'%s : règle %s (%s)',
				$url,
				is_string( $violation['id'] ?? null ) ? $violation['id'] : '?',
				is_string( $violation['help'] ?? null ) ? $violation['help'] : '?'
			);
			foreach ( $violation['nodes'] as $element ) {
				$cible    = is_array( $element ) && is_array( $element['target'] ?? null ) ? $element['target'] : array();
				$lignes[] = sprintf( '%s, sur %s', $regle, implode( ' ', array_filter( $cible, 'is_string' ) ) );
			}
		}
	}
	return $lignes;
}

/**
 * Rend la ligne des mesures d'une page, et une ligne par seuil dépassé.
 *
 * @param string $rapport Le contenu du rapport JSON de Lighthouse.
 * @param bool   $accueil Vrai quand la page est l'accueil, seule page soumise aux 200 Kio.
 * @return array{mesures: string, echecs: list<string>}
 */
function fabienclement_dev_mesures_lighthouse( string $rapport, bool $accueil ): array {
	$donnees = json_decode( $rapport, true );
	$audits  = is_array( $donnees ) && is_array( $donnees['audits'] ?? null ) ? $donnees['audits'] : array();

	$valeurs = array();
	foreach ( array( 'largest-contentful-paint', 'cumulative-layout-shift', 'total-blocking-time', 'total-byte-weight' ) as $audit ) {
		$valeur = is_array( $audits[ $audit ] ?? null ) ? ( $audits[ $audit ]['numericValue'] ?? null ) : null;
		if ( ! is_int( $valeur ) && ! is_float( $valeur ) ) {
			return array(
				'mesures' => '',
				'echecs'  => array( 'Le rapport de Lighthouse ne porte pas les mesures attendues.' ),
			);
		}
		$valeurs[ $audit ] = (float) $valeur;
	}

	$url = is_string( $donnees['finalDisplayedUrl'] ?? null ) ? $donnees['finalDisplayedUrl'] : '?';
	$lcp = number_format( $valeurs['largest-contentful-paint'] / 1000, 1, ',', '' );
	$cls = number_format( $valeurs['cumulative-layout-shift'], 2, ',', '' );
	$kio = (int) round( $valeurs['total-byte-weight'] / 1024 );

	$echecs = array();
	if ( $valeurs['largest-contentful-paint'] > 2500 ) {
		$echecs[] = sprintf( '%s : LCP de %s s, au-dessus de 2,5 s.', $url, $lcp );
	}
	if ( $valeurs['cumulative-layout-shift'] > 0.1 ) {
		$echecs[] = sprintf( '%s : CLS de %s, au-dessus de 0,1.', $url, $cls );
	}
	if ( $accueil && $valeurs['total-byte-weight'] > 200 * 1024 ) {
		$echecs[] = sprintf( "%s : %d Kio transférés, au-dessus des 200 Kio de l'accueil.", $url, $kio );
	}

	return array(
		'mesures' => sprintf(
			'%s : LCP %s s, CLS %s, TBT %d ms, %d Kio transférés',
			$url,
			$lcp,
			$cls,
			(int) round( $valeurs['total-blocking-time'] ),
			$kio
		),
		'echecs'  => $echecs,
	);
}
