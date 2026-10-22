/**
 * Frontend Validation for Secure Registration Form (Practical 9)
 * - Real-time duplicate email & student number checks via AJAX
 * - Password strength meter styled with project green theme
 * - Submits to register.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Status banner from URL query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    const alertBox = document.getElementById('statusAlert');
    
    if (alertBox && status) {
        alertBox.style.display = 'block';
        if (status === 'success') {
            alertBox.className = 'alert alert-success';
            alertBox.innerHTML = '<strong>Success!</strong> Registration completed successfully. You can now <a href="login.html" style="color: var(--primary-green); font-weight: bold; text-decoration: underline; margin-left: 5px;">Login here</a>.';
        } else if (status === 'error') {
            alertBox.className = 'alert alert-error';
            alertBox.innerHTML = '<strong>Registration Failed:</strong> ' + (message ? decodeURIComponent(message) : 'Please check your inputs and try again.');
        }
    }
    
    if (!form) return;
    
    // Validation patterns
    const patterns = {
        student_number: /^STU\d{3,6}$/i,
        first_name: /^[A-Za-z]{2,30}$/,
        last_name: /^[A-Za-z]{2,30}$/,
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        phone: /^[0-9]{10}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
    };
    
    // Field validation function
    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        const errorElement = document.getElementById(`${fieldName}_error`);
        
        field.classList.remove('error', 'success');
        if (errorElement) {
            errorElement.textContent = '';
        }
        
        if (!value && !field.required) {
            return true;
        }
        
        if (!value && field.required) {
            showError(field, errorElement, 'This field is required');
            return false;
        }
        
        switch (fieldName) {
            case 'student_number':
                if (!patterns.student_number.test(value)) {
                    showError(field, errorElement, 'Student number must be in format STU001, STU002, etc.');
                    return false;
                }
                break;
                
            case 'first_name':
                if (!patterns.first_name.test(value)) {
                    showError(field, errorElement, 'First name must contain only letters (2-30 characters)');
                    return false;
                }
                break;
                
            case 'last_name':
                if (!patterns.last_name.test(value)) {
                    showError(field, errorElement, 'Last name must contain only letters (2-30 characters)');
                    return false;
                }
                break;
                
            case 'email':
                if (!patterns.email.test(value)) {
                    showError(field, errorElement, 'Please enter a valid email address');
                    return false;
                }
                break;
                
            case 'phone':
                if (value && !patterns.phone.test(value)) {
                    showError(field, errorElement, 'Phone number must be exactly 10 digits');
                    return false;
                }
                break;
                
            case 'date_of_birth':
                const dob = new Date(value);
                const today = new Date();
                const minAge = 16;
                const maxAge = 100;
                
                if (dob > today) {
                    showError(field, errorElement, 'Date of birth cannot be in the future');
                    return false;
                }
                
                const age = today.getFullYear() - dob.getFullYear();
                if (age < minAge || age > maxAge) {
                    showError(field, errorElement, `Age must be between ${minAge} and ${maxAge} years`);
                    return false;
                }
                break;
                
            case 'gender':
                if (!value) {
                    showError(field, errorElement, 'Please select your gender');
                    return false;
                }
                break;
                
            case 'password':
                if (!patterns.password.test(value)) {
                    showError(field, errorElement, 'Password must be at least 8 characters with uppercase, lowercase, number, and special character');
                    return false;
                }
                break;
                
            case 'confirm_password':
                const password = document.getElementById('password').value;
                if (value !== password) {
                    showError(field, errorElement, 'Passwords do not match');
                    return false;
                }
                break;
                
            case 'terms':
                const termsCheckbox = document.getElementById('terms');
                if (!termsCheckbox.checked) {
                    showError(field, errorElement, 'You must accept the terms and conditions');
                    return false;
                }
                break;
        }
        
        field.classList.add('success');
        return true;
    }
    
    function showError(field, errorElement, message) {
        field.classList.add('error');
        field.classList.remove('success');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.color = '#d32f2f';
        }
    }
    
    // Real-time validation on input/blur
    const fields = form.querySelectorAll('input, select');
    fields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('error')) {
                validateField(field);
            }
        });
    });
    
    // Password Strength Meter
    const passwordField = document.getElementById('password');
    const strengthBar = document.getElementById('strength_bar');
    const strengthText = document.getElementById('strength_text');
    
    if (passwordField && strengthBar && strengthText) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'strength-bar';
            strengthText.className = '';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Password Strength';
            } else if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.classList.add('weak');
                strengthText.textContent = 'Weak';
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
                strengthText.classList.add('medium');
                strengthText.textContent = 'Medium';
            } else {
                strengthBar.classList.add('strong');
                strengthText.classList.add('strong');
                strengthText.textContent = 'Strong';
            }
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const allFields = form.querySelectorAll('input, select');
        
        allFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        if (isValid) {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Registering...';
            }
        } else {
            e.preventDefault();
            const firstError = form.querySelector('.error');
            if (firstError) {
                firstError.focus();
            }
        }
    });
    
    // AJAX Email Availability Check
    const emailField = document.getElementById('email');
    let emailCheckTimeout;
    
    if (emailField) {
        emailField.addEventListener('input', function() {
            const email = this.value.trim();
            clearTimeout(emailCheckTimeout);
            
            if (patterns.email.test(email)) {
                emailCheckTimeout = setTimeout(() => {
                    checkEmailAvailability(email);
                }, 400);
            }
        });
    }
    
    function checkEmailAvailability(email) {
        const errorElement = document.getElementById('email_error');
        fetch('check_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                showError(emailField, errorElement, 'This email is already registered. Please login or use another email.');
            } else if (!emailField.classList.contains('error')) {
                emailField.classList.add('success');
                if (errorElement) {
                    errorElement.textContent = 'Email is available';
                    errorElement.style.color = 'var(--primary-green)';
                }
            }
        })
        .catch(err => console.error('Error checking email:', err));
    }
    
    // AJAX Student Number Availability Check
    const studentNumberField = document.getElementById('student_number');
    let studentNumberCheckTimeout;
    
    if (studentNumberField) {
        studentNumberField.addEventListener('input', function() {
            const studentNumber = this.value.trim();
            clearTimeout(studentNumberCheckTimeout);
            
            if (patterns.student_number.test(studentNumber)) {
                studentNumberCheckTimeout = setTimeout(() => {
                    checkStudentNumberAvailability(studentNumber);
                }, 400);
            }
        });
    }
    
    function checkStudentNumberAvailability(studentNumber) {
        const errorElement = document.getElementById('student_number_error');
        fetch('check_student_number.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'student_number=' + encodeURIComponent(studentNumber)
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                showError(studentNumberField, errorElement, 'This student number is already registered');
            } else if (!studentNumberField.classList.contains('error')) {
                studentNumberField.classList.add('success');
                if (errorElement) {
                    errorElement.textContent = 'Student number is available';
                    errorElement.style.color = 'var(--primary-green)';
                }
            }
        })
        .catch(err => console.error('Error checking student number:', err));
    }
});
