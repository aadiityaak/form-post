<?php

/**
 * Settings class for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Settings
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
   * Register settings
   *
   * @since    1.0.0
   */
  public function register_settings()
  {
    register_setting(
      'form_post_settings',
      'form_post_settings',
      array($this, 'sanitize_settings')
    );

    // Email Settings Section
    add_settings_section(
      'form_post_email_settings',
      'Email Settings',
      array($this, 'email_settings_callback'),
      'form_post_settings'
    );

    add_settings_field(
      'admin_email',
      'Admin Email',
      array($this, 'text_field_callback'),
      'form_post_settings',
      'form_post_email_settings',
      array(
        'id' => 'admin_email',
        'description' => 'Email address to receive registration notifications'
      )
    );

    add_settings_field(
      'email_subject_admin',
      'Admin Email Subject',
      array($this, 'text_field_callback'),
      'form_post_settings',
      'form_post_email_settings',
      array(
        'id' => 'email_subject_admin',
        'description' => 'Subject line for admin notification emails'
      )
    );

    add_settings_field(
      'email_template_admin',
      'Admin Email Template',
      array($this, 'textarea_field_callback'),
      'form_post_settings',
      'form_post_email_settings',
      array(
        'id' => 'email_template_admin',
        'description' => 'Email template for admin notifications. Available tags: {nama_lengkap}, {email}, {nomor_telepon}, {instansi}, {jabatan}'
      )
    );

    add_settings_field(
      'email_subject_user',
      'User Email Subject',
      array($this, 'text_field_callback'),
      'form_post_settings',
      'form_post_email_settings',
      array(
        'id' => 'email_subject_user',
        'description' => 'Subject line for user confirmation emails'
      )
    );

    add_settings_field(
      'email_template_user',
      'User Email Template',
      array($this, 'textarea_field_callback'),
      'form_post_settings',
      'form_post_email_settings',
      array(
        'id' => 'email_template_user',
        'description' => 'Email template for user confirmations. Available tags: {nama_lengkap}, {email}'
      )
    );

    // Captcha Settings Section
    add_settings_section(
      'form_post_captcha_settings',
      'Captcha Settings',
      array($this, 'captcha_settings_callback'),
      'form_post_settings'
    );

    add_settings_field(
      'enable_captcha',
      'Enable Captcha',
      array($this, 'checkbox_field_callback'),
      'form_post_settings',
      'form_post_captcha_settings',
      array(
        'id' => 'enable_captcha',
        'description' => 'Enable Google reCAPTCHA to prevent spam submissions'
      )
    );

    add_settings_field(
      'captcha_site_key',
      'reCAPTCHA Site Key',
      array($this, 'text_field_callback'),
      'form_post_settings',
      'form_post_captcha_settings',
      array(
        'id' => 'captcha_site_key',
        'description' => 'Your reCAPTCHA site key'
      )
    );

    add_settings_field(
      'captcha_secret_key',
      'reCAPTCHA Secret Key',
      array($this, 'text_field_callback'),
      'form_post_settings',
      'form_post_captcha_settings',
      array(
        'id' => 'captcha_secret_key',
        'description' => 'Your reCAPTCHA secret key'
      )
    );
  }

  /**
   * Sanitize settings
   *
   * @since    1.0.0
   * @param    array    $input    The input array to sanitize
   * @return   array    The sanitized input
   */
  public function sanitize_settings($input)
  {
    $sanitized = array();

    if (isset($input['admin_email'])) {
      $sanitized['admin_email'] = sanitize_email($input['admin_email']);
    }

    if (isset($input['email_subject_admin'])) {
      $sanitized['email_subject_admin'] = sanitize_text_field($input['email_subject_admin']);
    }

    if (isset($input['email_template_admin'])) {
      $sanitized['email_template_admin'] = sanitize_textarea_field($input['email_template_admin']);
    }

    if (isset($input['email_subject_user'])) {
      $sanitized['email_subject_user'] = sanitize_text_field($input['email_subject_user']);
    }

    if (isset($input['email_template_user'])) {
      $sanitized['email_template_user'] = sanitize_textarea_field($input['email_template_user']);
    }

    if (isset($input['enable_captcha'])) {
      $sanitized['enable_captcha'] = $input['enable_captcha'] ? '1' : '0';
    } else {
      $sanitized['enable_captcha'] = '0';
    }

    if (isset($input['captcha_site_key'])) {
      $sanitized['captcha_site_key'] = sanitize_text_field($input['captcha_site_key']);
    }

    if (isset($input['captcha_secret_key'])) {
      $sanitized['captcha_secret_key'] = sanitize_text_field($input['captcha_secret_key']);
    }

    // Update database settings
    foreach ($sanitized as $key => $value) {
      Form_Post_Database::update_setting($key, $value);
    }

    return $sanitized;
  }

  /**
   * Email settings section callback
   *
   * @since    1.0.0
   */
  public function email_settings_callback()
  {
    echo '<p>Configure email settings for registration notifications and confirmations.</p>';
  }

  /**
   * Captcha settings section callback
   *
   * @since    1.0.0
   */
  public function captcha_settings_callback()
  {
    echo '<p>Configure Google reCAPTCHA to prevent spam submissions. Get your keys from <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Google reCAPTCHA Admin Console</a>.</p>';
  }

  /**
   * Text field callback
   *
   * @since    1.0.0
   * @param    array    $args    The field arguments
   */
  public function text_field_callback($args)
  {
    $option = Form_Post_Database::get_setting($args['id']);
    $id = $args['id'];
    $description = $args['description'];

    echo "<input type='text' id='$id' name='form_post_settings[$id]' value='" . esc_attr($option) . "' class='regular-text'>";
    echo "<p class='description'>$description</p>";
  }

  /**
   * Textarea field callback
   *
   * @since    1.0.0
   * @param    array    $args    The field arguments
   */
  public function textarea_field_callback($args)
  {
    $option = Form_Post_Database::get_setting($args['id']);
    $id = $args['id'];
    $description = $args['description'];

    echo "<textarea id='$id' name='form_post_settings[$id]' rows='5' class='large-text'>" . esc_textarea($option) . "</textarea>";
    echo "<p class='description'>$description</p>";
  }

  /**
   * Checkbox field callback
   *
   * @since    1.0.0
   * @param    array    $args    The field arguments
   */
  public function checkbox_field_callback($args)
  {
    $option = Form_Post_Database::get_setting($args['id']);
    $id = $args['id'];
    $description = $args['description'];

    $checked = $option === '1' ? 'checked' : '';
    echo "<input type='checkbox' id='$id' name='form_post_settings[$id]' value='1' $checked>";
    echo "<label for='$id'>$description</label>";
  }

  /**
   * Get default settings
   *
   * @since    1.0.0
   * @return   array    The default settings
   */
  public function get_default_settings()
  {
    return array(
      'admin_email' => get_option('admin_email'),
      'email_subject_admin' => 'Pendaftaran Webinar Baru',
      'email_template_admin' => 'Ada pendaftaran webinar baru dari {nama_lengkap} ({email})',
      'email_subject_user' => 'Konfirmasi Pendaftaran Webinar',
      'email_template_user' => 'Terima kasih telah mendaftar webinar kami. Kami akan menghubungi Anda segera.',
      'enable_captcha' => '0',
      'captcha_site_key' => '',
      'captcha_secret_key' => ''
    );
  }
}
