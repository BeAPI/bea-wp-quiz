<?php

namespace BEA\ComposerInstaller;

use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Script\Event;

class PluginBoilerplateScaffold {

	static $available_components = array(
		'controller',
		'cron',
		'model',
		'route',
		'widget',
		'shortcode',
	);

	/**
	 * Scaffold a new plugin from Boilerplate
	 *
	 * @param Event $event
	 */
	public static function scaffoldPlugin( Event $event ) {

		// Quick access
		$io = $event->getIO();
		$composer = $event->getComposer();
		$args = $event->getArguments();

		// Setup
		$pluginName = '';
		$components = array();
		if( !empty( $args ) ) {
			// Get plugin name
			$pluginName = array_shift( $args );
			$pluginName = str_replace( array( ' ', '_' ), '-', trim( $pluginName ) );

			// Get selected modules
			$components = array_filter( $args, function( $el ){
				if( in_array( $el, static::$available_components ) ) {
					return true;
				}

				return false;
			} );
		}

		$io->write("\nScaffolding plugin $pluginName");

		// Get plugin name
		if( empty( $pluginName ) ) {
			$pluginName = trim( $io->ask( "What is your plugin's name ? " ) );
			if( empty( $pluginName ) ) {
				$io-> write( "Your plugin's name is invalid" );
				exit;
			}
		}

		// Get plugin components
		if( !empty( $components ) ) {
			$io->write("\nYou have selected those components for your plugin :");
			foreach( $components as $component ) {
				$io->write( "* $component" );
			}
			if ( false === $io->askConfirmation( "Is that Ok for you ? ", true ) ) {
				exit;
			}
		} else {
			$io->write("\nYou have not selected any components for your plugin");
			$io->write("Available components are :");
			foreach( static::$available_components as $component ) {
				$io->write( "* $component" );
			}
			if ( false === $io->askConfirmation( "\nIs that Ok for you ? ", true ) ) {
				exit;
			}
		}

		$downloadPath = $composer->getConfig()->get( 'vendor-dir' ) . '/boilerplate';
		$pluginPath = dirname( $composer->getConfig()->get( 'vendor-dir' ) ) . '/content/plugins';

		if( is_dir( $pluginPath . '/' . $pluginName ) ) {
			$io->write( "oops! Plugin already exist" );
			exit;
		}

		// Ensure we have boilerplate plugin locally
		if( !file_exists( $downloadPath . '/bea-plugin-boilerplate.php' ) ) {
			$composer->getDownloadManager()->download( self::getPluginBoilerplatePackage(), $downloadPath );
		}

		if( !file_exists( $downloadPath . '/bea-plugin-boilerplate.php' ) ) {
			$io->write( "oops! Couldn't download plugin boilerplate" );
			exit;
		}

		// Setup plugin directory
		if( !is_writable( $pluginPath ) ) {
			$io->write( "oops! Plugins directory is not writable" );
			exit;
		}
		if( !mkdir( $pluginPath . '/' . $pluginName ) ) {
			$io->write( "oops! Couldn't create your plugin directory" );
			exit;
		}

		// Basic plugin files
		mkdir( $pluginPath . '/' . $pluginName . '/classes/admin/', 0777, true );

		rename( $downloadPath . '/bea-plugin-boilerplate.php', $pluginPath . '/' . $pluginName . '/' . $pluginName . '.php' );
		rename( $downloadPath . '/compat.php', $pluginPath . '/' . $pluginName . '/compat.php' );
		rename( $downloadPath . '/autoload.php', $pluginPath . '/' . $pluginName . '/autoload.php' );

		// Basic plugin classes
		rename( $downloadPath . '/classes/plugin.php', $pluginPath . '/' . $pluginName . '/classes/plugin.php' );
		rename( $downloadPath . '/classes/main.php', $pluginPath . '/' . $pluginName . '/classes/main.php' );
		rename( $downloadPath . '/classes/helpers.php', $pluginPath . '/' . $pluginName . '/classes/helpers.php' );
		rename( $downloadPath . '/classes/singleton.php', $pluginPath . '/' . $pluginName . '/classes/singleton.php' );
		rename( $downloadPath . '/classes/admin/main.php', $pluginPath . '/' . $pluginName . '/classes/admin/main.php' );

		foreach( static::$available_components as $component ) {
			if( !in_array( $component, $components ) ) {
				continue;
			}

			switch( $component ) {
				case 'controller':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/controllers/' );
					rename( $downloadPath . '/classes/controllers/controller.php', $pluginPath . '/' . $pluginName . '/classes/controllers/controller.php' );
					break;
				case 'cron':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/cron/' );
					rename( $downloadPath . '/classes/cron/cron.php', $pluginPath . '/' . $pluginName . '/classes/cron/cron.php' );
					break;
				case 'model':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/models/' );
					rename( $downloadPath . '/classes/models/model.php', $pluginPath . '/' . $pluginName . '/classes/models/model.php' );
					rename( $downloadPath . '/classes/models/user.php', $pluginPath . '/' . $pluginName . '/classes/models/user.php' );
					break;
				case 'route':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/routes/' );
					rename( $downloadPath . '/classes/routes/router.php', $pluginPath . '/' . $pluginName . '/classes/routes/router.php' );
					break;
				case 'widget':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/widgets/' );
					mkdir( $pluginPath . '/' . $pluginName . '/views/' );
					mkdir( $pluginPath . '/' . $pluginName . '/views/admin/' );
					mkdir( $pluginPath . '/' . $pluginName . '/views/client/' );

					// Class
					rename( $downloadPath . '/classes/widgets/main.php', $pluginPath . '/' . $pluginName . '/classes/widgets/main.php' );

					// Views
					rename( $downloadPath . '/views/admin/widget.php', $pluginPath . '/' . $pluginName . '/views/admin/widget.php' );
					rename( $downloadPath . '/views/client/widget.php', $pluginPath . '/' . $pluginName . '/views/client/widget.php' );
					break;
				case 'shortcode':
					mkdir( $pluginPath . '/' . $pluginName . '/classes/shortcodes/' );
					rename( $downloadPath . '/classes/shortcodes/shortcode.php', $pluginPath . '/' . $pluginName . '/classes/shortcodes/shortcode.php' );
					rename( $downloadPath . '/classes/shortcodes/shortcode-factory.php', $pluginPath . '/' . $pluginName . '/classes/shortcodes/shortcode-factory.php' );
					break;
			}
		}

		// Replace
		$pluginCompletePath = $pluginPath . '/' . $pluginName . '/';

		// text domain
		self::doStrReplace( $pluginCompletePath, 'bea-plugin-boilerplate', $pluginName );

		// init function
		self::doStrReplace( $pluginCompletePath, 'init_bea_pb_plugin', 'init_' . str_replace( '-', '_', $pluginName ) . '_plugin' );

		// plugin human name
		$pluginRealName = self::askAndConfirm( $io, "\nWhat is your plugin real name ? (e.g: 'My great plugin') " );
		self::doStrReplace( $pluginCompletePath, 'BEA Plugin Name', $pluginRealName );

		// namespace
		$pluginNamespace = self::askAndConfirm( $io, "\nWhat is your plugin's namespace ? (e.g: 'BEA\\My_Plugin') " );
		self::doStrReplace( $pluginCompletePath, 'BEA\\PB', $pluginNamespace );

		// constants prefix
		$pluginConstsPrefix = self::askAndConfirm( $io, "\nWhat is your constants prefix ? (e.g: 'MY_PLUGIN_') " );
		if( '_' !== substr( $pluginConstsPrefix, -1 ) ) {
			$pluginConstsPrefix = $pluginConstsPrefix . '_';
		}
		self::doStrReplace( $pluginCompletePath, 'BEA_PB_', $pluginConstsPrefix );

		// view folder
		$pluginViewFolderName = self::askAndConfirm( $io, "\nWhat is your plugin's view folder name ? (e.g: 'my-plugin') " );
		self::doStrReplace( $pluginCompletePath, 'bea-pb', $pluginViewFolderName );

		$io->write( "\nYour plugin is ready ! :)" );
	}

