<?php
/**
 * Plugin Name: Dash Download Shortcode
 * Plugin URI: http://
 * Description: Allows you to wrap uploaded file links in a shortcode that will force a download when clicked
 * Author: a
 * Author URI: http://
 * Version: 1.1
 */
define( 'DASH_VERSION', '1.0' );
define( 'DASH_SCRIPT_PATH', WP_CONTENT_DIR . '/dash-load.php' );
define( 'DASH_SCRIPT_BASE', plugin_dir_path( __FILE__ ) . 'inc/dash-load.php' );

class Dash_Shortcode {
	
	public $version;


	function __construct() {
		load_plugin_textdomain( 'dash_shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		$this->version = get_option( 'DASH_version' );
		$this->update_script( $this->version );

		add_shortcode( 'download', array( $this, 'download_shortcode_cb' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'rewrite_urls' ) );
	}


	function update_script( $version = false ) {
		if ( ! $version 
		 || version_compare( $version, DASH_VERSION, '<' )
		 || ( version_compare( $version, DASH_VERSION, '>=' ) && ( ! file_exists( DASH_SCRIPT_PATH ) || 0 == @filesize( DASH_SCRIPT_PATH ) ) )
		 ) {
			$content = file_exists( DASH_SCRIPT_BASE ) ? @file_get_contents( DASH_SCRIPT_BASE ) : '';

			if ( @file_put_contents( DASH_SCRIPT_PATH, $content ) ) {
				add_action( 'admin_notices', array( $this, 'updated_script' ) );
				update_option( 'DASH_version', DASH_VERSION );
			} else {
				if ( file_exists( $script ) && 0 != @filesize( DASH_SCRIPT_PATH ) ) {
					add_action( 'admin_notices', array( $this, 'updated_script' ) );
					update_option( 'DASH_version', DASH_VERSION );
				} else {
					add_action( 'admin_notices', array( $this, 'outdated_script' ) );
				}
			}
		}
	}
	

	function updated_script(){
		printf( '<div class="updated"><p>%s</p></div>', __( 'The download script in your <code>/wp-content/</code> directory has been updated.', 'dash_download_shortcode' ) );
	}

	function outdated_script() {
		printf( '<div class="error"><p>%s</p></div>', __( 'There is a problem with the download script in your <code>/wp-content/</code> directory. You may need to manually upload it.', 'dash_download_shortcode' ) );
	}

	function download_shortcode_cb( $attr, $content ) {
		if ( empty( $content ) ) {
			return;
		} else {
			
			$content_pre = content_url( '/' );

			$content = esc_url( $content );


			$content = str_replace( $content_pre . $this->uploads_path( '/' ), '', $content );


			global $wp_rewrite;
			if ( $wp_rewrite->using_mod_rewrite_permalinks() && apply_filters( 'DASH_rewrite_urls', true ) ) {
				
				$url = home_url( '/' ) . $this->download_path( '/' ) . $content;
			} else {
				
				$url = sprintf( '%1$sdash-load.php?dash=%2$s', $content_pre, $this->uploads_path( '/' ) . $content );
			}

			if ( isset( $attr['label'] ) && ! empty( $attr['label'] ) ) {
				
				$label = esc_html( $attr['label'] );
			} else {
				
				$label = $url;
			}

			
			$class = apply_filters( 'DASH_download_link_class', '' );

			
			return sprintf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $url ), esc_attr( $class ), esc_attr( $label ) );
		}
	}


	function append_path( $path = '' ) {
		if ( ! empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false ) {
			$path = '/' . ltrim( $path, '/' );
		}
		return $path;			
	}

	
	function download_path( $path = '' ) {
		return apply_filters( 'DASH_download_rewrite_path', 'download' ) . $this->append_path( $path );
	}

	function uploads_path( $path = '' ) {
		$upload_dir = wp_upload_dir();
		$upload_path = str_replace( content_url( '/' ), '', $upload_dir['baseurl'] );

		return apply_filters( 'DASH_download_files_directory', $upload_path ) . $this->append_path( $path );
	}

	function optional_subdir() {
		if ( site_url() != home_url() ) {
			return str_replace( home_url( '/' ), '', site_url( '/' ) );
		}
	}
	 
	function rewrite_urls( $wp_rewrite ) {
		$wp_rewrite->add_external_rule( 
			sprintf( '%s/(.+)$', $this->download_path() ),
			sprintf( '%1$swp-content/dash-load.php?dash=%2$s/$1', $this->optional_subdir(), $this->uploads_path() )
		);
	}
	
	function deactivate() {
		if ( file_exists( DASH_SCRIPT_PATH ) && ! @unlink( DASH_SCRIPT_PATH ) )
			@file_put_contents( DASH_SCRIPT_PATH, '' );
	}

}

new Dash_Shortcode;
?>