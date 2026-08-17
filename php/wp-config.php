<?php
/**
 * The base configuration for WordPress.
 *
 * Lives one directory above the WordPress install (wp-load.php looks here
 * automatically) so it never sits inside the Composer-managed wordpress/
 * tree, which gets wiped and rewritten on every `composer install`.
 *
 * Database credentials, secret keys, and the table prefix are read from
 * environment variables (see .env and docker-compose.yml) instead of being
 * hardcoded, so this file is safe to commit.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/#moving-wp-config-php
 */

define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'db' );
define( 'DB_CHARSET', getenv( 'WORDPRESS_DB_CHARSET' ) ?: 'utf8mb4' );
define( 'DB_COLLATE', getenv( 'WORDPRESS_DB_COLLATE' ) ?: '' );

/**
 * Authentication unique keys and salts.
 *
 * Generate real values with https://api.wordpress.org/secret-key/1.1/salt/
 * (or `openssl rand -hex 32`) and set them as WORDPRESS_* environment
 * variables — never hardcode them.
 */
define( 'AUTH_KEY', getenv( 'WORDPRESS_AUTH_KEY' ) );
define( 'SECURE_AUTH_KEY', getenv( 'WORDPRESS_SECURE_AUTH_KEY' ) );
define( 'LOGGED_IN_KEY', getenv( 'WORDPRESS_LOGGED_IN_KEY' ) );
define( 'NONCE_KEY', getenv( 'WORDPRESS_NONCE_KEY' ) );
define( 'AUTH_SALT', getenv( 'WORDPRESS_AUTH_SALT' ) );
define( 'SECURE_AUTH_SALT', getenv( 'WORDPRESS_SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_SALT', getenv( 'WORDPRESS_LOGGED_IN_SALT' ) );
define( 'NONCE_SALT', getenv( 'WORDPRESS_NONCE_SALT' ) );

/**
 * WordPress database table prefix.
 */
$table_prefix = getenv( 'WORDPRESS_TABLE_PREFIX' ) ?: 'wp_';

/**
 * For developers: WordPress debugging mode.
 */
define( 'WP_DEBUG', filter_var( getenv( 'WORDPRESS_DEBUG' ) ?: false, FILTER_VALIDATE_BOOLEAN ) );

/**
 * Filesystem access method. Safe as 'direct' here since PHP always runs
 * as www-data in this containerized setup.
 */
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', 0755 );
define( 'FS_CHMOD_FILE', 0644 );

/**
 * Absolute path to the WordPress directory.
 *
 * This file is copied to /var/www/wp-config.php in the container, one
 * level above the /var/www/html docroot — hence '/html/' here, not the
 * host-side 'wordpress' folder name (irrelevant once inside the container).
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/html/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
