
<?php

/**
 * REST API class for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/includes
 * @author     Websweetstudio.com - Aditya Kristyanto <aditya@websweetstudio.com>
 */
class Form_Post_Rest_API {

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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register REST API routes
     *
     * @since    1.0.0
     */
    public function register_routes() {
        $namespace = 'form-post/v1';

        // Register submission endpoint
        register_rest_route($namespace, '/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_form_submission'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'nama_lengkap' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_name')
                ),
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email',
                    'validate_callback' => array($this, 'validate_email')
                ),
                'nomor_telepon' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_phone')
                ),
                'instansi' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'jabatan' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'alamat' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'keterangan' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_textarea_field'
                )
            )
        ));

        // Get registrations endpoint (admin only)
        register_rest_route($namespace, '/registrations', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_registrations'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'status' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'limit' => array(
                    'required' => false,
                    'sanitize_callback' => 'absint',
                    'default' => 50
                ),
                'offset' => array(
                    'required' => false,
                    'sanitize_callback' => 'absint',
                    'default' => 0
                )
            )
        ));

        // Update registration status endpoint (admin only)
        register_rest_route($namespace, '/registrations/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_registration'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'status' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'validate_status')
                )
            )
        ));

        // Delete registration endpoint (admin only)
        register_rest_route($namespace, '/registrations/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_registration'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        ));

        // Get statistics endpoint (admin only)
        register_rest_route($namespace, '/statistics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_statistics'),
            'permission_callback' => array($this, 'check_admin_permissions')
        ));
    }

    /**
     * Check permissions for form submission
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   bool|WP_Error      True if permission is granted, WP_Error otherwise
     */
    public function check_permissions($request) {
        // Check nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', 'Security check failed', array('status' => 401));
        }

        // Additional captcha check if enabled
        $enable_captcha = Form_Post_Database::get_setting('enable_captcha', '0');
        if ($enable_captcha === '1') {
            $captcha_response = $request->get_param('g-recaptcha-response');
            if (!$this->verify_captcha($captcha_response)) {
                return new WP_Error('rest_forbidden', 'Captcha verification failed', array('status' => 401));
            }
        }

        return true;
    }

    /**
     * Check admin permissions
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   bool|WP_Error      True if permission is granted, WP_Error otherwise
     */
    public function check_admin_permissions($request) {
        // Check nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', 'Security check failed', array('status' => 401));
        }

        // Check if user has admin capabilities
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', 'You do not have permissions to access this endpoint', array('status' => 403));
        }

        return true;
    }

    /**
     * Handle form submission
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   WP_REST_Response   The response object
     */
    public function handle_form_submission($request) {
        // Get form data
        $data = array(
            'nama_lengkap' => $request->get_param('nama_lengkap'),
            'email' => $request->get_param('email'),
            'nomor_telepon' => $request->get_param('nomor_telepon'),
            'instansi' => $request->get_param('instansi'),
            'jabatan' => $request->get_param('jabatan'),
            'alamat' => $request->get_param('alamat'),
            'keterangan' => $request->get_param('keterangan'),
            'status' => 'pending',
            'ip_address' => $request->get_param('ip_address') ?: $_SERVER['REMOTE_ADDR'],
            'user_agent' => $request->get_param('user_agent') ?: $_SERVER['HTTP_USER_AGENT']
        );

        // Check if email already exists
        $existing = Form_Post_Database::get_registrations(array('email' => $data['email']));
        if (!empty($existing)) {
            return new WP_Error(
                'email_exists',
                'This email is already registered',
                array('status' => 400)
            );
        }

        // Insert registration
        $registration_id = Form_Post_Database::insert_registration($data);

        if ($registration_id) {
            // Send notifications
            $this->send_notifications($registration_id, $data);

            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => 'Registration successful! We will contact you soon.',
                    'registration_id' => $registration_id
                ),
                200
            );
        } else {
            return new WP_Error(
                'registration_failed',
                'Failed to register. Please try again.',
                array('status' => 500)
            );
        }
    }

    /**
     * Get registrations
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   WP_REST_Response   The response object
     */
    public function get_registrations($request) {
        $args = array(
            'status' => $request->get_param('status'),
            'limit' => $request->get_param('limit'),
            'offset' => $request->get_param('offset')
        );

        $registrations = Form_Post_Database::get_registrations($args);
        $total = Form_Post_Database::count_registrations($args['status']);

        return new WP_REST_Response(
            array(
                'success' => true,
                'data' => $registrations,
                'total' => $total,
                'pagination' => array(
                    'limit' => $args['limit'],
                    'offset' => $args['offset'],
                    'total_pages' => ceil($total / $args['limit'])
                )
            ),
            200
        );
    }

    /**
     * Update registration
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   WP_REST_Response   The response object
     */
    public function update_registration($request) {
        $id = $request->get_param('id');
        $status = $request->get_param('status');

        $updated = Form_Post_Database::update_registration($id, array('status' => $status));

        if ($updated) {
            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => 'Registration updated successfully'
                ),
                200
            );
        } else {
            return new WP_Error(
                'update_failed',
                'Failed to update registration',
                array('status' => 500)
            );
        }
    }

    /**
     * Delete registration
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   WP_REST_Response   The response object
     */
    public function delete_registration($request) {
        $id = $request->get_param('id');

        $deleted = Form_Post_Database::delete_registration($id);

        if ($deleted) {
            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => 'Registration deleted successfully'
                ),
                200
            );
        } else {
            return new WP_Error(
                'delete_failed',
                'Failed to delete registration',
                array('status' => 500)
            );
        }
    }

    /**
     * Get statistics
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object
     * @return   WP_REST_Response   The response object
     */
    public function get_statistics($request) {
        $stats = array(
            'total' => Form_Post_Database::count_registrations(),
            'pending' => Form_Post_Database::count_registrations('pending'),
            'approved' => Form_Post_Database::count_registrations('diterima'),
            'rejected' => Form_Post_Database::count_registrations('ditolak')
        );

        return new WP_REST_Response(
            array(
                'success' => true,
                'data' => $stats
            ),
            200
        );
    }

    /**
     * Send email notifications
     *
     * @since    1.0.0
     * @param    int      $registration_id    The registration ID
     * @param    array    $data               The registration data
     */
    private function send_notifications($registration_id, $data) {
        // Get settings
        $admin_email = Form_Post_Database::get_setting('admin_email', get_option('admin_email'));
        $email_subject_admin = Form_Post_Database::get_setting('email_subject_admin', 'Pendaftaran Webinar Baru');
        $email_template_admin = Form_Post_Database::get_setting('email_template_admin', 'Ada pendaftaran webinar baru dari {nama_lengkap} ({email})');
        $email_subject_user = Form_Post_Database::get_setting('email_subject_user', 'Konfirmasi Pendaftaran Webinar');
        $email_template_user = Form_Post_Database::get_setting('email_template_user', 'Terima kasih telah mendaftar webinar kami. Kami akan menghubungi Anda segera.');

        // Send email to admin
        $admin_message = str_replace(
            array('{nama_lengkap}', '{email}', '{nomor_telepon}', '{instansi}', '{jabatan}'),
            array($data['nama_lengkap'], $data['email'], $data['nomor_telepon'], $data['instansi'], $data['jabatan']),
            $email_template_admin
        );

        wp_mail($admin_email, $email_subject_admin, $admin_message);

        // Send email to user
        $user_message = str_replace(
            array('{nama_lengkap}', '{email}'),
            array($data['nama_lengkap'], $data['email']),
            $email_template_user
        );

        wp_mail($data['email'], $email_subject_user, $user_message);
    }

    /**
     * Verify captcha
     *
     * @since    1.0.0
     * @param    string    $response    The captcha response
     * @return   bool      True if captcha is valid, false otherwise
     */
    private function verify_captcha($response) {
        $secret_key = Form_Post_Database::get_setting('captcha_secret_key', '');
        
        if (empty($secret_key) || empty($response)) {
            return false;
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $secret_key,
                'response' => $response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

