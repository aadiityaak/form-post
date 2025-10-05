<?php

/**
 * Registrations display for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/admin/partials
 */

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] === 'bulk_action') {
  $registration_ids = isset($_POST['registration_ids']) ? $_POST['registration_ids'] : array();
  $bulk_action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';

  if (!empty($registration_ids) && !empty($bulk_action)) {
    foreach ($registration_ids as $id) {
      if ($bulk_action === 'approve') {
        Form_Post_Database::update_registration($id, array('status' => 'diterima'));
      } elseif ($bulk_action === 'reject') {
        Form_Post_Database::update_registration($id, array('status' => 'ditolak'));
      } elseif ($bulk_action === 'delete') {
        Form_Post_Database::delete_registration($id);
      }
    }

    // Redirect to avoid form resubmission
    wp_redirect(admin_url('admin.php?page=webinar-registrations&status=' . $bulk_action));
    exit;
  }
}

// Get current status filter
$current_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Get pagination parameters
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$limit = 20;
$offset = ($paged - 1) * $limit;

// Get registrations
$registrations = Form_Post_Database::get_registrations(array(
  'status' => $current_status,
  'limit' => $limit,
  'offset' => $offset,
  'orderby' => 'created_at',
  'order' => 'DESC'
));

// Get total count for pagination
$total = Form_Post_Database::count_registrations($current_status);
$total_pages = ceil($total / $limit);
?>

