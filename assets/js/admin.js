/**
 * Admin JavaScript for Form Post Plugin
 * Handles admin interface interactions
 */

jQuery(document).ready(function ($) {
  // Initialize admin functionality
  initAdmin();

  function initAdmin() {
    // Registration management
    initRegistrationManagement();

    // Bulk actions
    initBulkActions();

    // Export functionality
    initExportFunctionality();

    // Status updates
    initStatusUpdates();

    // Statistics dashboard
    initStatisticsDashboard();

    // Settings form
    initSettingsForm();
  }

  /**
   * Registration management functionality
   */
  function initRegistrationManagement() {
    // View registration details
    $(".view-registration").on("click", function (e) {
      e.preventDefault();

      const registrationId = $(this).data("id");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "get_registration_details",
          registration_id: registrationId,
          nonce: $("#webinar_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            showRegistrationDetails(response.data);
          } else {
            showNotice("Error loading registration details", "error");
          }
        },
        error: function () {
          showNotice("Error loading registration details", "error");
        },
      });
    });

    // Delete registration
    $(".delete-registration").on("click", function (e) {
      e.preventDefault();

      if (!confirm("Are you sure you want to delete this registration?")) {
        return;
      }

      const registrationId = $(this).data("id");
      const row = $(this).closest("tr");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "delete_registration",
          registration_id: registrationId,
          nonce: $("#webinar_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            row.fadeOut(400, function () {
              $(this).remove();
            });
            showNotice("Registration deleted successfully", "success");
          } else {
            showNotice("Error deleting registration", "error");
          }
        },
        error: function () {
          showNotice("Error deleting registration", "error");
        },
      });
    });
  }

  /**
   * Bulk actions functionality
   */
  function initBulkActions() {
    // Handle bulk action form submission
    $("#doaction, #doaction2").on("click", function (e) {
      const action = $(this).prev("select").val();

      if (action === "-1") {
        return;
      }

      if (action === "delete") {
        if (
          !confirm(
            "Are you sure you want to delete the selected registrations?"
          )
        ) {
          e.preventDefault();
          return;
        }
      }
    });

    // Select all checkbox
    $("#cb-select-all-1").on("change", function () {
      const isChecked = $(this).prop("checked");
      $('input[name="registration_ids[]"]').prop("checked", isChecked);
    });

    // Update select all checkbox when individual checkboxes change
    $('input[name="registration_ids[]"]').on("change", function () {
      const allChecked =
        $('input[name="registration_ids[]"]:not(:checked)').length === 0;
      $("#cb-select-all-1").prop("checked", allChecked);
    });
  }

  /**
   * Export functionality
   */
  function initExportFunctionality() {
    $(".export-csv").on("click", function (e) {
      e.preventDefault();

      const status = $(this).data("status");
      const nonce = $(this).data("nonce");

      // Build export URL
      let exportUrl = `${ajaxurl}?action=export_registrations&status=${status}&_wpnonce=${nonce}`;

      // Create and trigger download
      const link = document.createElement("a");
      link.href = exportUrl;
      link.download = `webinar-registrations-${status}-${new Date()
        .toISOString()
        .slice(0, 10)}.csv`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  }

  /**
   * Status update functionality
   */
  function initStatusUpdates() {
    $(".status-update").on("change", function () {
      const registrationId = $(this).data("id");
      const newStatus = $(this).val();
      const row = $(this).closest("tr");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "update_registration_status",
          registration_id: registrationId,
          status: newStatus,
          nonce: $("#webinar_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            // Update status badge
            const statusBadge = row.find(".status-badge");
            statusBadge
              .removeClass("status-pending status-approved status-rejected")
              .addClass(
                `status-${
                  newStatus === "pending"
                    ? "pending"
                    : newStatus === "diterima"
                    ? "approved"
                    : "rejected"
                }`
              )
              .text(newStatus);

            showNotice("Status updated successfully", "success");
          } else {
            showNotice("Error updating status", "error");
          }
        },
        error: function () {
          showNotice("Error updating status", "error");
        },
      });
    });
  }

  /**
   * Statistics dashboard functionality
   */
  function initStatisticsDashboard() {
    // Auto-refresh statistics every 30 seconds
    setInterval(function () {
      refreshStatistics();
    }, 30000);

    // Initial statistics load
    refreshStatistics();
  }

  /**
   * Refresh statistics
   */
  function refreshStatistics() {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "get_statistics",
        nonce: $("#webinar_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          updateStatisticsDisplay(response.data);
        }
      },
    });
  }

  /**
   * Update statistics display
   */
  function updateStatisticsDisplay(data) {
    $(".stat-total .stat-number").text(data.total);
    $(".stat-pending .stat-number").text(data.pending);
    $(".stat-approved .stat-number").text(data.approved);
    $(".stat-rejected .stat-number").text(data.rejected);

    // Add animation effect
    $(".stat-card").addClass("pulse");
    setTimeout(function () {
      $(".stat-card").removeClass("pulse");
    }, 1000);
  }

  /**
   * Settings form functionality
   */
  function initSettingsForm() {
    // Test email functionality
    $("#test-email").on("click", function (e) {
      e.preventDefault();

      const testEmail = $("#admin_email").val();

      if (!testEmail) {
        showNotice("Please enter an email address first", "error");
        return;
      }

      $(this).prop("disabled", true).text("Sending...");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "test_email",
          email: testEmail,
          nonce: $("#webinar_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            showNotice("Test email sent successfully", "success");
          } else {
            showNotice("Error sending test email", "error");
          }
        },
        error: function () {
          showNotice("Error sending test email", "error");
        },
        complete: function () {
          $("#test-email").prop("disabled", false).text("Send Test Email");
        },
      });
    });

    // Validate reCAPTCHA keys
    $("#validate-captcha").on("click", function (e) {
      e.preventDefault();

      const siteKey = $("#captcha_site_key").val();
      const secretKey = $("#captcha_secret_key").val();

      if (!siteKey || !secretKey) {
        showNotice("Please enter both site key and secret key", "error");
        return;
      }

      $(this).prop("disabled", true).text("Validating...");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "validate_captcha",
          site_key: siteKey,
          secret_key: secretKey,
          nonce: $("#webinar_nonce").val(),
        },
        success: function (response) {
          if (response.success) {
            showNotice("reCAPTCHA keys are valid", "success");
          } else {
            showNotice("Invalid reCAPTCHA keys", "error");
          }
        },
        error: function () {
          showNotice("Error validating reCAPTCHA keys", "error");
        },
        complete: function () {
          $("#validate-captcha").prop("disabled", false).text("Validate Keys");
        },
      });
    });
  }

  /**
   * Show registration details in modal
   */
  function showRegistrationDetails(data) {
    // Create modal HTML
    const modalHtml = `
            <div class="webinar-modal-overlay">
                <div class="webinar-modal">
                    <div class="modal-header">
                        <h3>Registration Details</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-content">
                        <div class="detail-row">
                            <div class="detail-label">Name:</div>
                            <div class="detail-value">${data.nama_lengkap}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">${data.email}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone:</div>
                            <div class="detail-value">${
                              data.nomor_telepon
                            }</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Institution:</div>
                            <div class="detail-value">${
                              data.instansi || "-"
                            }</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Position:</div>
                            <div class="detail-value">${
                              data.jabatan || "-"
                            }</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Address:</div>
                            <div class="detail-value">${
                              data.alamat || "-"
                            }</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Notes:</div>
                            <div class="detail-value">${
                              data.keterangan || "-"
                            }</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">
                                <select class="status-update" data-id="${
                                  data.id
                                }">
                                    <option value="pending" ${
                                      data.status === "pending"
                                        ? "selected"
                                        : ""
                                    }>Pending</option>
                                    <option value="diterima" ${
                                      data.status === "diterima"
                                        ? "selected"
                                        : ""
                                    }>Approved</option>
                                    <option value="ditolak" ${
                                      data.status === "ditolak"
                                        ? "selected"
                                        : ""
                                    }>Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Registration Date:</div>
                            <div class="detail-value">${data.created_at}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="button modal-close">Close</button>
                    </div>
                </div>
            </div>
        `;

    // Add modal to page
    $("body").append(modalHtml);

    // Handle modal close
    $(".modal-close, .webinar-modal-overlay").on("click", function (e) {
      if (e.target === this) {
        $(".webinar-modal-overlay").remove();
      }
    });

    // Prevent modal close when clicking inside modal
    $(".webinar-modal").on("click", function (e) {
      e.stopPropagation();
    });
  }

  /**
   * Show admin notice
   */
  function showNotice(message, type) {
    const noticeHtml = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
            </div>
        `;

    // Add notice to top of page
    $(".wrap h1").after(noticeHtml);

    // Auto-hide after 5 seconds
    setTimeout(function () {
      $(".notice").fadeOut(400, function () {
        $(this).remove();
      });
    }, 5000);

    // Handle dismiss button
    $(document).on("click", ".notice-dismiss", function () {
      $(this)
        .closest(".notice")
        .fadeOut(400, function () {
          $(this).remove();
        });
    });
  }
});

// Add modal styles dynamically
$("<style>")
  .prop("type", "text/css")
  .html(
    `
        .webinar-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .webinar-modal {
            background: #fff;
            border-radius: 4px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            flex-shrink: 0;
        }
        
        .detail-value {
            flex-grow: 1;
        }
        
        .pulse {
            animation: pulse 1s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    `
  )
  .appendTo("head");
