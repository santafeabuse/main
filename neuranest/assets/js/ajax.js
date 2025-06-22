/**
 * NeuraNest AJAX Functions
 * Handles all AJAX requests without page reloads
 */

// Global AJAX settings
$.ajaxSetup({
    timeout: 30000,
    error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        showNotification('Ошибка сети. Попробуйте еще раз.', 'error');
    }
});

// Notification system
function showNotification(message, type = 'info', duration = 5000) {
    // Remove existing notifications
    $('.notification').remove();
    
    const notification = $(`
        <div class="notification notification-${type}">
            <div class="notification-content">
                <span class="notification-icon">${getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="$(this).parent().parent().fadeOut()">&times;</button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    notification.fadeIn();
    
    if (duration > 0) {
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, duration);
    }
}

function getNotificationIcon(type) {
    const icons = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    return icons[type] || icons['info'];
}

// Loading overlay
function showLoading(message = 'Загрузка...') {
    $('.loading-overlay').remove();
    
    const overlay = $(`
        <div class="loading-overlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-message">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(overlay);
    overlay.fadeIn();
}

function hideLoading() {
    $('.loading-overlay').fadeOut(() => $('.loading-overlay').remove());
}

// Profile functions
function updateDisplayName(formData) {
    showLoading('Обновление имени...');
    
    $.ajax({
        url: 'ajax/update_profile.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Имя успешно обновлено!', 'success');
                // Update display name in UI
                $('.user-name').text(response.display_name || 'User');
            } else {
                showNotification(response.message || 'Ошибка обновления', 'error');
            }
        }
    });
}

function uploadAvatar(formData) {
    showLoading('Загрузка аватара...');
    
    $.ajax({
        url: 'ajax/upload_avatar.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Аватар успешно обновлен!', 'success');
                // Update avatar in UI
                $('.avatar img').attr('src', '../assets/images/avatars/' + response.filename);
                // If no img tag exists, replace with img
                if ($('.avatar img').length === 0) {
                    $('.avatar-container').html(`<img src="../assets/images/avatars/${response.filename}" alt="Avatar" class="avatar">`);
                }
            } else {
                showNotification(response.message || 'Ошибка загрузки', 'error');
            }
        }
    });
}

// Password change functions
function sendPasswordVerificationCode(formData) {
    showLoading('Отправка кода...');
    
    $.ajax({
        url: 'ajax/send_password_code.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Код отправлен на ваш email!', 'success');
                // Show verification step
                $('#step-request').hide();
                $('#step-verify').show();
                updateStepIndicator(2);
            } else {
                showNotification(response.message || 'Ошибка отправки', 'error');
            }
        }
    });
}

function changePassword(formData) {
    showLoading('Изменение пароля...');
    
    $.ajax({
        url: 'ajax/change_password.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Пароль успешно изменен!', 'success');
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 2000);
            } else {
                showNotification(response.message || 'Ошибка изменения пароля', 'error');
            }
        }
    });
}

// Email change functions
function sendEmailVerificationCodes(formData) {
    showLoading('Отправка кодов...');
    
    $.ajax({
        url: 'ajax/send_email_codes.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Коды отправлены на оба email!', 'success');
                // Show verification step
                $('#step-request').hide();
                $('#step-verify').show();
                updateStepIndicator(2);
                // Update email display
                $('#new-email-display').text(response.new_email);
            } else {
                showNotification(response.message || 'Ошибка отправки', 'error');
            }
        }
    });
}

function changeEmail(formData) {
    showLoading('Изменение email...');
    
    $.ajax({
        url: 'ajax/change_email.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Email успешно изменен!', 'success');
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 2000);
            } else {
                showNotification(response.message || 'Ошибка изменения email', 'error');
            }
        }
    });
}

// Premium upgrade functions
function processPayment(formData) {
    showLoading('Обработка платежа...');
    
    $.ajax({
        url: 'ajax/process_payment.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Платеж успешно обработан! Добро пожаловать в Premium!', 'success');
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 3000);
            } else {
                showNotification(response.message || 'Ошибка обработки платежа', 'error');
            }
        }
    });
}

