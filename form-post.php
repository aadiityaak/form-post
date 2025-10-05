<?php

/**
 * Plugin Name: Form Post
 * Plugin URI: https://websweetstudio.com
 * Description: Plugin WordPress untuk formulir pendaftaran webinar dengan database dan manajemen admin
 * Version: 1.0.0
 * Author: Websweetstudio.com - Aditya Kristyanto
 * Author URI: https://websweetstudio.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: form-post
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

define('FORM_POST_VERSION', '1.0.0');
define('FORM_POST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORM_POST_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once FORM_POST_PLUGIN_DIR . 'includes/class-form-post-init.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_form_post()
{
  $plugin = new Form_Post_Init();
  $plugin->run();
}

// Activation hook
register_activation_hook(__FILE__, 'activate_form_post');
function activate_form_post()
{
  require_once FORM_POST_PLUGIN_DIR . 'includes/class-database.php';
  Form_Post_Database::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'deactivate_form_post');
function deactivate_form_post()
{
  require_once FORM_POST_PLUGIN_DIR . 'includes/class-database.php';
  Form_Post_Database::deactivate();
}

// Run the plugin
run_form_post();
