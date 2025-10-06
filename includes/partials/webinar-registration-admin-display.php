<?php

/**
 * Admin dashboard display for Form Post plugin
 *
 * @since      1.0.0
 * @package    Form_Post
 * @subpackage Form_Post/admin/partials
 */
?>

<div class="wrap webinar-registration-admin">
  <div class="form-header">
    <h1>Webinar Registration Dashboard</h1>
    <p>Manage webinar registrations and settings</p>
  </div>

  <div class="form-content">
    <?php
    // Get statistics
    $stats = array(
      'total' => Form_Post_Database::count_registrations(),
      'pending' => Form_Post_Database::count_registrations('pending'),
      'approved' => Form_Post_Database::count_registrations('diterima'),
      'rejected' => Form_Post_Database::count_registrations('ditolak')
    );
    ?>

    <div class="statistics-dashboard">
      <div class="stat-card total">
        <div class="stat-number"><?php echo esc_html($stats['total']); ?></div>
        <div class="stat-label">Total Registrations</div>
      </div>

      <div class="stat-card pending">
        <div class="stat-number"><?php echo esc_html($stats['pending']); ?></div>
        <div class="stat-label">Pending</div>
      </div>

      <div class="stat-card approved">
        <div class="stat-number"><?php echo esc_html($stats['approved']); ?></div>
        <div class="stat-label">Approved</div>
      </div>

      <div class="stat-card rejected">
        <div class="stat-number"><?php echo esc_html($stats['rejected']); ?></div>
        <div class="stat-label">Rejected</div>
      </div>
    </div>

    <div class="dashboard-actions">
      <h2>Quick Actions</h2>
      <div class="action-buttons">
        <a href="<?php echo admin_url('admin.php?page=webinar-registrations'); ?>" class="btn btn-primary">
          View All Registrations
        </a>
        <a href="<?php echo admin_url('admin.php?page=webinar-settings'); ?>" class="btn btn-secondary">
          Plugin Settings
        </a>
        <a href="<?php echo admin_url('admin.php?page=webinar-registrations&export=csv&_wpnonce=' . wp_create_nonce('export_registrations')); ?>" class="btn btn-secondary">
          Export to CSV
        </a>
      </div>
    </div>

    <div class="recent-registrations">
      <h2>Recent Registrations</h2>
      <?php
      $recent_registrations = Form_Post_Database::get_registrations(array(
        'limit' => 5,
        'orderby' => 'created_at',
        'order' => 'DESC'
      ));

      if (!empty($recent_registrations)) :
      ?>
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_registrations as $registration) : ?>
              <tr>
                <td><?php echo esc_html($registration->nama_lengkap); ?></td>
                <td><?php echo esc_html($registration->email); ?></td>
                <td>
                  <span class="status-badge status-<?php echo $registration->status === 'pending' ? 'pending' : ($registration->status === 'diterima' ? 'approved' : 'rejected'); ?>">
                    <?php echo esc_html(ucfirst($registration->status)); ?>
                  </span>
                </td>
                <td><?php echo esc_html(date('M j, Y', strtotime($registration->created_at))); ?></td>
                <td>
                  <a href="<?php echo admin_url('admin.php?page=webinar-registrations&action=view&id=' . $registration->id); ?>" class="view-registration" data-id="<?php echo $registration->id; ?>">
                    View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else : ?>
        <p>No registrations yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php wp_nonce_field('webinar_nonce', 'webinar_nonce', true, true); ?>