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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpali1');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'rootroot');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Ja/g(2r))/q-%?npU!f)~Vu&X{>c?1J5<oXxnyff6z#x0c.{qywJDqZ$%$FhS!2Y');
define('SECURE_AUTH_KEY',  '^b|a SAP$RbTjbMR*Q+T>DpRf5Y<@t/f&9Z)KX Ani 88T!2bx}y?z4&& </OGE-');
define('LOGGED_IN_KEY',    ')nE:F[t?m*:_ 7F|M,&3?G,}Es1vEw~[9BInNozq3u0v`YCn-czV23ORLZ l1|JO');
define('NONCE_KEY',        ':RH y7)C.P6P<J9y%R _qS9AGuax!+.ew:8yQ|3Pt2dExX},ah$b=A&eMax_-=83');
define('AUTH_SALT',        'nVq3g]jI&&7M|xi]*{&wZXW@}O7V?[@^=vgUoBM@2wqFTAT}n*-p1i ex,%+KDEd');
define('SECURE_AUTH_SALT', 'K3s%$P=$)$Z*&v)_yK?6MFkqvrO:[!^iR+.^&A8b{5WJ2{Ivl=cos0=Idelix%d~');
define('LOGGED_IN_SALT',   'gLa@7s;%-[sdqD`C#i9(uA|?O@%F0bP&O>6+Lm[$J OLt-o(#KSj3Dcmgh15AUiG');
define('NONCE_SALT',       'v,*+#R5I%(>?{I;<;+_}lJWS@1Lh9Z]Q4`7|<.v42_3H3`aUZYn;U RW<on#P,>w');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
