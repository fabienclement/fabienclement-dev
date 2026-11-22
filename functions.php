<?php
/**
 * Ce que le thème retire du HTML que WordPress écrit par défaut.
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

// Les liens oEmbed laissent un autre site afficher la page en encart.
// WordPress n'écrit ces liens que si ce crochet est en place.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// Le site n'a pas de commentaires : l'en-tête annonce le seul flux des articles.
add_filter( 'feed_links_show_comments_feed', '__return_false' );
