// Form validation functions

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;
        
        this.fields = {};
        this.errors = {};
        this.setupValidation();
    }
    
    setupValidation() {
        // Find all input fields with validation requirements
        const inputs = this.form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            // Store reference to field
            this.fields[input.id] = input;
            
            // Add event listeners
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
        
        // Add form submit event listener
        this.form.addEventListener('submit', (e) => this.validateForm(e));
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldId = field.id;
        let isValid = true;
        let message = '';
        
        // Clear previous error
        this.clearFieldError(field);
        
        // Check required fields
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            const cleaned = value.replace(/[\s\-\(\)]/g, '');
            if (!phoneRegex.test(cleaned)) {
                isValid = false;
                message = 'Please enter a valid phone number';
            }
        }
        
        // Date validation
        if (field.type === 'date' && value) {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                isValid = false;
                message = 'Please select a future date';
            }
        }
        
        // Password strength (if needed)
        if (field.type === 'password' && value) {
            if (value.length < 8) {
                isValid = false;
                message = 'Password must be at least 8 characters';
            }
        }
        
        if (!isValid) {
            this.showFieldError(field, message);
            this.errors[fieldId] = message;
        } else {
            delete this.errors[fieldId];
        }
        
        return isValid;
    }
    
    showFieldError(field, message) {
        // Remove any existing error message
        this.clearFieldError(field);
        
        // Add error class to field
        field.classList.add('is-invalid');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        // Insert after the field
        field.parentNode.appendChild(errorDiv);
        
        // If there's a label, add error class to it too
        const label = field.parentNode.querySelector('label');
        if (label) {
            label.classList.add('text-danger');
        }
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        // Remove error message
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
        
        // Remove label error class
        const label = field.parentNode.querySelector('label');
        if (label) {
            label.classList.remove('text-danger');
        }
        
        // Remove from errors object
        delete this.errors[field.id];
    }
    
    validateForm(e) {
        // Prevent default submission if there are errors
        e.preventDefault();
        
        // Clear all errors first
        Object.keys(this.fields).forEach(fieldId => {
            this.clearFieldError(this.fields[fieldId]);
        });
        
        // Validate all fields
        let isValid = true;
        Object.keys(this.fields).forEach(fieldId => {
            const field = this.fields[fieldId];
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Special validation for password confirmation
        const password = this.fields['password'];
        const confirmPassword = this.fields['confirm_password'];
        
        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                this.showFieldError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }
        }
        
        // If form is valid, you can submit it
        if (isValid) {
            // In a real application, you would submit the form
            // For now, we'll just show a success message
            this.showFormSuccess();
            return true;
        }
        
        return false;
    }
    
    showFormSuccess() {
        // Remove any existing success message
        const existingAlert = this.form.querySelector('.alert-success');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create success alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            Form submitted successfully!
        `;
        
        // Insert after form
        this.form.parentNode.insertBefore(alertDiv, this.form.nextSibling);
        
        // Clear form after successful submission
        setTimeout(() => {
            this.form.reset();
            alertDiv.remove();
        }, 3000);
    }
}

// Initialize validators for all forms
document.addEventListener('DOMContentLoaded', function() {
    // Initialize contact form validator
    if (document.getElementById('contactForm')) {
        new FormValidator('contactForm');
    }
    
    // Initialize reservation form validator
    if (document.getElementById('reservationForm')) {
        new FormValidator('reservationForm');
    }
    
    // Initialize registration form validator
    if (document.getElementById('registerForm')) {
        new FormValidator('registerForm');
    }
    
    // Initialize login form validator
    if (document.getElementById('loginForm')) {
        new FormValidator('loginForm');
    }
});

// Additional validation helper functions
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    const cleaned = phone.replace(/[\s\-\(\)]/g, '');
    return re.test(cleaned);
}

function validateRequired(value) {
    return value && value.trim().length > 0;
}

// Export functions if using modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        FormValidator,
        validateEmail,
        validatePhone,
        validateRequired
    };
}