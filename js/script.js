/**
 * AniCore - Combined JavaScript file
 * Contains all functionality: UI interactions, form validation, async operations
 */

// Toast notification system
const toastSystem = {
  container: null,

  init: function () {
    // Create toast container if it doesn't exist
    if (!document.querySelector(".toast-container")) {
      this.container = document.createElement("div");
      this.container.className = "toast-container";
      document.body.appendChild(this.container);
    } else {
      this.container = document.querySelector(".toast-container");
    }
  },

  show: function (message, type = "info", duration = 3000) {
    if (!this.container) this.init();

    // Create toast element
    const toast = document.createElement("div");
    toast.className = `toast show bg-${type} text-white`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    // Add toast content
    toast.innerHTML = `
      <div class="toast-header bg-${type} text-white">
        <strong class="me-auto">${
          type.charAt(0).toUpperCase() + type.slice(1)
        }</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        ${message}
      </div>
    `;

    // Add to container
    this.container.appendChild(toast);

    // Auto-remove after duration
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, duration);

    // Add click listener to close button
    const closeBtn = toast.querySelector(".btn-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
      });
    }
  },
};

// Initialize Bootstrap components and handle basic functionality
document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize all dropdowns with default Bootstrap behavior
  const dropdownElementList = [].slice.call(
    document.querySelectorAll(".dropdown-toggle")
  );
  dropdownElementList.map(function (dropdownToggleEl) {
    return new bootstrap.Dropdown(dropdownToggleEl);
  });

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll(".confirm-delete");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (
        !confirm(
          "Are you sure you want to remove this anime from your watchlist?"
        )
      ) {
        e.preventDefault();
      }
    });
  });
  // Let watchlist forms submit naturally without any JavaScript interference
  console.log(
    "Watchlist forms will submit using standard HTML form submission"
  );
});

// Dynamic episode counter
function updateEpisodeCount(input, max) {
  const counter = document.getElementById(input.dataset.counter);
  if (counter) {
    counter.textContent = `${input.value} / ${max}`;
  }
}

// Async watchlist updates with toast notifications
async function updateWatchlistAsync(formElement, event) {
  if (!formElement || !event) return;

  event.preventDefault();

  const formData = new FormData(formElement);
  const submitButton = formElement.querySelector('button[type="submit"]');
  const originalText = submitButton.innerHTML;

  try {
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

    // Send the request
    const response = await fetch(formElement.action, {
      method: formElement.method,
      body: formData,
    });

    if (!response.ok) {
      throw new Error("Network response was not ok");
    }

    const data = await response.json();

    // Show success message
    if (data.success) {
      toastSystem.show(data.message, "success");

      // Update UI if needed (e.g., status changes, counts, etc.)
      if (data.updateElement && data.updateContent) {
        const element = document.querySelector(data.updateElement);
        if (element) {
          element.innerHTML = data.updateContent;
        }
      }

      // Handle redirects if needed
      if (data.redirect) {
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 1000);
      }
    } else {
      toastSystem.show(data.message || "An error occurred", "danger");
    }
  } catch (error) {
    console.error("Error:", error);
    toastSystem.show(
      "An error occurred while processing your request",
      "danger"
    );
  } finally {
    // Restore button state
    submitButton.disabled = false;
    submitButton.innerHTML = originalText;
  }
}

// Enhanced searching functionality
function enhanceSearch() {
  const searchInput = document.getElementById("search-input");
  const searchResults = document.getElementById("search-results");
  const clearSearchBtn = document.getElementById("clear-search");

  if (!searchInput || !searchResults) return;

  let searchTimeout;

  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout);

    const query = this.value.trim();

    if (query.length < 3) {
      searchResults.innerHTML = "";
      return;
    }

    // Show loading state
    searchResults.innerHTML =
      '<div class="text-center py-3"><div class="loading-spinner"></div><p class="mt-2">Searching...</p></div>';

    // Debounce search requests
    searchTimeout = setTimeout(async function () {
      try {
        const response = await fetch(
          `search.php?ajax=1&q=${encodeURIComponent(query)}`
        );

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const data = await response.json();

        if (data.results && data.results.length > 0) {
          // Render results
          searchResults.innerHTML = data.results
            .map(
              (anime) => `
            <a href="anime_detail.php?id=${anime.id}" class="list-group-item list-group-item-action">
              <div class="d-flex align-items-center">
                <img src="images/posters/${anime.poster}" alt="${anime.title}" class="me-3" style="width: 50px; height: 70px; object-fit: cover;">
                <div>
                  <h6 class="mb-1">${anime.title}</h6>
                  <small class="text-muted">${anime.genres} • ${anime.year}</small>
                </div>
              </div>
            </a>
          `
            )
            .join("");
        } else {
          searchResults.innerHTML =
            '<div class="text-center py-3">No results found</div>';
        }
      } catch (error) {
        console.error("Error:", error);
        searchResults.innerHTML =
          '<div class="text-center py-3 text-danger">Error fetching results</div>';
      }
    }, 500);
  });

  if (clearSearchBtn) {
    clearSearchBtn.addEventListener("click", function () {
      searchInput.value = "";
      searchResults.innerHTML = "";
      searchInput.focus();
    });
  }
}

