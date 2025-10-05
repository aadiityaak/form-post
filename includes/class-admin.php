<?php

/**
 * Admin class for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Admin
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param    string    $plugin_name       The name of this plugin.
   * @param    string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      $this->plugin_name,
      plugin_dir_url(__FILE__) . '../assets/css/admin.css',
      array(),
      $this->version,
      'all'
    );
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script(
      $this->plugin_name,
      plugin_dir_url(__FILE__) . '../assets/js/admin.js',
      array('jquery'),
      $this->version,
      false
    );
  }

  /**
   * Add plugin admin menu
   *
   * @since    1.0.0
   */
  public function add_plugin_admin_menu()
  {
    // Add main menu
    add_menu_page(
      'Webinar Registration',
      'Webinar Registration',
      'manage_options',
      'webinar-registration',
      array($this, 'display_plugin_setup_page'),
      'dashicons-list-view',
      6
    );

    // Add submenu for registrations
    add_submenu_page(
      'webinar-registration',
      'Registrations',
      'Registrations',
      'manage_options',
      'webinar-registrations',
      array($this, 'display_registrations_page')
    );

    // Add submenu for settings
    add_submenu_page(
      'webinar-registration',
      'Settings',
      'Settings',
      'manage_options',
      'webinar-settings',
      array($this, 'display_settings_page')
    );
  }

  /**
   * Display the plugin setup page
   *
   * @since    1.0.0
   */
  public function display_plugin_setup_page()
  {
    include_once 'partials/webinar-registration-admin-display.php';
  }

  /**
   * Display the registrations page
   *
   * @since    1.0.0
   */
  public function display_registrations_page()
  {
    include_once 'partials/webinar-registration-registrations-display.php';
  }

  /**
   * Display the settings page
   *
   * @since    1.0.0
   */
  public function display_settings_page()
  {
    include_once 'partials/webinar-registration-settings-display.php';
  }

  /**
   * Handle bulk actions for registrations
   *
   * @since    1.0.0
   */
  public function handle_bulk_actions()
  {
    if (isset($_POST['action']) && isset($_POST['registration_ids'])) {
      $action = $_POST['action'];
      $registration_ids = $_POST['registration_ids'];

      if (!empty($registration_ids) && is_array($registration_ids)) {
        foreach ($registration_ids as $id) {
          if ($action === 'approve') {
            Form_Post_Database::update_registration($id, array('status' => 'diterima'));
          } elseif ($action === 'reject') {
            Form_Post_Database::update_registration($id, array('status' => 'ditolak'));
          } elseif ($action === 'delete') {
            Form_Post_Database::delete_registration($id);
          }
        }

        // Redirect to avoid form resubmission
        wp_redirect(admin_url('admin.php?page=webinar-registrations&status=' . $action));
        exit;
      }
    }
  }

  /**
   * Handle export functionality
   *
   * @since    1.0.0
   */
  public function handle_export()
  {
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
      // Check nonce
      if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'export_registrations')) {
        wp_die('Security check failed');
      }

      // Get registrations
      $registrations = Form_Post_Database::get_registrations(array(
        'limit' => 9999,
        'status' => isset($_GET['status']) ? $_GET['status'] : ''
      ));

      // Set headers for CSV download
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="webinar-registrations.csv"');

      // Open output stream
      $output = fopen('php://output', 'w');

      // Add CSV headers
      fputcsv($output, array(
        'ID',
        'Nama Lengkap',
        'Email',
        'Nomor Telepon',
        'Instansi',
        'Jabatan',
        'Alamat',
        'Keterangan',
        'Status',
        'Tanggal Pendaftaran'
      ));

      // Add data rows
      foreach ($registrations as $registration) {
        fputcsv($output, array(
          $registration->id,
          $registration->nama_lengkap,
          $registration->email,
          $registration->nomor_telepon,
          $registration->instansi,
          $registration->jabatan,
          $registration->alamat,
          $registration->keterangan,
          $registration->status,
          $registration->created_at
        ));
      }

      // Close output stream
      fclose($output);
      exit;
    }
  }

  /**
   * Add admin notices
   *
   * @since    1.0.0
   */
  public function add_admin_notices()
  {
    if (isset($_GET['status'])) {
      $status = $_GET['status'];

      if ($status === 'approved') {
        echo '<div class="notice notice-success is-dismissible"><p>Registrations approved successfully.</p></div>';
      } elseif ($status === 'rejected') {
        echo '<div class="notice notice-success is-dismissible"><p>Registrations rejected successfully.</p></div>';
      } elseif ($status === 'deleted') {
        echo '<div class="notice notice-success is-dismissible"><p>Registrations deleted successfully.</p></div>';
      }
    }

    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
      echo '<div class="notice notice-success is-dismissible"><p>Settings updated successfully.</p></div>';
    }
  }
}
