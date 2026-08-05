<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'iotiweb' );

/** Database username */
define( 'DB_USER', 'iotiweb' );

/** Database password */
define( 'DB_PASSWORD', 'PnBkEJWPWcfWwdT' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '=26x#f5:Ex`pYc~TxlXsrQ.>Lm^;UR)<!wI BwY8bl7FN$c+/ :$R@.FLVQQoQpa' );
define( 'SECURE_AUTH_KEY',  'FImp+4>) uT*(HMELpvZy}o=2^dW6)G1#c&[U*KZDBZ22OD=%v=tx`H#P$*rhMvv' );
define( 'LOGGED_IN_KEY',    'w4p@1j9uoTDn)$ 4?rH;1v2[cH2s&cbXuQ^Ksvnt+w^1p,{~BgJQ>6a*G|1tEA=Q' );
define( 'NONCE_KEY',        'V[2R?MFxVsk}H^3v84 SJ%%I T]oD`u|b[0+xzy.y}pvQuED~HtuR*V$*JT;}uES' );
define( 'AUTH_SALT',        'G 0JfvNvb.%G:dqJlM{O{;r`edg`l/PCgI3^eLdcKwnu#~Mc{Uk0_E(FLJvVwX9r' );
define( 'SECURE_AUTH_SALT', 't7ko`6K=?pTK$zSP%0~C{vEa#!%o-)F@}$Ae^{[u=dl}ycmR}*>E#Bn$OsGB?4r{' );
define( 'LOGGED_IN_SALT',   '`,UZdc1^o4_a?2$B?<g)6|0=QjgD3/t05v|o$BzwrY[-PFFSw5_<=OI)<@T8r)_R' );
define( 'NONCE_SALT',       '04Y=Mq4;3AgK$,IF_JvAlIN!*o!wxECED~cg>qzy/uFT*Gn04qHkxdpI71x-On$b' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'i2iweb';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/** Turn off wordpress core updates */
	
define( 'WP_AUTO_UPDATE_CORE', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
