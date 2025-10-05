<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Init
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Form_Post_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    if (! defined('WPINC')) {
      die;
    }

    $this->plugin_name = 'form-post';
    $this->version = FORM_POST_VERSION;

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->define_rest_api_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Form_Post_Loader. Orchestrates the hooks of the plugin.
   * - Form_Post_i18n. Defines internationalization functionality.
   * - Form_Post_Admin. Defines all hooks for the admin area.
   * - Form_Post_Public. Defines all hooks for the public side of the site.
   * - Form_Post_Rest_API. Defines all REST API endpoints.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {
    require_once FORM_POST_PLUGIN_DIR . 'includes/class-database.php';
    require_once FORM_POST_PLUGIN_DIR . 'includes/class-admin.php';
    require_once FORM_POST_PLUGIN_DIR . 'includes/class-frontend.php';
    require_once FORM_POST_PLUGIN_DIR . 'includes/class-rest-api.php';
    require_once FORM_POST_PLUGIN_DIR . 'includes/class-settings.php';
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Form_Post_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {
    add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
  }

  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain()
  {
    load_plugin_textdomain(
      'form-post',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {
    $plugin_admin = new Form_Post_Admin($this->get_plugin_name(), $this->get_version());

    // Admin menu
    add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));

    // Admin scripts and styles
    add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
    add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {
    $plugin_public = new Form_Post_Frontend($this->get_plugin_name(), $this->get_version());

    // Public scripts and styles
    add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
    add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));

    // Register shortcode
    add_action('init', array($plugin_public, 'register_shortcodes'));
  }

  /**
   * Register all of the hooks related to the REST API functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_rest_api_hooks()
  {
    $plugin_rest_api = new Form_Post_Rest_API($this->get_plugin_name(), $this->get_version());

    // Register REST API routes
    add_action('rest_api_init', array($plugin_rest_api, 'register_routes'));
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }

  /**
   * Run the plugin.
   */
  public function run()
  {
    // All hooks are registered in the constructor
  }
}
