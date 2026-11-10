<?php
/**
 * Tests de la lecture des rapports d'axe et de Lighthouse.
 *
 * @package fabienclement-dev
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/bin/mesures-fonctions.php';

/**
 * La lecture des rapports d'axe et de Lighthouse, sans WordPress ni navigateur.
 */
final class Mesures_Test extends TestCase {

	/**
	 * Encode en JSON, comme axe et Lighthouse écrivent leurs rapports.
	 *
	 * @param array<mixed> $donnees Les données du rapport.
	 */
	private function json( array $donnees ): string {
		// Les tests tournent sans WordPress, donc sans wp_json_encode().
		return (string) json_encode( $donnees ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	/**
	 * Chaque élément fautif d'une violation d'axe donne une ligne.
	 */
	public function test_une_violation_d_axe_nomme_sa_page_sa_regle_et_ses_elements(): void {
		$rapport = $this->json(
			array(
				array(
					'url'        => 'https://fabienclement-dev.local/',
					'violations' => array(
						array(
							'id'    => 'list',
							'help'  => '<ul> and <ol> must only directly contain <li>, <script> or <template> elements',
							'nodes' => array(
								array( 'target' => array( '.wp-block-navigation__container' ) ),
								array( 'target' => array( 'footer', 'ul' ) ),
							),
						),
					),
				),
			)
		);

		$this->assertSame(
			array(
				'https://fabienclement-dev.local/ : règle list (<ul> and <ol> must only directly contain <li>, <script> or <template> elements), sur .wp-block-navigation__container',
				'https://fabienclement-dev.local/ : règle list (<ul> and <ol> must only directly contain <li>, <script> or <template> elements), sur footer ul',
			),
			fabienclement_dev_violations_axe( $rapport )
		);
	}

	/**
	 * Une page sans violation ne donne aucune ligne.
	 */
	public function test_une_page_sans_violation_ne_donne_rien(): void {
		$rapport = $this->json(
			array(
				array(
					'url'        => 'https://fabienclement-dev.local/',
					'violations' => array(),
				),
			)
		);

		$this->assertSame( array(), fabienclement_dev_violations_axe( $rapport ) );
	}

	/**
	 * Rend un rapport de Lighthouse réduit aux mesures que le thème juge.
	 *
	 * @param float $lcp    Le LCP, en millisecondes.
	 * @param float $cls    Le CLS.
	 * @param float $tbt    Le TBT, en millisecondes.
	 * @param float $octets Le poids transféré, en octets.
	 */
	private function rapport_lighthouse( float $lcp, float $cls, float $tbt, float $octets ): string {
		return $this->json(
			array(
				'finalDisplayedUrl' => 'https://fabienclement-dev.local/',
				'audits'            => array(
					'largest-contentful-paint' => array( 'numericValue' => $lcp ),
					'cumulative-layout-shift'  => array( 'numericValue' => $cls ),
					'total-blocking-time'      => array( 'numericValue' => $tbt ),
					'total-byte-weight'        => array( 'numericValue' => $octets ),
				),
			)
		);
	}

	/**
	 * Une page sous les seuils ne donne aucun échec, et ses mesures s'affichent.
	 */
	public function test_une_page_sous_les_seuils_affiche_ses_mesures(): void {
		$this->assertSame(
			array(
				'mesures' => 'https://fabienclement-dev.local/ : LCP 1,5 s, CLS 0,00, TBT 450 ms, 82 Kio transférés',
				'echecs'  => array(),
			),
			fabienclement_dev_mesures_lighthouse( $this->rapport_lighthouse( 1501.4, 0, 450, 83982 ), true )
		);
	}

	/**
	 * Un LCP au-dessus de 2,5 s et un CLS au-dessus de 0,1 sont des échecs, et le TBT n'en est jamais un.
	 */
	public function test_le_lcp_et_le_cls_au_dessus_des_seuils_echouent(): void {
		$mesures = fabienclement_dev_mesures_lighthouse( $this->rapport_lighthouse( 2600, 0.15, 900, 1000 ), false );

		$this->assertSame(
			array(
				'https://fabienclement-dev.local/ : LCP de 2,6 s, au-dessus de 2,5 s.',
				'https://fabienclement-dev.local/ : CLS de 0,15, au-dessus de 0,1.',
			),
			$mesures['echecs']
		);
	}

	/**
	 * Les 200 Kio ne valent que pour l'accueil.
	 */
	public function test_les_200_kio_ne_valent_que_pour_l_accueil(): void {
		$lourd = $this->rapport_lighthouse( 1000, 0, 0, 210000 );

		$this->assertSame(
			array( "https://fabienclement-dev.local/ : 205 Kio transférés, au-dessus des 200 Kio de l'accueil." ),
			fabienclement_dev_mesures_lighthouse( $lourd, true )['echecs']
		);
		$this->assertSame( array(), fabienclement_dev_mesures_lighthouse( $lourd, false )['echecs'] );
	}

	/**
	 * Un rapport de Lighthouse sans mesure est un échec, pas une page propre.
	 */
	public function test_un_rapport_de_lighthouse_sans_mesure_est_un_echec(): void {
		$this->assertSame(
			array( 'Le rapport de Lighthouse ne porte pas les mesures attendues.' ),
			fabienclement_dev_mesures_lighthouse( '{"audits":{}}', true )['echecs']
		);
	}

	/**
	 * Un rapport illisible est un échec, pas une page propre.
	 */
	public function test_un_rapport_d_axe_illisible_est_un_echec(): void {
		$this->assertSame(
			array( "Le rapport d'axe est illisible." ),
			fabienclement_dev_violations_axe( '' )
		);
	}
}
