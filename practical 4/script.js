// Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity

// ==================== THEME SWITCHER (Dark/Light) ====================
function initThemeSwitcher() {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    }
    updateThemeIcon(savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const newTheme = body.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
}

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.textContent = theme === 'light' ? '🌙' : '☀️';
    }
}

// ==================== HAMBURGER MENU ====================
function initHamburgerMenu() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        const links = navMenu.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });
    }
}

// ==================== COLLAPSIBLE FAQ ====================
function initCollapsibleFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const summary = item.querySelector('summary');
        const answer = item.querySelector('.faq-answer');
        
        if (summary && answer) {
            summary.addEventListener('click', (e) => {
                e.preventDefault();
                const isOpen = item.classList.contains('active');
                
                // Close all other FAQ items
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    otherItem.removeAttribute('open');
                });
                
                // Toggle current item
                if (!isOpen) {
                    item.classList.add('active');
                    item.setAttribute('open', '');
                }
            });
        }
    });
}

// ==================== MODAL POPUP ====================
function showModal(message, title = 'Notification') {
    // Create modal if it doesn't exist
    let modal = document.getElementById('custom-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'custom-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <h3 id="modal-title"></h3>
                <p id="modal-message"></p>
                <button class="btn btn-primary modal-ok">OK</button>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            #custom-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s, visibility 0.3s;
            }
            #custom-modal.active {
                opacity: 1;
                visibility: visible;
            }
            #custom-modal .modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
            }
            #custom-modal .modal-content {
                position: relative;
                background-color: #183302;
                border: 2px solid #2e7d32;
                border-radius: 12px;
                padding: 2rem;
                max-width: 400px;
                width: 90%;
                z-index: 10000;
                text-align: center;
            }
            #custom-modal .modal-close {
                position: absolute;
                top: 10px;
                right: 15px;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #2e7d32;
            }
            #custom-modal h3 {
                color: #2e7d32;
                margin-bottom: 1rem;
            }
            #custom-modal p {
                margin-bottom: 1.5rem;
            }
            [data-theme="dark"] #custom-modal .modal-content {
                background-color: #1a1a1a;
                border-color: #4caf50;
            }
            [data-theme="dark"] #custom-modal h3,
            [data-theme="dark"] #custom-modal .modal-close {
                color: #4caf50;
            }
        `;
        document.head.appendChild(style);
        
        // Event listeners
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        modal.querySelector('.modal-overlay').addEventListener('click', () => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        modal.querySelector('.modal-ok').addEventListener('click', () => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-message').textContent = message;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// ==================== NOTIFICATION BANNER ====================
function initNotificationBanner() {
    const banner = document.getElementById('notification-banner');
    const bannerClose = document.getElementById('banner-close');
    
    if (banner && !localStorage.getItem('bannerDismissed')) {
        setTimeout(() => {
            banner.classList.add('active');
        }, 1000);
    }
    
    if (bannerClose && banner) {
        bannerClose.addEventListener('click', () => {
            banner.classList.remove('active');
            localStorage.setItem('bannerDismissed', 'true');
        });
    }
}

// ==================== PAGE-SPECIFIC INITIALIZATION ====================
function initPageSpecific() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    
    // Login page
    if (currentPage === 'login.html') {
        const loginForm = document.querySelector('form[action="dashboard.html"]');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                showModal('Login successful! Redirecting to dashboard...', 'Success');
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            });
        }
    }
    
    // Logout page
    if (currentPage === 'logout.html') {
        showModal('You have been successfully logged out.', 'Logout');
    }
    
    // Profile page
    if (currentPage === 'profile.html') {
        const changePhotoBtn = document.querySelector('.btn-primary');
        if (changePhotoBtn && changePhotoBtn.textContent === 'Change Photo') {
            changePhotoBtn.addEventListener('click', () => {
                showModal('Profile photo updated successfully!', 'Profile Updated');
            });
        }
    }
    
    // Assignments page
    if (currentPage === 'assignments.html') {
        const submitBtns = document.querySelectorAll('.assignment-card .btn-primary');
        submitBtns.forEach(btn => {
            if (btn.textContent === 'Submit Now') {
                btn.addEventListener('click', () => {
                    showModal('Assignment submitted successfully!', 'Assignment Submitted');
                });
            }
        });
    }
    
    // Study Material page
    if (currentPage === 'study-material.html') {
        const downloadBtns = document.querySelectorAll('.btn-secondary');
        downloadBtns.forEach(btn => {
            if (btn.textContent === 'Download') {
                btn.addEventListener('click', () => {
                    showModal('Study material downloaded successfully!', 'Download Complete');
                });
            }
        });
        
        const materialBtns = document.querySelectorAll('.material-card .btn-primary');
        materialBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                showModal('Opening study materials...', 'Accessing Materials');
            });
        });
    }
}

// ==================== INITIALIZE ALL ====================
document.addEventListener('DOMContentLoaded', () => {
    initThemeSwitcher();
    initHamburgerMenu();
    initCollapsibleFAQ();
    initNotificationBanner();
    initPageSpecific();
});
