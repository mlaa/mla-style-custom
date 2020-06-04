<?php
/**
 * Plugin Name:     MLA Style Custom
 * Plugin URI:      https://github.com/mlaa/mla-style-custom
 * Description:     Miscellaneous actions & filters for Humanities Commons.
 * Author:          MLA
 * Author URI:      https://github.com/mlaa
 * Text Domain:     mla-style-custom
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Mla_Style_Custom
 */

/**
 * require child plugins then initiate them,
 * require_once trailingslashit( __DIR__ ) . 'includes/file.php';
 */
require_once trailingslashit( __DIR__ ) . 'includes/elasticpress.php';
require_once trailingslashit( __DIR__ ) . 'includes/style-author-bios.php';
require_once trailingslashit( __DIR__ ) . 'includes/wordpress.php';
require_once trailingslashit( __DIR__ ) . 'includes/author-order.php';
