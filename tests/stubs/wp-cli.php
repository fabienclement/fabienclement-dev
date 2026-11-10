<?php
/**
 * Les méthodes de WP_CLI que bin/surcharges.php appelle, pour PHPStan.
 *
 * Le paquet php-stubs/wp-cli-stubs exige les stubs de WordPress 6 au plus,
 * et le thème déclare ceux de WordPress 7. PHPStan lit ce fichier sans
 * l'exécuter.
 *
 * @package fabienclement-dev
 */

// phpcs:disable

/**
 * Les méthodes de WP-CLI dont le thème se sert.
 */
class WP_CLI {

	/**
	 * Affiche un message de succès.
	 *
	 * @param string $message Le message.
	 */
	public static function success( string $message ): void {}

	/**
	 * Affiche une ligne.
	 *
	 * @param string $message La ligne.
	 */
	public static function line( string $message = '' ): void {}

	/**
	 * Arrête WP-CLI avec un code de sortie.
	 *
	 * @param int $code Le code de sortie.
	 * @return never
	 */
	public static function halt( int $code ): void {}
}
