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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'Zdr)pIz>6im9*zs:3oPgEy+gD].R0%J{OAo6]0[g$,xLg2C|X/p:JXwDCA,?}D=c' );
define( 'SECURE_AUTH_KEY',   '0rY^!jc9!zg`hF{/3jp(;Di!lQ}g +~6[gTj9;dAWK5HJco&~v5}CRI|mr>^[n6t' );
define( 'LOGGED_IN_KEY',     'WCXEur3jCBVL+vF2IK*R1[t|6?0VYBRcF7=Q i(hq6bR9?+3Z]+I)X]U5h{=//' );
define( 'NONCE_KEY',         'R&S ]eQ-;645V;y<kfZB4hQ>T6lh94^D=z+w88lx-ZsR$D+3#_;d;k?h:OX/V~+,' );
define( 'AUTH_SALT',         'd4=BRNY~J<<-d~ZE:O-7%8pVLI0ne$A~PRmyD9mIP2Rkb%&d}60I_SLsBVh!:`;J' );
define( 'SECURE_AUTH_SALT',  '5J=IfwLY9@x?-WIMe|g-^o4]-BVdB+kH*;ljUATc@MQ@P0^4h(3.IggjUOx[)5rX' );
define( 'LOGGED_IN_SALT',    '6>y?PTDj<{#NWz7liUe%6I0@_%%[(@q<-^P^jU:g!%jZO.8AndEb|F-J*9|$|7DY' );
define( 'NONCE_SALT',        ' F.Qt_KQTjwRG:-/P,,N4rPtd`,q-zi%co&yAS;i}Gf]F-#d:,L5S^7%7;A Fx_1' );
define( 'WP_CACHE_KEY_SALT', 'd99;{a!2Fiu<`5JT4o^l&rzbL:Q@2uztQa/*vAaxq%2?&%cAoyC[7m+@F D8=MBQ' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
