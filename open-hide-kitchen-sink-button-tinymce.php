<?php

// OPEN AND/OR HIDE KITCHEN SINK BUTTON IN TINYMCE

/*  Compatible Versions of PHP and WordPress */
add_action( 'init', 'kitchen_sink_set_metadata' ); 
// it had originally 'plugins_loaded' because this functions were in a plugin
// https://wordpress.org/plugins/jonradio-display-kitchen-sink/

function kitchen_sink_set_metadata() {
  /*  Force Kitchen Sink to be displayed for the current User on the current Site.
    Set both User Setting and the Cookie.
    
    Faster to just check the relevant User Setting on every Admin panel,
    than try to determine when the WordPress Page/Post Editor is being used.
    
    But first, be sure User is logged in.
  */  
  if ( 0 !== ( $user_id = get_current_user_id() ) ) {
    $settings_default = 'editor=tinymce&hidetb=1';
    $cookie_name = 'wp-settings-1';
    if ( isset( $_COOKIE[$cookie_name] ) ) {
      $cookie_value = str_ireplace( 'hidetb=0', 'hidetb=1', $_COOKIE[$cookie_name] );
    } else {
      $cookie_value = $settings_default;
    }
    /*  Determine Path off Domain to WordPress Address, not Site Address, for Cookie Path value.
      Which, confusingly enough, is site_url().
    */
    setcookie( $cookie_name, $cookie_value, strtotime( '+1 year' ), parse_url( site_url(), PHP_URL_PATH ) . '/', $_SERVER['SERVER_NAME'] );

    global $wpdb;
    $editor_settings_name = $wpdb->prefix . 'user-settings';
    $wp_user_settings = get_user_meta( $user_id, $editor_settings_name, TRUE );
    if ( empty( $wp_user_settings ) ) {
      $settings = $settings_default;
      $update = TRUE;
    } else {
      parse_str( $wp_user_settings, $settings_array );
      if ( $update = ( ( !isset( $settings_array['hidetb'] ) ) || ( '0' === $settings_array['hidetb'] ) ) ) {
        $settings_array['hidetb'] = '1';
        $settings = build_query( $settings_array );
      }
    }
    if ( $update ) {
      /*  Build the Query and Save It
      */  
      update_user_meta( $user_id, $editor_settings_name, $settings );
    }
  }
}

add_filter( 'tiny_mce_before_init', 'kitchen_sink_remove_icon' );
 
function kitchen_sink_remove_icon( $args ) {
  if ( version_compare( get_bloginfo( 'version' ), '3.8.99', '>' ) ) {
    $args_key = 'toolbar1';
  } else {
    $args_key = 'theme_advanced_buttons1';
  }
  if ( FALSE !== ( $icons = explode( ',', $args[$args_key] ) ) ) {
    if ( FALSE !== ( $icons_key = array_search( 'wp_adv', $icons ) ) ) {
      unset( $icons[$icons_key] );
      if ( empty( $icons ) ) {
        $args[$args_key] = '';
      } else {        
        $args[$args_key] = implode( ',', $icons );
      }
    }
  }
  return $args;
}

?>