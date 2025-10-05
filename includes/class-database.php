<?php

/**
 * Database class for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Database
{

  /**
   * Table name for webinar registrations
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $table_name    The table name for webinar registrations.
   */
  private static $table_name = 'webinar_registrations';

  /**
   * Table name for settings
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $settings_table_name    The table name for settings.
   */
  private static $settings_table_name = 'webinar_settings';

  /**
   * Activate the plugin
   *
   * @since    1.0.0
   */
  public static function activate()
  {
    self::create_tables();
    self::insert_default_settings();
  }

  /**
   * Deactivate the plugin
   *
   * @since    1.0.0
   */
  public static function deactivate()
  {
    // Clean up if needed
  }

  /**
   * Create database tables
   *
   * @since    1.0.0
   * @access   private
   */
  private static function create_tables()
  {
    global $wpdb;

    $table_name = $wpdb->prefix . self::$table_name;
    $settings_table_name = $wpdb->prefix . self::$settings_table_name;

    $charset_collate = $wpdb->get_charset_collate();

    // Create webinar registrations table
    $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            nama_lengkap varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            nomor_telepon varchar(50) NOT NULL,
            instansi varchar(255) DEFAULT NULL,
            jabatan varchar(255) DEFAULT NULL,
            alamat text DEFAULT NULL,
            keterangan text DEFAULT NULL,
            status enum('pending','diterima','ditolak') NOT NULL DEFAULT 'pending',
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

    // Create settings table
    $settings_sql = "CREATE TABLE $settings_table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($settings_sql);
  }

  /**
   * Insert default settings
   *
   * @since    1.0.0
   * @access   private
   */
  private static function insert_default_settings()
  {
    global $wpdb;

    $settings_table_name = $wpdb->prefix . self::$settings_table_name;

    $default_settings = array(
      'admin_email' => get_option('admin_email'),
      'email_subject_admin' => 'Pendaftaran Webinar Baru',
      'email_subject_user' => 'Konfirmasi Pendaftaran Webinar',
      'email_template_admin' => 'Ada pendaftaran webinar baru dari {nama_lengkap} ({email})',
      'email_template_user' => 'Terima kasih telah mendaftar webinar kami. Kami akan menghubungi Anda segera.',
      'enable_captcha' => '0',
      'captcha_site_key' => '',
      'captcha_secret_key' => ''
    );

    foreach ($default_settings as $key => $value) {
      $wpdb->insert(
        $settings_table_name,
        array(
          'setting_key' => $key,
          'setting_value' => $value
        ),
        array('%s', '%s')
      );
    }
  }

  /**
   * Get table name for webinar registrations
   *
   * @since    1.0.0
   * @return   string    The table name
   */
  public static function get_table_name()
  {
    global $wpdb;
    return $wpdb->prefix . self::$table_name;
  }

  /**
   * Get settings table name
   *
   * @since    1.0.0
   * @return   string    The settings table name
   */
  public static function get_settings_table_name()
  {
    global $wpdb;
    return $wpdb->prefix . self::$settings_table_name;
  }

  /**
   * Get registration by ID
   *
   * @since    1.0.0
   * @param     int    $id    The registration ID
   * @return    object|null    The registration object or null if not found
   */
  public static function get_registration($id)
  {
    global $wpdb;

    $table_name = self::get_table_name();

    return $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE id = %d",
      $id
    ));
  }

  /**
   * Get all registrations
   *
   * @since    1.0.0
   * @param     array    $args    Arguments for query
   * @return    array    Array of registration objects
   */
  public static function get_registrations($args = array())
  {
    global $wpdb;

    $table_name = self::get_table_name();

    $defaults = array(
      'status' => '',
      'limit' => 50,
      'offset' => 0,
      'orderby' => 'created_at',
      'order' => 'DESC'
    );

    $args = wp_parse_args($args, $defaults);

    $where = '';
    if (!empty($args['status'])) {
      $where = $wpdb->prepare("WHERE status = %s", $args['status']);
    }

    $sql = "SELECT * FROM $table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";

    return $wpdb->get_results($wpdb->prepare($sql, $args['limit'], $args['offset']));
  }

  /**
   * Count registrations by status
   *
   * @since    1.0.0
   * @param     string    $status    The status to count
   * @return    int       The count of registrations
   */
  public static function count_registrations($status = '')
  {
    global $wpdb;

    $table_name = self::get_table_name();

    if (!empty($status)) {
      return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE status = %s",
        $status
      ));
    } else {
      return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
  }

  /**
   * Insert new registration
   *
   * @since    1.0.0
   * @param     array    $data    The registration data
   * @return    int|false    The registration ID or false on failure
   */
  public static function insert_registration($data)
  {
    global $wpdb;

    $table_name = self::get_table_name();

    $result = $wpdb->insert(
      $table_name,
      $data,
      array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result !== false) {
      return $wpdb->insert_id;
    }

    return false;
  }

  /**
   * Update registration
   *
   * @since    1.0.0
   * @param     int      $id     The registration ID
   * @param     array    $data   The data to update
   * @return    bool     True on success, false on failure
   */
  public static function update_registration($id, $data)
  {
    global $wpdb;

    $table_name = self::get_table_name();

    return $wpdb->update(
      $table_name,
      $data,
      array('id' => $id),
      array('%s', '%s', '%s'),
      array('%d')
    ) !== false;
  }

  /**
   * Delete registration
   *
   * @since    1.0.0
   * @param     int    $id    The registration ID
   * @return    bool   True on success, false on failure
   */
  public static function delete_registration($id)
  {
    global $wpdb;

    $table_name = self::get_table_name();

    return $wpdb->delete(
      $table_name,
      array('id' => $id),
      array('%d')
    ) !== false;
  }

  /**
   * Get setting value
   *
   * @since    1.0.0
   * @param     string     $key    The setting key
   * @param     mixed      $default    Default value if setting not found
   * @return    mixed      The setting value
   */
  public static function get_setting($key, $default = '')
  {
    global $wpdb;

    $settings_table_name = self::get_settings_table_name();

    $value = $wpdb->get_var($wpdb->prepare(
      "SELECT setting_value FROM $settings_table_name WHERE setting_key = %s",
      $key
    ));

    return $value !== null ? $value : $default;
  }

  /**
   * Update setting value
   *
   * @since    1.0.0
   * @param     string    $key     The setting key
   * @param     mixed     $value   The setting value
   * @return    bool      True on success, false on failure
   */
  public static function update_setting($key, $value)
  {
    global $wpdb;

    $settings_table_name = self::get_settings_table_name();

    return $wpdb->update(
      $settings_table_name,
      array('setting_value' => $value),
      array('setting_key' => $key),
      array('%s'),
      array('%s')
    ) !== false;
  }
}
