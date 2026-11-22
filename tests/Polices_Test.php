<?php
/**
 * Tests de la police que le thème déclare dans theme.json.
 *
 * @package fabienclement-dev
 */

use PHPUnit\Framework\TestCase;

/**
 * La police du thème, sans WordPress.
 */
final class Polices_Test extends TestCase {

	/**
	 * Le nombre de fichiers woff2 que la décision du 10/11/2026 permet au thème.
	 */
	private const FICHIERS_PERMIS = 2;

	/**
	 * Rend la famille Hanken Grotesk telle que theme.json la déclare.
	 *
	 * @return array{slug: string, fontFamily: string, fontFace: list<array{fontDisplay: string, src: list<string>}>}
	 */
	private function famille_hanken_grotesk(): array {
		foreach ( $this->theme_json()['settings']['typography']['fontFamilies'] as $famille ) {
			if ( 'hanken-grotesk' === $famille['slug'] ) {
				return $famille;
			}
		}

		$this->fail( 'theme.json ne déclare aucune famille « hanken-grotesk ».' );
	}

	/**
	 * Rend le contenu de theme.json.
	 *
	 * @return array{settings: array{typography: array{fontFamilies: list<array{slug: string, fontFamily: string, fontFace: list<array{fontDisplay: string, src: list<string>}>}>}}, styles: array{typography: array{fontFamily: string}}}
	 */
	private function theme_json(): array {
		$contenu = file_get_contents( dirname( __DIR__ ) . '/theme.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		/**
		 * Le contenu décodé de theme.json.
		 *
		 * @var array{settings: array{typography: array{fontFamilies: list<array{slug: string, fontFamily: string, fontFace: list<array{fontDisplay: string, src: list<string>}>}>}}, styles: array{typography: array{fontFamily: string}}} $theme
		 */
		$theme = json_decode( (string) $contenu, true, 512, JSON_THROW_ON_ERROR );

		return $theme;
	}

	/**
	 * Rend le chemin, depuis la racine du thème, de chaque fichier de police déclaré.
	 *
	 * @return list<string>
	 */
	private function fichiers_declares(): array {
		$fichiers = array();

		foreach ( $this->famille_hanken_grotesk()['fontFace'] as $face ) {
			foreach ( $face['src'] as $source ) {
				$fichiers[] = str_replace( 'file:./', '', $source );
			}
		}

		return $fichiers;
	}

	/**
	 * Le texte de tout le site emploie Hanken Grotesk.
	 */
	public function test_le_texte_du_site_emploie_hanken_grotesk(): void {
		$this->assertSame(
			'var(--wp--preset--font-family--hanken-grotesk)',
			$this->theme_json()['styles']['typography']['fontFamily']
		);
	}

	/**
	 * Le navigateur affiche la police du système tant que Hanken Grotesk n'est pas chargée.
	 */
	public function test_la_police_du_systeme_sert_de_repli(): void {
		$this->assertStringStartsWith(
			'"Hanken Grotesk", system-ui,',
			$this->famille_hanken_grotesk()['fontFamily']
		);
	}

	/**
	 * Chaque fichier de police déclaré existe dans le thème, au format woff2.
	 */
	public function test_chaque_fichier_declare_existe_en_woff2(): void {
		$fichiers = $this->fichiers_declares();

		$this->assertNotEmpty( $fichiers );

		foreach ( $fichiers as $fichier ) {
			$this->assertStringEndsWith( '.woff2', $fichier );
			$this->assertFileExists( dirname( __DIR__ ) . '/' . $fichier );
		}
	}

	/**
	 * Le thème déclare deux fichiers de police au plus.
	 */
	public function test_le_theme_declare_deux_fichiers_au_plus(): void {
		$this->assertLessThanOrEqual( self::FICHIERS_PERMIS, count( $this->fichiers_declares() ) );
	}

	/**
	 * Chaque fichier woff2 du dossier des polices est déclaré dans theme.json.
	 */
	public function test_aucun_fichier_woff2_ne_reste_sans_declaration(): void {
		$chemins = glob( dirname( __DIR__ ) . '/assets/fonts/*.woff2' );

		$this->assertIsArray( $chemins );

		$presents = array_map(
			static fn ( string $chemin ): string => 'assets/fonts/' . basename( $chemin ),
			$chemins
		);

		$this->assertEqualsCanonicalizing( $this->fichiers_declares(), $presents );
	}

	/**
	 * Le texte de la licence accompagne les fichiers, comme l'Open Font License le demande.
	 */
	public function test_la_licence_accompagne_les_fichiers(): void {
		$this->assertFileExists( dirname( __DIR__ ) . '/assets/fonts/OFL.txt' );
	}

	/**
	 * Le navigateur affiche le texte sans attendre la police.
	 */
	public function test_le_texte_s_affiche_sans_attendre_la_police(): void {
		foreach ( $this->famille_hanken_grotesk()['fontFace'] as $face ) {
			$this->assertSame( 'swap', $face['fontDisplay'] );
		}
	}
}
