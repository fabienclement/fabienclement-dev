<?php
/**
 * Tests de la liste des surcharges que la base garde.
 *
 * @package fabienclement-dev
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/bin/surcharges-fonctions.php';

/**
 * La liste des surcharges, sans WordPress.
 */
final class Surcharges_Test extends TestCase {

	/**
	 * Une copie de gabarit qui a son fichier dans le thème masque ce fichier.
	 */
	public function test_une_copie_de_gabarit_masque_son_fichier(): void {
		$gabarits = array(
			array(
				'type'           => 'wp_template',
				'slug'           => 'index',
				'source'         => 'custom',
				'has_theme_file' => true,
			),
		);

		$this->assertSame(
			array( 'Le gabarit index masque templates/index.html.' ),
			fabienclement_dev_surcharges( $gabarits, '' )
		);
	}

	/**
	 * Un gabarit lu depuis son fichier n'est pas une surcharge.
	 */
	public function test_un_gabarit_du_theme_n_est_pas_une_surcharge(): void {
		$gabarits = array(
			array(
				'type'           => 'wp_template',
				'slug'           => 'index',
				'source'         => 'theme',
				'has_theme_file' => true,
			),
		);

		$this->assertSame( array(), fabienclement_dev_surcharges( $gabarits, '' ) );
	}

	/**
	 * Une copie de partie de gabarit masque son fichier de parts/.
	 */
	public function test_une_copie_de_partie_masque_son_fichier(): void {
		$gabarits = array(
			array(
				'type'           => 'wp_template_part',
				'slug'           => 'header',
				'source'         => 'custom',
				'has_theme_file' => true,
			),
		);

		$this->assertSame(
			array( 'La partie de gabarit header masque parts/header.html.' ),
			fabienclement_dev_surcharges( $gabarits, '' )
		);
	}

	/**
	 * Un gabarit créé dans l'éditeur n'a pas de fichier à masquer.
	 */
	public function test_un_gabarit_sans_fichier_n_existe_que_dans_la_base(): void {
		$gabarits = array(
			array(
				'type'           => 'wp_template',
				'slug'           => 'page-contact',
				'source'         => 'custom',
				'has_theme_file' => false,
			),
		);

		$this->assertSame(
			array( "Le gabarit page-contact n'existe que dans la base." ),
			fabienclement_dev_surcharges( $gabarits, '' )
		);
	}

	/**
	 * Des styles globaux modifiés dans l'éditeur masquent theme.json.
	 */
	public function test_des_styles_globaux_modifies_masquent_theme_json(): void {
		$styles = '{"version": 3, "isGlobalStylesUserThemeJSON": true, "styles": {"color": {"background": "#000000"}}}';

		$this->assertSame(
			array( 'Les styles globaux de la base masquent theme.json.' ),
			fabienclement_dev_surcharges( array(), $styles )
		);
	}

	/**
	 * Des réglages modifiés dans l'éditeur masquent aussi theme.json.
	 */
	public function test_des_reglages_modifies_masquent_theme_json(): void {
		$styles = '{"version": 3, "isGlobalStylesUserThemeJSON": true, "settings": {"color": {"palette": {"custom": [{"slug": "noir", "color": "#000000", "name": "Noir"}]}}}}';

		$this->assertSame(
			array( 'Les styles globaux de la base masquent theme.json.' ),
			fabienclement_dev_surcharges( array(), $styles )
		);
	}

	/**
	 * La copie vide que WordPress crée à l'ouverture des styles ne masque rien.
	 */
	public function test_la_copie_vide_des_styles_globaux_ne_masque_rien(): void {
		$styles = '{"version": 3, "isGlobalStylesUserThemeJSON": true}';

		$this->assertSame( array(), fabienclement_dev_surcharges( array(), $styles ) );
	}
}