<div class="wrap webinar-registration-admin">
  <div class="form-header">
    <h1>Webinar Registrations</h1>
    <p>Manage and review webinar registration submissions</p>
  </div>

  <div class="form-content">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
      <div class="stat-summary">
        <span class="stat-label">Total:</span>
        <span class="stat-value"><?php echo esc_html(Form_Post_Database::count_registrations()); ?></span>
      </div>
      <div class="stat-summary">
        <span class="stat-label">Pending:</span>
        <span class="stat-value"><?php echo esc_html(Form_Post_Database::count_registrations('pending')); ?></span>
      </div>
      <div class="stat-summary">
        <span class="stat-label">Approved:</span>
        <span class="stat-value"><?php echo esc_html(Form_Post_Database::count_registrations('diterima')); ?></span>
      </div>
      <div class="stat-summary">
        <span class="stat-label">Rejected:</span>
        <span class="stat-value"><?php echo esc_html(Form_Post_Database::count_registrations('ditolak')); ?></span>
      </div>
    </div>

    <!-- Filter and Export -->
    <div class="table-nav">
      <div class="filter-form">
        <form method="get" action="">
          <input type="hidden" name="page" value="webinar-registrations">
          <select name="filter_status">
            <option value="">All Status</option>
            <option value="pending" <?php selected($current_status, 'pending'); ?>>Pending</option>
            <option value="diterima" <?php selected($current_status, 'diterima'); ?>>Approved</option>
            <option value="ditolak" <?php selected($current_status, 'ditolak'); ?>>Rejected</option>
          </select>
          <input type="submit" value="Filter" class="button">
        </form>
      </div>

      <div class="bulk-actions">
        <a href="<?php echo admin_url('admin.php?page=webinar-registrations&export=csv&_wpnonce=' . wp_create_nonce('export_registrations') . '&filter_status=' . $current_status); ?>" class="btn btn-secondary export-csv" data-status="<?php echo esc_attr($current_status); ?>" data-nonce="<?php echo wp_create_nonce('export_registrations'); ?>">
          Export to CSV
        </a>
      </div>
    </div>

    <!-- Registrations Table -->
    <div class="webinar-registrations-table">
      <form method="post" action="">
        <div class="table-wrapper">
          <table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th class="check-column">
                  <input type="checkbox" id="cb-select-all-1">
                </th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Institution</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($registrations)) : ?>
                <?php foreach ($registrations as $registration) : ?>
                  <tr>
                    <th class="check-column">
                      <input type="checkbox" name="registration_ids[]" value="<?php echo $registration->id; ?>">
                    </th>
                    <td>
                      <strong><?php echo esc_html($registration->nama_lengkap); ?></strong>
                      <div class="row-actions">
                        <span class="view">
                          <a href="#" class="view-registration" data-id="<?php echo $registration->id; ?>">View</a> |
                        </span>
                        <span class="edit">
                          <a href="#" class="edit-registration" data-id="<?php echo $registration->id; ?>">Edit</a> |
                        </span>
                        <span class="delete">
                          <a href="#" class="delete-registration" data-id="<?php echo $registration->id; ?>">Delete</a>
                        </span>
                      </div>
                    </td>
                    <td><?php echo esc_html($registration->email); ?></td>
                    <td><?php echo esc_html($registration->nomor_telepon); ?></td>
                    <td><?php echo esc_html($registration->instanzi ?: '-'); ?></td>
                    <td>
                      <select class="status-update" data-id="<?php echo $registration->id; ?>">
                        <option value="pending" <?php selected($registration->status, 'pending'); ?>>Pending</option>
                        <option value="diterima" <?php selected($registration->status, 'diterima'); ?>>Approved</option>
                        <option value="ditolak" <?php selected($registration->status, 'ditolak'); ?>>Rejected</option>
                      </select>
                    </td>
                    <td><?php echo esc_html(date('M j, Y H:i', strtotime($registration->created_at))); ?></td>
                    <td>
                      <div class="action-buttons">
                        <button type="button" class="button button-small view-registration" data-id="<?php echo $registration->id; ?>">
                          View
                        </button>
                        <button type="button" class="button button-small delete-registration" data-id="<?php echo $registration->id; ?>">
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="8">No registrations found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Bulk Actions -->
        <div class="tablenav bottom">
          <div class="alignleft actions bulkactions">
            <select name="bulk_action">
              <option value="-1">Bulk Actions</option>
              <option value="approve">Approve</option>
              <option value="reject">Reject</option>
              <option value="delete">Delete</option>
            </select>
            <input type="submit" name="action" value="Apply" class="button action">
          </div>

          <!-- Pagination -->
          <div class="tablenav-pages">
            <span class="displaying-num">
              <?php
              $start = ($paged - 1) * $limit + 1;
              $end = min($paged * $limit, $total);
              echo esc_html("$start-$end of $total");
              ?>
            </span>

            <?php if ($total_pages > 1) : ?>
              <span class="pagination-links">
                <?php
                $current_url = admin_url('admin.php?page=webinar-registrations');
                if (!empty($current_status)) {
                  $current_url .= '&filter_status=' . $current_status;
                }

                // First page
                if ($paged > 1) {
                  echo '<a class="first-page" href="' . esc_url($current_url) . '">&laquo;</a>';
                } else {
                  echo '<span class="tablenav-pages-navspan">&laquo;</span>';
                }

                // Previous page
                if ($paged > 1) {
                  $prev_url = add_query_arg('paged', $paged - 1, $current_url);
                  echo '<a class="prev-page" href="' . esc_url($prev_url) . '">&lsaquo;</a>';
                } else {
                  echo '<span class="tablenav-pages-navspan">&lsaquo;</span>';
                }

                // Current page
                echo '<span class="paging-input">' . $paged . ' of ' . $total_pages . '</span>';

                // Next page
                if ($paged < $total_pages) {
                  $next_url = add_query_arg('paged', $paged + 1, $current_url);
                  echo '<a class="next-page" href="' . esc_url($next_url) . '">&rsaquo;</a>';
                } else {
                  echo '<span class="tablenav-pages-navspan">&rsaquo;</span>';
                }

                // Last page
                if ($paged < $total_pages) {
                  $last_url = add_query_arg('paged', $total_pages, $current_url);
                  echo '<a class="last-page" href="' . esc_url($last_url) . '">&raquo;</a>';
                } else {
                  echo '<span class="tablenav-pages-navspan">&raquo;</span>';
                }
                ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php wp_nonce_field('webinar_nonce', 'webinar_nonce', true, true); ?>