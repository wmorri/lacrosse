<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'developer_lacrosse_new');

/** MySQL database username */
define('DB_USER', 'lacrossenew123');

/** MySQL database password */
define('DB_PASSWORD', 'lacrossenew!@#');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'QMyvB>Q7l==/PZdxO&+Sha5wrzE|)C%QFY^Fe+`>CyZ*+V5^>/0!BV@gWO6,/dG(');
define('SECURE_AUTH_KEY',  'C^nk^ TSX$ieH J<b[e:y4Am$+;S-8=A]V*mkYciPMp@_)6`(<3Va? t/~ !-aHz');
define('LOGGED_IN_KEY',    'YZ%W.Yxi5ZQw1C`f*?o9zPnIOD<SlX7}mQ6FTVeC6,hCx-/sXN0Gzrf/|9}bb0| ');
define('NONCE_KEY',        '@_w[>_pZ]gv6A&3s&d:Ex.[,3GGL|,DqwfEF4Eg:1 ?%of:7I+ O++)-#]5g-@),');
define('AUTH_SALT',        'C)Y*Fy]d|,J,N%jE^O<REjf-.aU +!FwQ4Y0-4m,uJ$d3VXBMn=MJfcDG0=X+AHz');
define('SECURE_AUTH_SALT', '%FHx@kID1Uhi,e{}HL+)7:),L{75]/$5Vyb:>S!,?%Rr$31_unT;C6/BGe.Nk0t|');
define('LOGGED_IN_SALT',   '[2wn]9x|JEyOHiS~*FNqy ?NT,[7A5;o7j1+gI:ETm,poZtrngaSz|{kdRZAflYi');
define('NONCE_SALT',       'p|wyu=$7G*<.z2QijE8S2}YRxq/y5x|=f)PpF$]nwSyKb8i-c-#o8_09J:CARm7R');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
