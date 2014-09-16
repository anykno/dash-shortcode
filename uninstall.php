<?php

delete_option( 'DASH_version' );

if ( file_exists( WP_CONTENT_DIR . '/dash-load.php' ) && ! @unlink( WP_CONTENT_DIR . '/dash-load.php' ) )
	@file_put_contents( WP_CONTENT_DIR . '/dash-load.php', '' );