// Registration functions
function registerUser(formData) {
    showLoading('Создание аккаунта...');
    
    $.ajax({
        url: 'ajax/register.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Код подтверждения отправлен на ваш email!', 'success');
                setTimeout(() => {
                    window.location.href = 'verify.php?type=registration';
                }, 2000);
            } else {
                showNotification(response.message || 'Ошибка регистрации', 'error');
            }
        }
    });
}

function verifyAccount(formData) {
    showLoading('Подтверждение аккаунта...');
    
    $.ajax({
        url: 'ajax/verify.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Аккаунт успешно подтвержден!', 'success');
                setTimeout(() => {
                    window.location.href = '../chat/chat.php';
                }, 2000);
            } else {
                showNotification(response.message || 'Ошибка подтверждения', 'error');
            }
        }
    });
}

function resendVerificationCode() {
    showLoading('Отправка кода...');
    
    $.ajax({
        url: 'ajax/resend_code.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Код отправлен повторно!', 'success');
                startResendCooldown();
            } else {
                showNotification(response.message || 'Ошибка отправки', 'error');
            }
        }
    });
}

// Login function
function loginUser(formData) {
    showLoading('Вход в систему...');
    
    $.ajax({
        url: 'ajax/login.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Успешный вход!', 'success');
                setTimeout(() => {
                    window.location.href = response.redirect || '../chat/chat.php';
                }, 1000);
            } else {
                showNotification(response.message || 'Ошибка входа', 'error');
            }
        }
    });
}

// Utility functions
function updateStepIndicator(activeStep) {
    $('.step').removeClass('active').addClass('inactive');
    $(`.step:nth-child(${activeStep})`).removeClass('inactive').addClass('active');
}

function startResendCooldown() {
    let countdown = 60;
    const resendBtn = $('#resendBtn');
    const countdownElement = $('#countdown');
    
    resendBtn.prop('disabled', true);
    
    const timer = setInterval(() => {
        countdown--;
        countdownElement.text(countdown);
        
        if (countdown <= 0) {
            clearInterval(timer);
            resendBtn.prop('disabled', false);
            $('#resendContainer').show();
            $('#countdownContainer').hide();
        }
    }, 1000);
    
    $('#resendContainer').hide();
    $('#countdownContainer').show();
}

// Auto-format verification codes
function setupCodeInputs() {
    $('.code-input').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 6) {
            value = value.slice(0, 6);
        }
        $(this).val(value);
        
        // Auto-submit when 6 digits are entered
        if (value.length === 6 && $(this).hasClass('auto-submit')) {
            $(this).closest('form').submit();
        }
    });
    
    $('.code-input').on('keypress', function(e) {
        if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
            e.preventDefault();
        }
    });
}

// Initialize AJAX functionality
$(document).ready(function() {
    setupCodeInputs();
    
    // Add notification styles if not present
    if (!$('#ajax-styles').length) {
        $('head').append(`
            <style id="ajax-styles">
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    min-width: 300px;
                    max-width: 500px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    display: none;
                }
                
                .notification-content {
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                
                .notification-success {
                    background: #d1fae5;
                    border: 1px solid #a7f3d0;
                    color: #065f46;
                }
                
                .notification-error {
                    background: #fee2e2;
                    border: 1px solid #fecaca;
                    color: #991b1b;
                }
                
                .notification-warning {
                    background: #fef3c7;
                    border: 1px solid #fde68a;
                    color: #92400e;
                }
                
                .notification-info {
                    background: #dbeafe;
                    border: 1px solid #93c5fd;
                    color: #1e40af;
                }
                
                .notification-message {
                    flex: 1;
                    font-weight: 500;
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    opacity: 0.7;
                    padding: 0;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .notification-close:hover {
                    opacity: 1;
                }
                
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                    display: none;
                }
                
                .loading-content {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                }
                
                .loading-spinner {
                    width: 40px;
                    height: 40px;
                    border: 4px solid #f3f4f6;
                    border-top: 4px solid #667eea;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 15px;
                }
                
                .loading-message {
                    font-weight: 500;
                    color: #374151;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                @media (max-width: 768px) {
                    .notification {
                        top: 10px;
                        right: 10px;
                        left: 10px;
                        min-width: auto;
                    }
                }
            </style>
        `);
    }
});
