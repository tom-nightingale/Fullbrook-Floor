<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fullbrook-floor.vm' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '7=ihalo~v-jtki*5$p=XCw>jqhoq-M=ZN#S&ND8]3K3TK>P1uj?xW]gQF@=!FH(i' );
define( 'SECURE_AUTH_KEY',  'y]3m2z%&c~JN%&5_L_d=6X) ]AzJXNW9aF4f_.F}S~KXXZtY(]x(9(<Xvd]g3ulK' );
define( 'LOGGED_IN_KEY',    '3nH^ j08cH0LJW;rI3IX_2G<yssz 7n^b%wY3R5|0FcDUOI7HZP-DERq#HrzNUP4' );
define( 'NONCE_KEY',        '$QLA%YaEnb((&IV.:<vF*lfY5Y_V6Ial@4(6|KlmC4Z]n-wb1Fn_XS6iR42fXCoh' );
define( 'AUTH_SALT',        'E}HF=GX&9MwI,$0jF`&yL8dLhpq4(SD5+Zi(IA3a@sAlDe1(T!W5Cg~@).!L3(3E' );
define( 'SECURE_AUTH_SALT', '#{W06261<|8p6f[Cky=5e{gU ^!F$WKF!1>N50g)3YJ]96*[n{*4jfFS]N` &U/h' );
define( 'LOGGED_IN_SALT',   'ghu$QU B#U=W|dtl%{,IxtgJ,YerUniOR>prl?9 Q?bnBMio4QYu98t %a@ NzDe' );
define( 'NONCE_SALT',       'D#]]<x4G?wy8=}*{qcJnR:#L_Y5o+2F$tFG_vY^$<nODsrutp>;OBex-l~P0R0M3' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'league_';

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
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
