<?php
/**
 * @package AM_WP_REACT_EXAMPLE
 * @wordpress-plugin
 * 
 * Plugin Name: WP React Example in Backend
 * Description: Shows how to use React in the WP backend including Authentication
 * Author: Alexander Merz <alexander.merz@gmail.com>
 * Version: 1.0.0
 */
namespace AM_WP_REACT_EXAMPLE;

define( 'AM_WP_REACT_EXAMPLE', '1.0.0' );
// the relative path to the JS and CSS files for the React app
define( 'CSS_PATH', 'wp-example/build/static/css/' );
define( 'JS_PATH', 'wp-example/build/static/js/' );

/**
 * Add menu item to WP backend
 */
function plugin_menu() {
    \add_menu_page( 'React Example', 'React Example', 'edit_posts', 'react-example', 'AM_WP_REACT_EXAMPLE\react_page' , 'dashicons-admin-generic', 99 );
}

/**
 * Add an admin page to the backend for your plugin
 * The admin page will contain the React app
 */
function react_page() {

    // get the JS and CSS files for the React app
    $css_files = \glob( \plugin_dir_path( __FILE__ ) . CSS_PATH . '*.css' );
    $js_files = \glob( \plugin_dir_path( __FILE__ ) . JS_PATH . '*.js' );

    // enqueue the CSS files for the React app
    foreach ( $css_files as $css_file ) {
        $handle = 'wp-react-' . md5( $css_file );
        $css_file_real = \plugin_dir_url( __FILE__ ) . CSS_PATH . \basename( $css_file );
        \wp_enqueue_style( $handle, $css_file_real, [], null);
    }

    // enqueue the JS files for the React app
    foreach ( $js_files as $js_file ) {
        $handle = 'wp-react-' . md5( $js_file );
        $js_file_real = \plugin_dir_url( __FILE__ ) . JS_PATH . \basename( $js_file );
        \wp_enqueue_script( $handle, $js_file_real, [], null, true );
    }      

    // we need to pass the nonce to the React app via global JS variable
    // the variable is injected in front of the first included JS file
    if( count( $js_files ) > 0 ) { // make sure we actually have at least one JS file
        $nonce = \wp_create_nonce( "wp_rest" ); // the nonce is used for authentication, the value 'wp_rest' is required by the standard WP REST API
        $handle = 'wp-react-' . md5( $js_files[0] );    
        \wp_add_inline_script( $handle, sprintf( 'document.WpReact = { "nonce" :  "%s"};', $nonce ), 'before' );
        // the variable will be available in the React JS files as document.WpReact.nonce
    }                

    // we only render the root node for the React app 
    echo '<div id="root"></div>';        
}

// add the menu item to the WP backend
\add_action( 'admin_menu', 'AM_WP_REACT_EXAMPLE\plugin_menu' );    