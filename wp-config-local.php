
<?php
if ( is_file( dirname( __FILE__ ) . '/../env_dev' ) && is_file( dirname( __FILE__ ) . '/wp-config-dev.php' ) ) {
	require( dirname( __FILE__ ) . '/wp-config-dev.php' );
	return true;
} elseif( is_file( dirname( __FILE__ ) . '/../env_qualif' ) && is_file( dirname( __FILE__ ) . '/wp-config-qualif.php' ) ) {
	require( dirname( __FILE__ ) . '/wp-config-qualif.php' );
	return true;
}

define( 'DB_NAME', '%DB_NAME%' );
define( 'DB_USER', '%DB_USER%' );
define( 'DB_PASSWORD', '%DB_PASSWORD%' );
