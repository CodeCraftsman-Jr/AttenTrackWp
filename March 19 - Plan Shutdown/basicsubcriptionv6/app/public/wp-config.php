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

// ** Enable Debug logging to the /wp-content/debug.log file **
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost:10005' );


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
define( 'AUTH_KEY',          'NB-?cM3b# UofS`C35H!bb~MU`4a$GnOobK{ffN.9nwHI*PJHe|(detRxCa]|6&O' );
define( 'SECURE_AUTH_KEY',   'ce@< tGSu)MR<D|~2nk-_]EEg:UT8Hzq{lkj^E;?7S:m{M/Kx~5ez8=T>Fc|S74O' );
define( 'LOGGED_IN_KEY',     '_onL~BRF]]Yd$z<Kbq+X=f|0{ wd_t W2_6:ah{l6y_Znq/Y?[Ef+CMsYY!=lD,q' );
define( 'NONCE_KEY',         ',oHCMWv^0)dK&pw{qHVObi^k=Znz}7H~Y#xN/yKg;_00IMzWzQl%/dAy$nb6hTf)' );
define( 'AUTH_SALT',         'Xdgg@(x=Ia|uoq$.t|bQA8E^Ms{1gRT.j9JfIB$4Eixm?n].a~:,(h/ODkO$8.Ey' );
define( 'SECURE_AUTH_SALT',  'Z]f+X1_)HF@CpF0cM@Z]r,dTnbAd4~U4v9yM)||[|UG>#1^{E(U7a?l`QI89=j2z' );
define( 'LOGGED_IN_SALT',    '~H]Q$a9o`kl%SXz2;/bPnf-Nn;6|7T1S#@*L8BE|Kssn+b*kmmY02f/f1RbIrMw/' );
define( 'NONCE_SALT',        'gYtr?1$?2<G1rGnVT_uAzt1bByeS]/98pUm@[;xKB~^8fR,w)1`X(>=ILl>8!5gd' );
define( 'WP_CACHE_KEY_SALT', '`0S43oXQf)wfqc#iHsrI*n_4iLjY~1s{S9UFJ@P1m|}vrA2qLV&lk5>mzRoR{X[f' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
