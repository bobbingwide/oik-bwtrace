<?php // (C) Copyright Bobbing Wide 2011-2020
if ( !defined( "CLASS_OIK_UPDATE_INCLUDED" ) ) {
define( "CLASS_OIK_UPDATE_INCLUDED", "3.4.0" );

/**
 *
 * Library: class-oik-update
 * Provides: oik_update
 * Depends: bobbfunc, bobbforms, class-bobbcomp, oik_plugins 
 * Deferred dependencies: class-oik-remote - could be cyclical! 
 *
 * Functions that support the plugin and theme updates.
 * Extracted from admin/oik-admin.inc 
 * 
 * These are the shared library functions. 
 */
 
class oik_update {

/**
 * Register this plugin as one that is served from a different server to WordPress.org
 *
 * @param string $file - fully qualified plugin file name
 * @param string $server - server name initial value - only set when the server value in the options is blank 
 * @param string $apikey - hard coded apikey initial value
 *
 * 
 * Notes: Plugins registered using the API set the default value for the server ... which may be null
 * i.e. they set the intention to be served from somewhere other than WordPress.org
 * When required we determine the actual server location AND other fields as needed during oik_query_plugins_server() 
 *
 * At least ONE plugin needs to call this API for the oik plugin server logic to be activated 
 */
static function oik_register_plugin_server( $file, $server=null, $apikey=null ) {
	global $bw_registered_plugins;
	if ( !isset( $bw_registered_plugins ) ) {
		self::oik_lazy_altapi_init();
	}
	$bw_registered_plugins[] = array( "file" => $file, "server" => $server, "apikey" => $apikey );
	//bw_trace2( $bw_registered_plugins, "global bw_registered_plugins", true );
}

/**
 * Register this theme as one that is served from a different server to WordPress.org
 */
static function oik_register_theme_server( $file, $server=null, $apikey=null ) {
	global $bw_registered_themes;
	if ( !isset( $bw_registered_themes ) ) {
		self::oik_lazy_alttheme_init();
	}
	$bw_registered_themes[] = array( "file" => $file, "server" => $server, "apikey" => $apikey );
	bw_trace2( $bw_registered_themes );
}

/** 
 * Only register our plugin server when needed.
 * 
 * 
 */
static function oik_lazy_altapi_init() {
	add_action( "pre_set_site_transient_update_plugins", "oik_update::oik_altapi_check" );
	add_action( "site_transient_update_plugins", "oik_update::oik_site_transient_update_plugins", 10, 1 );
	add_filter( 'site_transient_update_plugins', 'oik_update::oik_site_transient_filter_symlinked_plugins' );
	add_filter( 'site_transient_update_plugins', 'oik_update::oik_site_transient_filter_git_plugins' );
	add_action( "plugins_api", "oik_update::oik_pluginsapi", 10, 3 );
}

/** 
 * Only register our theme server when needed
 */
static function oik_lazy_alttheme_init() {
	add_action( "pre_set_site_transient_update_themes", "oik_update::oik_alttheme_check" );
	add_action( "site_transient_update_themes", "oik_update::oik_site_transient_update_themes", 10, 1 );
	add_filter( "themes_api", "oik_update::oik_themes_api", 10, 3 );
	//add_filter( "themes_api_result", "oik_update::oik_themes_api_result", 10, 3  );
}

/**
 * Checks for plugin updates.
 *
 * @param Object $transient
 * @return Object the updated transient
 */  
static function oik_altapi_check( $transient ) {
	oik_require_lib( "class-oik-remote" );
	return( oik_remote::oik_lazy_altapi_check( $transient ) );
}


/**
 * Checks for theme updates.
 *
 * @param Object $transient
 * @return Object the updated transient
 */  
static function oik_alttheme_check( $transient ) {
	oik_require_lib( "class-oik-remote" );
	return( oik_remote::oik_lazy_alttheme_check( $transient ) );
}

/**
 * If required, unset last_checked to force another "check for updates" for plugins
 * 
 * Note: Only use this when testing the oik plugin update logic
 */
static function oik_site_transient_update_plugins( $transient ) {
	if ( defined( "OIK_FORCE_CHECK" ) ) {
		if ( OIK_FORCE_CHECK ) {
			static $last_checked = null; 
			if ( !$last_checked ) {
				$last_checked = $transient->last_checked;
				unset( $transient->last_checked ); 
				bw_trace2( $last_checked, "transient" );
			}  
		} else {     
			//bw_backtrace();
		}
	}
	return( $transient );
}

	/**
	 * Removes symlinked plugins from the update list.
	 *
	 * Note: They are only removed on the update-core page.
	 *
	 * @param object $transient The 'transient' for plugin updates.
	 * @return mixed Updated transient.
	 */
static function oik_site_transient_filter_symlinked_plugins( $transient ) {
	if ( oik_update::is_update_core() ) {
		foreach ( $transient->response as $plugin_file=>$plugin_object ) {
			if ( oik_update::is_symlinked( $plugin_file ) ) {
				unset( $transient->response[ $plugin_file ] );
			}
		}
	}
	return $transient;
}

	/**
	 * Checks if it's the update-core page.
	 *
	 * Note: This can't be done early since global $current_screen may not be set.
	 *
	 * @return bool
	 */
static function is_update_core() {
	$is_update_core=false;
	$current_screen=get_current_screen();
	bw_trace2( $current_screen, "current_screen", false, BW_TRACE_VERBOSE );
	if ( $current_screen && $current_screen->id === 'update-core' ) {
		$is_update_core=true;
	}
	return $is_update_core;
}
	/**
	 * Checks if a plugin is symlinked.
	 *
	 * @param string $plugin_file Plugin file e.g. oik/oik.php
	 * @return bool true if the plugin is symlinked
	 */
static function is_symlinked( $plugin_file ) {
	$normalized = wp_normalize_path( WP_PLUGIN_DIR );
	$plugin_path = $normalized . '/' . $plugin_file;
	$real_path = realpath( $plugin_path );
	$real_path = wp_normalize_path( $real_path );
	$symlinked = ( $real_path != $plugin_path ) ;
	return $symlinked;
}

	/**
	 * Removes git plugins from the update list.
	 *
	 * Plugins are only removed on the update-core page.
	 *
	 * @param object $transient The transient for plugin_updates.
	 * @return mixed
	 */
static function oik_site_transient_filter_git_plugins( $transient ) {
	if ( oik_update::is_update_core() ) {
		foreach ( $transient->response as $plugin_file=>$plugin_object ) {
			if ( oik_update::is_git( $plugin_file ) ) {
				unset( $transient->response[ $plugin_file ] );
			}
		}
	}
	return $transient;
}

	/**
	 * Determines if the plugin is a Git repository.
	 *
	 * @param string $plugin_file Plugin file name e.g. oik/oik.php.
	 * @return bool true if we consider this to be a Git repo.
	 */
	static function is_git( $plugin_file ) {
		$is_git = false;
		$dot_git = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );
		$dot_git .= '/.git';
		$dot_git = str_replace( "/", DIRECTORY_SEPARATOR, $dot_git );
		if ( file_exists( $dot_git ) && is_dir( $dot_git ) ) {
			$is_git = true;
		}
		return $is_git;
	}
 
/**
 * Updates site transient for theme updates.
 *
 * If required, unset last_checked to force another "check for updates" for themes.
 *
 * Note: Only use this when testing the oik theme update logic
 */
static function oik_site_transient_update_themes( $transient ) {
	if ( defined( "OIK_FORCE_CHECK_THEMES" ) && OIK_FORCE_CHECK_THEMES ) {
		static $last_checked = null; 
		if ( !$last_checked ) {
			$last_checked = $transient->last_checked;
			unset( $transient->last_checked ); 
			bw_trace2( $last_checked, "transient" );
		}  
	}     
	//bw_backtrace();
	return( $transient );
}


/**
 * Return the plugins server if the requested plugin is one of ours
 *
 * Note: $bw_registered_plugins is an array of filenames
 * we create $bw_slugs as an array of "slug" => array( 'basename' => "slug/plugin_name.php", 'file'=> 'server'=>, 'apikey'=> )
 * $bw_plugins (stored in WordPress options) also contains 'server' and 'apikey'
 * 
 * @param string $slug plugin slug
 * @return array 
 */
static function oik_query_plugins_server( $slug ) {
	global $bw_registered_plugins, $bw_slugs;
	if ( !isset( $bw_slugs ) ) {
		$bw_slugs = array();
		if ( isset( $bw_registered_plugins ) ) {
			foreach ( $bw_registered_plugins as $key => $value ) {
				$file = bw_array_get( $value, "file", null );
				$plugin_basename = plugin_basename( $file );
				$bw_slug = pathinfo( $plugin_basename, PATHINFO_DIRNAME );
				$value['basename'] = $plugin_basename;
				$bw_slugs[ $bw_slug ] = $value;
			}
		}
		bw_trace2( $bw_slugs );
	}
	$plugin_settings = bobbcomp::bw_get_option( $slug, "bw_plugins" ); 
	bw_trace2( $plugin_settings );
	/* return the saved settings, with any registered defaults, otherwise just get the registered settings */
	if ( $plugin_settings ) {
		$server = bw_array_get( $plugin_settings, "server", null );
		$apikey = bw_array_get( $plugin_settings, "apikey", null ); 
		if ( !$server || !$apikey ) { 
			 $value = bw_array_get( $bw_slugs, $slug, null );
		}   
		if ( !$server ) {   
			$server = bobbcomp::bw_array_get_dcb( $value, "server", null, "oik_update::oik_get_plugins_server" );
		}
		if ( !$apikey ) {
			$plugin_settings['apikey'] = bw_array_get( $value, "apikey", null );
		} 
	} else {
		$plugin_settings = bw_array_get( $bw_slugs, $slug, null );
		if ( $plugin_settings ) {
				$server = bobbcomp::bw_array_get_dcb( $plugin_settings, "server", null, "oik_update::oik_get_plugins_server" );
		}
		// apikey doesn't default here 
	}  
	if ( $plugin_settings ) {
		$plugin_settings['server'] = $server;
		bw_trace2( $server, "server" );  
	}
	return( $plugin_settings ); 
}

/**
 * Return the themes server if the requested theme is one of ours
 *
 * Note: $bw_registered_themes is an array of filenames
 * we create $bw_theme_slugs as an array of "slug" => array( 'basename' => "theme name", 'file'=> 'server'=>, 'apikey'=> )
 * $bw_themes (stored in WordPress options) also contains 'server' and 'apikey'
 * 
 * @param string $slug theme name
 * @return array 
 */
static function oik_query_themes_server( $slug ) {
	global $bw_registered_themes, $bw_theme_slugs;
	if ( !isset( $bw_theme_slugs ) ) {
		$bw_theme_slugs = array();
		if ( is_array( $bw_registered_themes) && count( $bw_registered_themes ) ) {
			foreach ( $bw_registered_themes as $key => $value ) {
				$file = bw_array_get( $value, "file", null );
				// The next 2 lines are equivalent to $bw_slug = bw_last_path( $file );
			 $pathinfo = pathinfo( $file, PATHINFO_DIRNAME );
				$bw_slug = basename( $pathinfo );
				$value['basename'] = $bw_slug;
				$bw_theme_slugs[ $bw_slug ] = $value;
			}
		}
		bw_trace2( $bw_theme_slugs );
	}
	$theme_settings = bobbcomp::bw_get_option( $slug, "bw_themes" ); 
	bw_trace2( $theme_settings );
	/* return the saved settings, with any registered defaults, otherwise just get the registered settings */
	if ( $theme_settings ) {
		$server = bw_array_get( $theme_settings, "server", null );
		$apikey = bw_array_get( $theme_settings, "apikey", null ); 
		if ( !$server || !$apikey ) { 
			 $value = bw_array_get( $bw_theme_slugs, $slug, null );
		}   
		if ( !$server ) {   
			$server = bobbcomp::bw_array_get_dcb( $value, "server", null, "oik_update::oik_get_themes_server" );
			bw_trace2( $server, $slug, false );
		}
		if ( !$apikey ) {
			$theme_settings['apikey'] = bw_array_get( $value, "apikey", null );
		} 
	} else {
		$theme_settings = bw_array_get( $bw_theme_slugs, $slug, null );
		if ( $theme_settings ) {
			$server = bobbcomp::bw_array_get_dcb( $theme_settings, "server", null, "oik_update::oik_get_themes_server" );
		}
		// apikey doesn't default here 
	}  

	if ( $theme_settings ) {
		$theme_settings['server'] = $server;
		bw_trace2( $server, "server", false, BW_TRACE_INFO );  
	}
	return( $theme_settings ); 
}

/**
 * Return the slug part of a plugin name
 *
 * This function should only be called when we know it's a plugin name with a directory.
 * 
 * Sample results
 * - "slug" for "slug/plugin_name.php" - when called for "update-check"
 * - "slugonly" for "slugonly" - when called for "plugin_information"
 * - "hello" for "hello.php" - does this ever happen?
 * - null for null
 * 
 * @param string $plugin - a plugin name
 * @return string $slug - the slug used to identify the plugin 
 *
 */
static function bw_get_slug( $plugin ) {
	if ( $plugin ) {
		$pathinfo = pathinfo( $plugin );
		$slug = $pathinfo['dirname'] ;
		if ( $slug == '.' ) { 
			$slug = $pathinfo['filename'];
		}
	} else {
		$slug = null;
	}  
	return( $slug );    
}

/**
 * Return the last path for the given file
 * 
 * @param string $file - a fully specified file name ( e.g. __FILE__ )
 * @return string the last part of the file's path
 * e.g. 
 *
 * @link http://www.php.net/manual/en/function.basename.php#72254
 * When using basename() on a path to a directory ('/bar/foo/'), the last path component ('foo') is returned
 */
static function bw_last_path( $file ) {
	bw_trace2();
	$pathinfo = pathinfo( $file, PATHINFO_DIRNAME );
	$last_path = basename( $pathinfo );
	return( $last_path );
}

static function oik_pluginsapi( $false, $action, $args ) {
	oik_require_lib( "class-oik-remote" );
	return( oik_remote::oik_lazy_pluginsapi( $false, $action, $args ) );
}

static function oik_themes_api( $false, $action, $args ) {
	oik_require_lib( "class-oik-remote" );
	return( oik_remote::oik_lazy_themes_api( $false, $action, $args ) );
}


  
/** 
 * Return the URL for the Premium (Pro) or Freemium version
 * 
 * If BW_OIK_PLUGINS_SERVER is defined we'll use that.
 * Else, we'll use the value of OIK_PLUGINS_COM
 * which we'll define if it's not already defined
 * 
 * @return string URL for an oik-plugins server
 */
static function oik_get_plugins_server() {
		if ( defined( 'BW_OIK_PLUGINS_SERVER' )) {
			$url = BW_OIK_PLUGINS_SERVER;
		} else {
		if ( !defined( "OIK_PLUGINS_COM" ) ) {
			define( "OIK_PLUGINS_COM", "https://www.oik-plugins.com" );
		}
			$url = OIK_PLUGINS_COM;
		}
		return( $url );
	}
	
	
/** 
 * Return the URL for the theme server
 * 
 * @return string URL for an oik-plugins server
 */
static function oik_get_themes_server() {
	return( self::oik_get_plugins_server() );
}

} /* end class */

} /* end !defined() */
