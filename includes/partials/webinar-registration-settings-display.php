<?php

/**
 * Settings display for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/admin/partials
 */

// Handle form submission
if (isset($_POST['submit'])) {
  // Verify nonce
  if (!isset($_POST['form_post_settings_nonce']) || !wp_verify_nonce($_POST['form_post_settings_nonce'], 'form_post_settings')) {
    wp_die('Security check failed');
  }

  // Save settings
  $settings = array(
    'admin_email' => sanitize_email($_POST['admin_email']),
    'email_subject_admin' => sanitize_text_field($_POST['email_subject_admin']),
    'email_template_admin' => sanitize_textarea_field($_POST['email_template_admin']),
    'email_subject_user' => sanitize_text_field($_POST['email_subject_user']),
    'email_template_user' => sanitize_textarea_field($_POST['email_template_user']),
    'enable_captcha' => isset($_POST['enable_captcha']) ? '1' : '0',
    'captcha_site_key' => sanitize_text_field($_POST['captcha_site_key']),
    'captcha_secret_key' => sanitize_text_field($_POST['captcha_secret_key'])
  );

  foreach ($settings as $key => $value) {
    Form_Post_Database::update_setting($key, $value);
  }

  // Redirect to avoid form resubmission
  wp_redirect(admin_url('admin.php?page=webinar-settings&settings-updated=true'));
  exit;
}

// Get current settings
$current_settings = array(
  'admin_email' => Form_Post_Database::get_setting('admin_email', get_option('admin_email')),
  'email_subject_admin' => Form_Post_Database::get_setting('email_subject_admin', 'Pendaftaran Webinar Baru'),
  'email_template_admin' => Form_Post_Database::get_setting('email_template_admin', 'Ada pendaftaran webinar baru dari {nama_lengkap} ({email})'),
  'email_subject_user' => Form_Post_Database::get_setting('email_subject_user', 'Konfirmasi Pendaftaran Webinar'),
  'email_template_user' => Form_Post_Database::get_setting('email_template_user', 'Terima kasih telah mendaftar webinar kami. Kami akan menghubungi Anda segera.'),
  'enable_captcha' => Form_Post_Database::get_setting('enable_captcha', '0'),
  'captcha_site_key' => Form_Post_Database::get_setting('captcha_site_key', ''),
  'captcha_secret_key' => Form_Post_Database::get_setting('captcha_secret_key', '')
);
?>

<div class="wrap webinar-registration-admin">
  <div class="form-header">
    <h1>Webinar Registration Settings</h1>
    <p>Configure plugin settings and email templates</p>
  </div>

  <div class="form-content">
    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') : ?>
      <div class="notice notice-success is-dismissible">
        <p>Settings updated successfully.</p>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="form-post-settings">
      <?php wp_nonce_field('form_post_settings', 'form_post_settings_nonce'); ?>

      <div class="postbox">
        <h2 class="hndle">Email Settings</h2>
        <div class="inside">
          <table class="form-table">
            <tr>
              <th scope="row">
                <label for="admin_email">Admin Email</label>
              </th>
              <td>
                <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($current_settings['admin_email']); ?>" class="regular-text">
                <p class="description">Email address to receive registration notifications</p>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="email_subject_admin">Admin Email Subject</label>
              </th>
              <td>
                <input type="text" id="email_subject_admin" name="email_subject_admin" value="<?php echo esc_attr($current_settings['email_subject_admin']); ?>" class="regular-text">
                <p class="description">Subject line for admin notification emails</p>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="email_template_admin">Admin Email Template</label>
              </th>
              <td>
                <textarea id="email_template_admin" name="email_template_admin" rows="6" class="large-text"><?php echo esc_textarea($current_settings['email_template_admin']); ?></textarea>
                <p class="description">Email template for admin notifications. Available tags: {nama_lengkap}, {email}, {nomor_telepon}, {instansi}, {jabatan}</p>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="email_subject_user">User Email Subject</label>
              </th>
              <td>
                <input type="text" id="email_subject_user" name="email_subject_user" value="<?php echo esc_attr($current_settings['email_subject_user']); ?>" class="regular-text">
                <p class="description">Subject line for user confirmation emails</p>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="email_template_user">User Email Template</label>
              </th>
              <td>
                <textarea id="email_template_user" name="email_template_user" rows="6" class="large-text"><?php echo esc_textarea($current_settings['email_template_user']); ?></textarea>
                <p class="description">Email template for user confirmations. Available tags: {nama_lengkap}, {email}</p>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle">Captcha Settings</h2>
        <div class="inside">
          <table class="form-table">
            <tr>
              <th scope="row">
                <label for="enable_captcha">Enable Captcha</label>
              </th>
              <td>
                <input type="checkbox" id="enable_captcha" name="enable_captcha" value="1" <?php checked($current_settings['enable_captcha'], '1'); ?>>
                <label for="enable_captcha">Enable Google reCAPTCHA to prevent spam submissions</label>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="captcha_site_key">reCAPTCHA Site Key</label>
              </th>
              <td>
                <input type="text" id="captcha_site_key" name="captcha_site_key" value="<?php echo esc_attr($current_settings['captcha_site_key']); ?>" class="regular-text">
                <p class="description">Your reCAPTCHA site key. Get keys from <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Google reCAPTCHA Admin Console</a></p>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="captcha_secret_key">reCAPTCHA Secret Key</label>
              </th>
              <td>
                <input type="text" id="captcha_secret_key" name="captcha_secret_key" value="<?php echo esc_attr($current_settings['captcha_secret_key']); ?>" class="regular-text">
                <p class="description">Your reCAPTCHA secret key</p>
              </td>
            </tr>
          </table>

          <p>
            <button type="button" id="validate-captcha" class="button button-secondary">Validate Keys</button>
            <span id="captcha-validation-result"></span>
          </p>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle">Test Email</h2>
        <div class="inside">
          <p>Send a test email to verify your email settings are working correctly.</p>
          <p>
            <button type="button" id="test-email" class="button button-secondary">Send Test Email</button>
            <span id="test-email-result"></span>
          </p>
        </div>
      </div>

      <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
      </p>
    </form>

    <div class="postbox">
      <h2 class="hndle">Shortcode Usage</h2>
      <div class="inside">
        <p>Use the following shortcode to display the registration form on any page or post:</p>
        <code>[webinar_registration_form]</code>

        <h4>Optional Parameters:</h4>
        <ul>
          <li><code>title</code> - Custom title for the form (default: "Webinar Registration")</li>
          <li><code>description</code> - Custom description for the form (default: "Please fill in the form below to register for our webinar.")</li>
          <li><code>show_captcha</code> - Show/hide captcha (default: "true")</li>
        </ul>

        <h4>Examples:</h4>
        <p>Basic usage: <code>[webinar_registration_form]</code></p>
        <p>With custom title: <code>[webinar_registration_form title="Register for Our Webinar"]</code></p>
        <p>With custom description: <code>[webinar_registration_form description="Join us for an informative webinar session."]</code></p>
        <p>Hide captcha: <code>[webinar_registration_form show_captcha="false"]</code></p>
      </div>
    </div>
  </div>
</div>

<?php wp_nonce_field('webinar_nonce', 'webinar_nonce', true, true); ?>