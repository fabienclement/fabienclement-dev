<?php
/**
 * Ce que le thème retire du HTML que WordPress écrit par défaut.
 *
 * @package fabienclement-dev
 */

// Le script des emojis pèse 23 Ko sur chaque page, pour un site qui n'affiche aucun emoji.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

// WordPress écrit le style des emojis par l'un de ces deux crochets.
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