// Add event listeners and initialize enhanced features
document.addEventListener("DOMContentLoaded", function () {
  // Initialize toast notification system
  toastSystem.init();

  // Initialize enhanced search
  enhanceSearch();

  // Check for URL parameters to display messages
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("message")) {
    const message = urlParams.get("message");
    const isError = urlParams.has("error");
    toastSystem.show(message, isError ? "danger" : "success");
  }
  // Find all episode counter inputs
  const episodeInputs = document.querySelectorAll("input[data-counter]");
  episodeInputs.forEach((input) => {
    const counterId = input.dataset.counter;
    const counterElement = document.getElementById(counterId);
    const maxEpisodes = input.max || "?";

    // Update on page load
    if (counterElement) {
      counterElement.textContent = `${input.value} / ${maxEpisodes}`;
    }

    // Add input event listener
    input.addEventListener("input", function () {
      if (counterElement) {
        counterElement.textContent = `${this.value} / ${maxEpisodes}`;
      }
    });
  });
});

// Automatically hide success messages after 2-3 seconds, except for specific ones
document.addEventListener("DOMContentLoaded", function () {
  const successMessages = document.querySelectorAll(
    ".alert-success:not(#successAlert)"
  );
  successMessages.forEach((message) => {
    setTimeout(() => {
      message.style.display = "none";
    }, 3000); // 3 seconds
  });
});

/***************************
 * Form Validation System
 ***************************/

// Form validation configuration
const validators = {
  required: {
    validate: (value) => value.trim() !== "",
    message: "This field is required",
  },
  email: {
    validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
    message: "Please enter a valid email address",
  },
  minLength: (min) => ({
    validate: (value) => value.length >= min,
    message: `Must be at least ${min} characters long`,
  }),
  matches: (field, fieldName) => ({
    validate: (value, form) => value === form.querySelector(field).value,
    message: `Must match ${fieldName}`,
  }),
  fileType: (types) => ({
    validate: (value, form, input) => {
      if (!input.files || !input.files[0]) return true;
      const fileType = input.files[0].type;
      return types.includes(fileType);
    },
    message: `Only ${types.join(", ")} files are allowed`,
  }),
  fileSize: (maxSizeInBytes) => ({
    validate: (value, form, input) => {
      if (!input.files || !input.files[0]) return true;
      return input.files[0].size <= maxSizeInBytes;
    },
    message: `File size must not exceed ${
      Math.round((maxSizeInBytes / 1024 / 1024) * 100) / 100
    }MB`,
  }),
};

