<?php

//main scripts
add_action('wp_enqueue_scripts', function(){

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'main-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style'], date('YmdGis', filemtime(dirname(__FILE__) . '/style.css') ) );

    wp_register_script( 'main-script', get_theme_file_uri( 'assets/js/main.min.js' ), [], date('YmdGis', filemtime(dirname(__FILE__) . '/assets/js/main.min.js' ) ), true );

});

?>