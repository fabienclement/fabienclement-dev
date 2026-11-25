<?php
/**
 * Ce que le thème retire, ferme ou ajoute à ce que WordPress fait par défaut.
 *
 * @package fabienclement-dev
 */

// Le script des emojis pèse 23 Ko sur chaque page, pour un site qui n'affiche aucun emoji.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

// WordPress n'écrit le style des emojis que si ce crochet est en place.
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// La balise generator affiche la version de WordPress.
remove_action( 'wp_head', 'wp_generator' );

// Le lien EditURI sert les logiciels qui publient à distance, par xmlrpc.php.
remove_action( 'wp_head', 'rsd_link' );

// Le lien court d'une page, en « ?p= », double son adresse canonique.
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

// Les liens vers l'API REST servent des logiciels, et aucun visiteur.
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

// WordPress envoie aussi ces deux liens dans un en-tête HTTP « Link », hors du HTML.
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// Le site refuse tout commentaire et tout rétrolien, quel que soit le réglage de la page.
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );

// L'en-tête annonce le flux des articles, sans les flux des commentaires.
add_filter( 'feed_links_show_comments_feed', '__return_false' );

// Le cœur de WordPress ne précharge aucune police de theme.json.
// Sans la balise de préchargement, le navigateur demande la police après avoir calculé le style de la page.
// La balise ne précharge pas l'italique, que peu de textes emploient.
add_action( 'wp_head', 'fabienclement_dev_precharger_police', 1 );

/**
 * Écrit dans l'en-tête HTML la balise qui précharge la police romaine du thème.
 */
function fabienclement_dev_precharger_police(): void {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/hanken-grotesk-romain.woff2' ) )
	);
}