	/**
	 * Ask the user for a value and then ask for confirmation
	 *
	 * @param IOInterface $io           Composer IO object
	 * @param string      $question     question to ask to the user
	 * @param string      $confirmation confirmation message
	 *
	 * @return string
	 */
	protected static function askAndConfirm( IOInterface $io, $question, $confirmation = '' ) {
		$value = '';
		while( empty( $value ) ) {
			$value = trim( $io->ask( $question ) );
		}

		if( empty( $confirmation ) ) {
			$confirm_msg = sprintf( 'You have enter %s. Is that Ok ? ', $value );
		} else {
			$confirm_msg = sprintf( $confirmation, $value );
		}

		if( $io->askConfirmation( $confirm_msg ) ) {
			return $value;
		}

		return self::askAndConfirm( $io, $question, $confirmation );
	}

	/**
	 * Do a search/replace in folder
	 *
	 * @param string $path
	 * @param string $search
	 * @param string $replace
	 * @param string $extension
	 *
	 * @return bool
	 * @internal param string $needle what to replace
	 */
	protected static function doStrReplace( $path, $search, $replace = '', $extension = 'php' ) {
		if( empty( $path ) || empty( $search ) ) {
			return false;
		}

		$path = realpath( $path );
		$fileList = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path ), \RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $fileList as $item ) {
			if ( $item->isFile() && false !== stripos( $item->getPathName(), $extension ) ) {
				$content = file_get_contents( $item->getPathName() );
				file_put_contents( $item->getPathName(), str_replace( $search, $replace, $content ) );
			}
		}

		return true;
	}

	/**
	 * Setup a dummy package for Composer to download
	 *
	 * @return Package
	 */
	protected static function getPluginBoilerplatePackage() {
		$p = new Package( 'plugin-boilerplate', 'dev-master', 'Latest' );
		$p->setType('library');
		$p->setDistType( 'zip' );
		$p->setDistUrl( 'https://github.com/BeAPI/bea-plugin-boilerplate/archive/master.zip' );

		return $p;
	}
}