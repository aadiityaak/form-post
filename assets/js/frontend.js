/**
 * Frontend JavaScript for Form Post Plugin
 * Handles form submission with Alpine.js and REST API
 */

// Alpine.js component for webinar registration form
function webinarRegistrationForm() {
  return {
    formData: {
      nama_lengkap: "",
      email: "",
      nomor_telepon: "",
      instansi: "",
      jabatan: "",
      alamat: "",
      keterangan: "",
    },
    errors: {},
    isSubmitting: false,
    message: "",
    messageType: "",

    // Initialize form
    init() {
      // Add event listeners for real-time validation
      this.$watch("formData.nama_lengkap", () =>
        this.validateField("nama_lengkap")
      );
      this.$watch("formData.email", () => this.validateField("email"));
      this.$watch("formData.nomor_telepon", () =>
        this.validateField("nomor_telepon")
      );
    },

    // Validate individual field
    validateField(field) {
      this.errors[field] = "";

      switch (field) {
        case "nama_lengkap":
          if (
            !this.formData.nama_lengkap ||
            this.formData.nama_lengkap.length < 3
          ) {
            this.errors[field] = formPostAjax.strings.validation_error;
          }
          break;
        case "email":
          if (!this.formData.email) {
            this.errors[field] = formPostAjax.strings.validation_error;
          } else if (!this.isValidEmail(this.formData.email)) {
            this.errors[field] = formPostAjax.strings.email_error;
          }
          break;
        case "nomor_telepon":
          if (!this.formData.nomor_telepon) {
            this.errors[field] = formPostAjax.strings.validation_error;
          } else if (!this.isValidPhone(this.formData.nomor_telepon)) {
            this.errors[field] = formPostAjax.strings.phone_error;
          }
          break;
      }
    },

    // Validate entire form
    validateForm() {
      this.errors = {};

      // Validate required fields
      if (
        !this.formData.nama_lengkap ||
        this.formData.nama_lengkap.length < 3
      ) {
        this.errors.nama_lengkap = formPostAjax.strings.validation_error;
      }

      if (!this.formData.email) {
        this.errors.email = formPostAjax.strings.validation_error;
      } else if (!this.isValidEmail(this.formData.email)) {
        this.errors.email = formPostAjax.strings.email_error;
      }

      if (!this.formData.nomor_telepon) {
        this.errors.nomor_telepon = formPostAjax.strings.validation_error;
      } else if (!this.isValidPhone(this.formData.nomor_telepon)) {
        this.errors.nomor_telepon = formPostAjax.strings.phone_error;
      }

      return Object.keys(this.errors).length === 0;
    },

    // Check if email is valid
    isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    },

    // Check if phone is valid
    isValidPhone(phone) {
      const phoneRegex = /^[+]?[0-9\s\-()]+$/;
      return phoneRegex.test(phone) && phone.replace(/\D/g, "").length >= 10;
    },

    // Submit form
    async submitForm() {
      if (!this.validateForm()) {
        this.showMessage(formPostAjax.strings.validation_error, "error");
        return;
      }

      this.isSubmitting = true;
      this.message = "";
      this.messageType = "";

      try {
        // Get reCAPTCHA response if enabled
        const captchaResponse = document.querySelector(".g-recaptcha-response")
          ? document.querySelector(".g-recaptcha-response").value
          : null;

        // Prepare form data
        const submissionData = {
          ...this.formData,
          ip_address: this.getClientIP(),
          user_agent: navigator.userAgent,
          "g-recaptcha-response": captchaResponse,
        };

        // Submit to REST API
        const response = await fetch(`${formPostAjax.api_url}/submit`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": formPostAjax.nonce,
          },
          body: JSON.stringify(submissionData),
        });

        const result = await response.json();

        if (result.success) {
          this.showMessage(result.message, "success");
          this.resetForm();

          // Add success animation
          this.$el.classList.add("success-animation");
          setTimeout(() => {
            this.$el.classList.remove("success-animation");
          }, 600);
        } else {
          // Handle specific error messages
          let errorMessage = formPostAjax.strings.error;

          if (result.code === "email_exists") {
            errorMessage = "This email is already registered";
          } else if (result.message) {
            errorMessage = result.message;
          }

          this.showMessage(errorMessage, "error");

          // Add error animation
          this.$el.classList.add("error-animation");
          setTimeout(() => {
            this.$el.classList.remove("error-animation");
          }, 500);
        }
      } catch (error) {
        console.error("Form submission error:", error);
        this.showMessage(formPostAjax.strings.error, "error");
      } finally {
        this.isSubmitting = false;
      }
    },

    // Reset form
    resetForm() {
      this.formData = {
        nama_lengkap: "",
        email: "",
        nomor_telepon: "",
        instansi: "",
        jabatan: "",
        alamat: "",
        keterangan: "",
      };
      this.errors = {};
    },

    // Show message
    showMessage(message, type) {
      this.message = message;
      this.messageType = type;

      // Auto-hide success messages after 5 seconds
      if (type === "success") {
        setTimeout(() => {
          this.message = "";
          this.messageType = "";
        }, 5000);
      }
    },

    // Get client IP (fallback method)
    getClientIP() {
      // This is a fallback method
      // In a real implementation, you might want to use a service to get the actual IP
      return "0.0.0.0";
    },
  };
}

// Register the Alpine.js component
document.addEventListener("alpine:init", () => {
  Alpine.data("webinarRegistrationForm", webinarRegistrationForm);
});

// Utility functions
document.addEventListener("DOMContentLoaded", function () {
  // Add reCAPTCHA script if needed
  const captchaElements = document.querySelectorAll(".g-recaptcha");
  if (captchaElements.length > 0) {
    const script = document.createElement("script");
    script.src = "https://www.google.com/recaptcha/api.js";
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
  }

  // Add smooth scrolling for form validation errors
  const formContainer = document.querySelector(".webinar-registration-form");
  if (formContainer) {
    const form = formContainer.querySelector("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        // This is handled by Alpine.js, but we keep this as a fallback
        if (!form.checkValidity()) {
          e.preventDefault();

          // Find first error field and scroll to it
          const firstError = form.querySelector(".error-message");
          if (firstError) {
            firstError.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
          }
        }
      });
    }
  }
});

// Helper function to format phone number as user types
function formatPhoneNumber(input) {
  // Remove all non-numeric characters
  let value = input.value.replace(/\D/g, "");

  // Format based on length
  if (value.length > 0 && value.length <= 3) {
    value = value;
  } else if (value.length > 3 && value.length <= 7) {
    value = value.slice(0, 3) + "-" + value.slice(3);
  } else if (value.length > 7) {
    value =
      value.slice(0, 3) + "-" + value.slice(3, 7) + "-" + value.slice(7, 11);
  }

  input.value = value;
}

// Helper function to add formatting to form fields
document.addEventListener("DOMContentLoaded", function () {
  const phoneInput = document.querySelector('input[name="nomor_telepon"]');
  if (phoneInput) {
    phoneInput.addEventListener("input", function () {
      formatPhoneNumber(this);
    });
  }
});

// Form analytics tracking (optional)
function trackFormSubmission(formData, result) {
  // This is where you could add analytics tracking
  // For example, Google Analytics events
  if (typeof gtag !== "undefined") {
    gtag("event", "form_submission", {
      event_category: "Webinar Registration",
      event_label: result.success ? "Success" : "Error",
    });
  }
}

// Export functions for potential use by other scripts
window.WebinarRegistrationForm = {
  formatPhoneNumber,
  trackFormSubmission,
};
