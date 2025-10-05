<?php

/**
 * Frontend class for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Frontend
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
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      $this->plugin_name,
      plugin_dir_url(__FILE__) . '../assets/css/frontend.css',
      array(),
      $this->version,
      'all'
    );
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {
    // Enqueue Alpine.js
    wp_enqueue_script(
      'alpine-js',
      'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
      array(),
      '3.0.0',
      true
    );

    // Enqueue plugin frontend script
    wp_enqueue_script(
      $this->plugin_name . '-frontend',
      plugin_dir_url(__FILE__) . '../assets/js/frontend.js',
      array('jquery'),
      $this->version,
      true
    );

    // Localize script for AJAX URL and nonce
    wp_localize_script(
      $this->plugin_name . '-frontend',
      'formPostAjax',
      array(
        'api_url' => rest_url('form-post/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'strings' => array(
          'submitting' => 'Submitting...',
          'success' => 'Registration successful! We will contact you soon.',
          'error' => 'An error occurred. Please try again.',
          'validation_error' => 'Please fill in all required fields correctly.',
          'email_error' => 'Please enter a valid email address.',
          'phone_error' => 'Please enter a valid phone number.'
        )
      )
    );
  }

  /**
   * Register shortcodes
   *
   * @since    1.0.0
   */
  public function register_shortcodes()
  {
    add_shortcode('webinar_registration_form', array($this, 'render_registration_form'));
  }

  /**
   * Render the registration form
   *
   * @since    1.0.0
   * @param    array    $atts    Shortcode attributes
   * @return   string   The form HTML
   */
  public function render_registration_form($atts)
  {
    // Default attributes
    $atts = shortcode_atts(array(
      'title' => 'Webinar Registration',
      'description' => 'Please fill in the form below to register for our webinar.',
      'show_captcha' => 'true'
    ), $atts, 'webinar_registration_form');

    // Get settings
    $enable_captcha = Form_Post_Database::get_setting('enable_captcha', '0');
    $captcha_site_key = Form_Post_Database::get_setting('captcha_site_key', '');

    // Start output buffering
    ob_start();
?>
    <div class="webinar-registration-form" x-data="webinarRegistrationForm()">
      <div class="form-header">
        <h2><?php echo esc_html($atts['title']); ?></h2>
        <p><?php echo esc_html($atts['description']); ?></p>
      </div>

      <div x-show="message" x-transition class="form-message" :class="messageType">
        <span x-text="message"></span>
      </div>

      <form @submit.prevent="submitForm" x-bind:disabled="isSubmitting">
        <div class="form-row">
          <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap *</label>
            <input
              type="text"
              id="nama_lengkap"
              name="nama_lengkap"
              x-model="formData.nama_lengkap"
              required
              :class="{'error': errors.nama_lengkap}">
            <span x-show="errors.nama_lengkap" class="error-message" x-text="errors.nama_lengkap"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Email *</label>
            <input
              type="email"
              id="email"
              name="email"
              x-model="formData.email"
              required
              :class="{'error': errors.email}">
            <span x-show="errors.email" class="error-message" x-text="errors.email"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="nomor_telepon">Nomor Telepon *</label>
            <input
              type="tel"
              id="nomor_telepon"
              name="nomor_telepon"
              x-model="formData.nomor_telepon"
              required
              :class="{'error': errors.nomor_telepon}">
            <span x-show="errors.nomor_telepon" class="error-message" x-text="errors.nomor_telepon"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="instansi">Instansi/Perusahaan</label>
            <input
              type="text"
              id="instansi"
              name="instansi"
              x-model="formData.instansi">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="jabatan">Jabatan</label>
            <input
              type="text"
              id="jabatan"
              name="jabatan"
              x-model="formData.jabatan">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="alamat">Alamat</label>
            <textarea
              id="alamat"
              name="alamat"
              rows="3"
              x-model="formData.alamat"></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea
              id="keterangan"
              name="keterangan"
              rows="3"
              x-model="formData.keterangan"></textarea>
          </div>
        </div>

        <?php if ($enable_captcha === '1' && !empty($captcha_site_key)): ?>
          <div class="form-row">
            <div class="form-group">
              <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($captcha_site_key); ?>"></div>
              <span x-show="errors.captcha" class="error-message" x-text="errors.captcha"></span>
            </div>
          </div>
        <?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              <span x-show="!isSubmitting">Register Now</span>
              <span x-show="isSubmitting">Submitting...</span>
            </button>
          </div>
        </div>
      </form>
    </div>
<?php
    return ob_get_clean();
  }

  /**
   * Validate form data
   *
   * @since    1.0.0
   * @param    array    $data    The form data to validate
   * @return   array    Validation errors
   */
  private function validate_form_data($data)
  {
    $errors = array();

    // Validate required fields
    if (empty($data['nama_lengkap'])) {
      $errors['nama_lengkap'] = 'Nama lengkap is required';
    }

    if (empty($data['email'])) {
      $errors['email'] = 'Email is required';
    } elseif (!is_email($data['email'])) {
      $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($data['nomor_telepon'])) {
      $errors['nomor_telepon'] = 'Nomor telepon is required';
    }

    // Check if email already exists
    if (empty($errors['email'])) {
      $existing = Form_Post_Database::get_registrations(array(
        'email' => $data['email']
      ));

      if (!empty($existing)) {
        $errors['email'] = 'This email is already registered';
      }
    }

    return $errors;
  }
}
