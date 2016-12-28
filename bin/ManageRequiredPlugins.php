<?php

namespace BEA\ComposerInstaller;

use Composer\Installer\PackageEvent;
use Composer\Package\CompletePackage;

class ManageRequiredPlugins {

	/**
	 * Add plugin install via composer to the .gitignore
	 *
	 * @param PackageEvent $event composer event
	 *
	 * @return bool
	 */
	public static function postPackageInstall( PackageEvent $event ) {

		$package = $event->getOperation()->getPackage();
		if( !in_array( $package->getType(), array( 'wordpress-muplugin', 'wordpress-plugin', 'wordpress-theme' ) ) ) {
			return false;
		}

		$plugin_directory = self::getDirectoryType( $package );
		if( false === $plugin_directory ) {
			return false;
		}

		$plugin_name = self::getPluginName( $package );
		$finalPath = self::getGitignoreFile( $event->getComposer() );

		$rules = array( '/content/' . $plugin_directory . '/' . $plugin_name . '/' );

		return self::insertWithMarkers( $finalPath, $plugin_name, $rules );
	}

	/**
	 * Remove plugin install via composer to the .gitignore
	 *
	 * @param PackageEvent $event composer event
	 *
	 * @return bool
	 */
	public static function postPackageUninstall( PackageEvent $event ) {
		$package = $event->getOperation()->getPackage();
		if( !in_array( $package->getType(), array( 'wordpress-muplugin', 'wordpress-plugin', 'wordpress-theme' ) ) ) {
			return false;
		}

		$plugin_name = self::getPluginName( $package );
		$finalPath = self::getGitignoreFile( $event->getComposer() );

		return self::removeWithMarkers( $finalPath, $plugin_name );
	}

	/**
	 * Extract a plugin's name from a composer package
	 *
	 * @param CompletePackage $package
	 *
	 * @return string|bool
	 */
	protected static function getPluginName( $package ) {

		// Look first for custom name in extra
		$package_extra = $package->getExtra();
		if ( isset( $package_extra['installer-name'] ) && !empty( $package_extra['installer-name'] ) ) {
			return $package_extra['installer-name'];
		}

		// Fallback to package's name
		$package_name = explode( '/', $package->getName() );
		if( !is_array( $package_name ) || 2 > count( $package_name ) ) {
			return false;
		}

		return $package_name[1];
	}

	/**
	 * Get the directory name from the package's type
	 *
	 * @param CompletePackage $package
	 *
	 * @return string|bool
	 */
	protected static function getDirectoryType( $package ) {
		switch( $package->getType() ) {
			case "wordpress-muplugin":
				return 'mu-plugins';
			case "wordpress-plugin":
				return 'plugins';
			case "wordpress-theme":
				return 'themes';
			default:
				return false;
		}
	}

	/**
	 * Get the .gitignore file path
	 *
	 * @return string
	 */
	protected static function getGitignoreFile( $composer ) {
		return dirname( $composer->getConfig()->get( 'vendor-dir' ) ) . '/.gitignore';
	}

	/**
	 * Insert lines of text into a file
	 *
	 * Extract from wp-admin/includes/misc.php L102
	 *
	 * @param string $filename  file to insert to
	 * @param string $marker    delimiter marker
	 * @param array  $insertion array of lines to insert to the file
	 *
	 * @return bool
	 */
	protected static function insertWithMarkers( $filename, $marker, $insertion ) {
		if (!file_exists( $filename ) || is_writeable( $filename ) ) {
			if (!file_exists( $filename ) ) {
				$markerdata = '';
			} else {
				$markerdata = explode( "\n", implode( '', file( $filename ) ) );
			}

			if ( !$f = @fopen( $filename, 'w' ) )
				return false;

			$foundit = false;
			if ( $markerdata ) {
				$state = true;
				foreach ( $markerdata as $n => $markerline ) {
					if (strpos($markerline, '# BEGIN ' . $marker . ' #') !== false)
						$state = false;
					if ( $state ) {
						if ( $n + 1 < count( $markerdata ) )
							fwrite( $f, "{$markerline}\n" );
						else
							fwrite( $f, "{$markerline}" );
					}
					if (strpos($markerline, '# END ' . $marker . ' #') !== false) {
						fwrite( $f, "# BEGIN {$marker} #\n" );
						if ( is_array( $insertion ))
							foreach ( $insertion as $insertline )
								fwrite( $f, "{$insertline}\n" );
						fwrite( $f, "# END {$marker} #\n" );
						$state = true;
						$foundit = true;
					}
				}
			}
			if (!$foundit) {
				fwrite( $f, "\n# BEGIN {$marker} #\n" );
				foreach ( $insertion as $insertline )
					fwrite( $f, "{$insertline}\n" );
				fwrite( $f, "# END {$marker} #\n" );
			}
			fclose( $f );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete marker bloc from a file
	 *
	 * @param string $filename  file to delete from
	 * @param string $marker    delimiter marker
	 *
	 * @return bool
	 */
	protected static function removeWithMarkers( $filename, $marker ) {
		if (!file_exists( $filename ) || is_writeable( $filename ) ) {
			if (!file_exists( $filename ) ) {
				$markerdata = '';
			} else {
				$start = null;
				$end   = null;

				$markerdata = explode( "\n", implode( '', file( $filename ) ) );
				foreach ( $markerdata as $n => $markerline ) {
					if( false !== strpos($markerline, '# BEGIN ' . $marker . ' #') ) {
						$start = $n;
					}

					if( false !== strpos($markerline, '# END ' . $marker . ' #') ) {
						$end = $n;
						break;
					}
				}
			}

			if ( !$f = @fopen( $filename, 'w' ) )
				return false;

			$foundit = false;
			if ( $markerdata ) {
				foreach ( $markerdata as $n => $markerline ) {
					if( $n < $start || $n > $end ) {
						fwrite( $f, "{$markerline}\n" );
					}
				}
			}
			fclose( $f );
			return true;
		} else {
			return false;
		}
	}
}