// Validate a single input
function validateInput(input, form) {
  // Skip validation if input is disabled or readonly
  if (input.disabled || input.readOnly) return true;

  const value = input.value;
  let isValid = true;
  let feedbackMessage = "";

  // Special handling for password field - show comprehensive validation
  if (input.type === "password" && input.id === "password") {
    const passwordErrors = [];

    if (value.length < 6) {
      passwordErrors.push("at least 6 characters long");
    }
    if (!/[A-Z]/.test(value)) {
      passwordErrors.push("at least one uppercase letter");
    }
    if (!/[a-z]/.test(value)) {
      passwordErrors.push("at least one lowercase letter");
    }
    if (!/[0-9]/.test(value)) {
      passwordErrors.push("at least one number");
    }
    if (!/[!@#$%^&*]/.test(value)) {
      passwordErrors.push("at least one special character (!@#$%^&*)");
    }

    if (passwordErrors.length > 0 && value.length > 0) {
      isValid = false;
      feedbackMessage = "Password must include: " + passwordErrors.join(", ");
    } else if (value.length === 0) {
      isValid = false;
      feedbackMessage = "This field is required";
    } else {
      isValid = true;
      feedbackMessage = ""; // Clear the message when password is valid
    }
  }
  // Handle other password fields (confirm password)
  else if (input.type === "password" && input.dataset.matches) {
    const targetField = input.dataset.matches;
    const targetName = input.dataset.matchesName || "the other field";
    const validator = validators.matches(targetField, targetName);
    if (value && !validator.validate(value, form)) {
      isValid = false;
      feedbackMessage = validator.message;
    } else if (value && validator.validate(value, form)) {
      isValid = true;
      feedbackMessage = ""; // Clear message when passwords match
    } else if (!value) {
      isValid = false;
      feedbackMessage = "This field is required";
    }
  }
  // Handle all other fields
  else {
    // Get validation rules from data attributes
    if (input.hasAttribute("required") || input.dataset.required === "true") {
      if (!validators.required.validate(value)) {
        isValid = false;
        feedbackMessage = validators.required.message;
      }
    }
    if (input.dataset.type === "email" && isValid && value) {
      if (!validators.email.validate(value)) {
        isValid = false;
        feedbackMessage = validators.email.message;
      }
    }

    if (input.dataset.minlength && isValid && value) {
      const min = parseInt(input.dataset.minlength);
      const validator = validators.minLength(min);
      if (!validator.validate(value)) {
        isValid = false;
        feedbackMessage = validator.message;
      }
    }

    if (input.dataset.matches && isValid && value) {
      const targetField = input.dataset.matches;
      const targetName = input.dataset.matchesName || "the other field";
      const validator = validators.matches(targetField, targetName);
      if (!validator.validate(value, form)) {
        isValid = false;
        feedbackMessage = validator.message;
      }
    }

    if (input.type === "file" && input.dataset.fileTypes && isValid) {
      const allowedTypes = input.dataset.fileTypes
        .split(",")
        .map((t) => t.trim());
      const validator = validators.fileType(allowedTypes);
      if (!validator.validate(value, form, input)) {
        isValid = false;
        feedbackMessage = validator.message;
      }
    }
    if (input.type === "file" && input.dataset.maxFileSize && isValid) {
      const maxSize = parseInt(input.dataset.maxFileSize) * 1024 * 1024; // Convert from MB to bytes
      const validator = validators.fileSize(maxSize);
      if (!validator.validate(value, form, input)) {
        isValid = false;
        feedbackMessage = validator.message;
      }
    }
  } // End of the else block for all other fields

  // Update input validation state
  input.classList.toggle("is-invalid", !isValid);
  input.classList.toggle("is-valid", isValid && value !== "");
  // Update feedback message
  const feedback = input.parentNode.querySelector(".invalid-feedback");
  if (feedback) {
    feedback.textContent = feedbackMessage;
    // Bootstrap automatically shows/hides invalid-feedback based on is-invalid class
    // But we can ensure it's visible when there's an error
    if (!isValid && feedbackMessage) {
      feedback.style.display = "block";
      feedback.style.color = "#dc3545"; // Bootstrap's danger color
    } else {
      feedback.style.display = "none";
      feedback.textContent = ""; // Clear the content when valid
    }
  }

  return isValid;
}

// Initialize form validation
function initFormValidation() {
  const forms = document.querySelectorAll(".needs-validation");

  Array.from(forms).forEach((form) => {
    // Apply validation rules based on data attributes
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      // Only create feedback element if it doesn't exist
      let feedback = input.nextElementSibling;
      if (!feedback || !feedback.classList.contains("invalid-feedback")) {
        // Check if there's already an invalid-feedback element in the parent
        const existingFeedback =
          input.parentNode.querySelector(".invalid-feedback");
        if (!existingFeedback) {
          feedback = document.createElement("div");
          feedback.classList.add("invalid-feedback");
          input.parentNode.insertBefore(feedback, input.nextElementSibling);
        } else {
          feedback = existingFeedback;
        }
      }

      // Add input event listener for real-time validation
      input.addEventListener("input", () => validateInput(input, form));

      // Initial validation state
      if (input.value) {
        validateInput(input, form);
      }
    });

    form.addEventListener("submit", function (event) {
      let isValid = true;

      // Validate all inputs before submission
      inputs.forEach((input) => {
        if (!validateInput(input, form)) {
          isValid = false;
        }
      });

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();

        // Focus the first invalid input
        const firstInvalid = form.querySelector(".is-invalid");
        if (firstInvalid) {
          firstInvalid.focus();
        }
      }

      form.classList.add("was-validated");
    });
  });
}

// Initialize validation when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", initFormValidation);
