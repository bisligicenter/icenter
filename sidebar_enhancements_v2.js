// Enhanced Sidebar JavaScript v3.0
// Modern sidebar with search, keyboard shortcuts, and improved UX

class EnhancedSidebar {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarToggleOuter = document.getElementById('sidebarToggleOuter');
        this.sidebarSearch = document.getElementById('sidebarSearch');
        this.logoutButton = document.getElementById('logoutButton');
        this.logoutModal = document.getElementById('logoutModal');
        this.modalContent = document.getElementById('modalContent');
        this.cancelLogout = document.getElementById('cancelLogout');
        this.confirmLogout = document.getElementById('confirmLogout');
        this.notificationToast = document.getElementById('notificationToast');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.navItems = document.querySelectorAll('.nav-item');
        
        this.isCollapsed = false;
        this.isMobile = window.innerWidth < 1024;
        this.searchTimeout = null;
        this.currentSearchTerm = '';
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        this.setupSearchFunctionality();
        this.setupMobileResponsiveness();
        this.setupAnimations();
        this.setupAccessibility();
        this.loadUserPreferences();
    }

    setupEventListeners() {
        // Sidebar toggle events
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }
        
        if (this.sidebarToggleOuter) {
            this.sidebarToggleOuter.addEventListener('click', () => this.toggleSidebar());
        }

        // Logout modal events
        if (this.logoutButton) {
            this.logoutButton.addEventListener('click', () => this.showLogoutModal());
        }

        if (this.cancelLogout) {
            this.cancelLogout.addEventListener('click', () => this.hideLogoutModal());
        }

        if (this.confirmLogout) {
            this.confirmLogout.addEventListener('click', () => this.performLogout());
        }

        // Modal backdrop click
        if (this.logoutModal) {
            this.logoutModal.addEventListener('click', (e) => {
                if (e.target === this.logoutModal) {
                    this.hideLogoutModal();
                }
            });
        }

        // Mobile overlay click
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => this.hideSidebar());
        }

        // Window resize
        window.addEventListener('resize', () => this.handleResize());

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideLogoutModal();
                if (this.isMobile) {
                    this.hideSidebar();
                }
            }
        });

        // Navigation item hover effects
        this.navItems.forEach(item => {
            item.addEventListener('mouseenter', () => this.handleNavItemHover(item, true));
            item.addEventListener('mouseleave', () => this.handleNavItemHover(item, false));
            item.addEventListener('click', () => this.handleNavItemClick(item));
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + K for search
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                this.focusSearch();
            }

            // Cmd/Ctrl + B for sidebar toggle
            if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
                e.preventDefault();
                this.toggleSidebar();
            }

            // Arrow keys for navigation
            if (this.sidebarSearch === document.activeElement) {
                this.handleSearchNavigation(e);
            }
        });
    }

    setupSearchFunctionality() {
        if (!this.sidebarSearch) return;

        this.sidebarSearch.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });

        this.sidebarSearch.addEventListener('focus', () => {
            this.sidebarSearch.parentElement.classList.add('ring-2', 'ring-blue-500/50');
        });

        this.sidebarSearch.addEventListener('blur', () => {
            this.sidebarSearch.parentElement.classList.remove('ring-2', 'ring-blue-500/50');
        });
    }

    setupMobileResponsiveness() {
        if (this.isMobile) {
            this.hideSidebar();
        }
    }

    setupAnimations() {
        // Add entrance animations
        this.navItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
        });

        // Add hover sound effects (optional)
        this.navItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                this.playHoverSound();
            });
        });
    }

    setupAccessibility() {
        // Add ARIA labels
        this.navItems.forEach(item => {
            const title = item.getAttribute('data-title');
            if (title) {
                item.setAttribute('aria-label', title);
            }
        });

        // Add focus management
        this.navItems.forEach(item => {
            item.addEventListener('focus', () => {
                item.classList.add('ring-2', 'ring-blue-500/50');
            });

            item.addEventListener('blur', () => {
                item.classList.remove('ring-2', 'ring-blue-500/50');
            });
        });
    }

    toggleSidebar() {
        if (this.isMobile) {
            this.sidebar.classList.toggle('mobile-open');
            this.sidebarOverlay.classList.toggle('active');
        } else {
            this.isCollapsed = !this.isCollapsed;
            this.sidebar.classList.toggle('collapsed');
            this.saveUserPreferences();
        }
    }

    showSidebar() {
        if (this.isMobile) {
            this.sidebar.classList.add('mobile-open');
            this.sidebarOverlay.classList.add('active');
        } else {
            this.isCollapsed = false;
            this.sidebar.classList.remove('collapsed');
        }
    }

    hideSidebar() {
        if (this.isMobile) {
            this.sidebar.classList.remove('mobile-open');
            this.sidebarOverlay.classList.remove('active');
        } else {
            this.isCollapsed = true;
            this.sidebar.classList.add('collapsed');
        }
    }

    showLogoutModal() {
        if (this.logoutModal && this.modalContent) {
            this.logoutModal.classList.remove('hidden');
            setTimeout(() => {
                this.modalContent.classList.remove('scale-95', 'opacity-0');
                this.modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    hideLogoutModal() {
        if (this.logoutModal && this.modalContent) {
            this.modalContent.classList.add('scale-95', 'opacity-0');
            this.modalContent.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                this.logoutModal.classList.add('hidden');
            }, 300);
        }
    }

    performLogout() {
        // Add loading state
        this.confirmLogout.classList.add('loading');
        this.confirmLogout.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>Logging out...';
        
        // Simulate logout process
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 1000);
    }

    focusSearch() {
        if (this.sidebarSearch) {
            this.sidebarSearch.focus();
            this.sidebarSearch.select();
        }
    }

    performSearch(query) {
        this.currentSearchTerm = query.toLowerCase();
        
        this.navItems.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const category = item.getAttribute('data-category') || '';
            const text = item.textContent.toLowerCase();
            
            const matches = title.toLowerCase().includes(this.currentSearchTerm) ||
                          category.toLowerCase().includes(this.currentSearchTerm) ||
                          text.includes(this.currentSearchTerm);
            
            if (this.currentSearchTerm === '') {
                item.style.display = 'flex';
                item.classList.remove('search-highlight');
            } else if (matches) {
                item.style.display = 'flex';
                item.classList.add('search-highlight');
            } else {
                item.style.display = 'none';
                item.classList.remove('search-highlight');
            }
        });

        // Show search results count
        this.showSearchResults();
    }

    showSearchResults() {
        const visibleItems = Array.from(this.navItems).filter(item => 
            item.style.display !== 'none'
        );

        if (this.currentSearchTerm && visibleItems.length > 0) {
            this.showNotification(`${visibleItems.length} result${visibleItems.length > 1 ? 's' : ''} found`);
        }
    }

    handleSearchNavigation(e) {
        const visibleItems = Array.from(this.navItems).filter(item => 
            item.style.display !== 'none'
        );
        
        const currentIndex = visibleItems.indexOf(document.activeElement);
        let nextIndex = currentIndex;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                nextIndex = (currentIndex + 1) % visibleItems.length;
                break;
            case 'ArrowUp':
                e.preventDefault();
                nextIndex = currentIndex === 0 ? visibleItems.length - 1 : currentIndex - 1;
                break;
            case 'Enter':
                e.preventDefault();
                if (visibleItems[currentIndex]) {
                    visibleItems[currentIndex].click();
                }
                break;
        }

        if (visibleItems[nextIndex]) {
            visibleItems[nextIndex].focus();
        }
    }

    handleNavItemHover(item, isHovering) {
        if (isHovering) {
            item.classList.add('hover:scale-105');
            this.addRippleEffect(item);
        } else {
            item.classList.remove('hover:scale-105');
        }
    }

    handleNavItemClick(item) {
        // Add click animation
        item.classList.add('scale-95');
        setTimeout(() => {
            item.classList.remove('scale-95');
        }, 150);

        // Show loading state if needed
        if (item.href) {
            this.showLoadingState(item);
        }
    }

    addRippleEffect(element) {
        const ripple = document.createElement('div');
        ripple.className = 'ripple-effect';
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;
        
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    showLoadingState(item) {
        const originalContent = item.innerHTML;
        item.classList.add('loading');
        item.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>Loading...';
        
        // Simulate loading
        setTimeout(() => {
            item.classList.remove('loading');
            item.innerHTML = originalContent;
        }, 1000);
    }

    showNotification(message, type = 'success') {
        if (this.notificationToast) {
            const toast = this.notificationToast.cloneNode(true);
            toast.querySelector('p').textContent = message;
            
            // Set type-specific styling
            if (type === 'error') {
                toast.querySelector('.border-l-4').classList.remove('border-blue-500');
                toast.querySelector('.border-l-4').classList.add('border-red-500');
                toast.querySelector('.bg-blue-500').classList.remove('bg-blue-500');
                toast.querySelector('.bg-blue-500').classList.add('bg-red-500');
                toast.querySelector('i').classList.remove('ri-check-line');
                toast.querySelector('i').classList.add('ri-error-warning-line');
            }
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
    }

    playHoverSound() {
        // Optional: Add subtle hover sound
        // const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
        // audio.volume = 0.1;
        // audio.play().catch(() => {});
    }

    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth < 1024;
        
        if (wasMobile !== this.isMobile) {
            if (this.isMobile) {
                this.hideSidebar();
            } else {
                this.showSidebar();
            }
        }
    }

    loadUserPreferences() {
        const preferences = localStorage.getItem('sidebarPreferences');
        if (preferences) {
            const { collapsed } = JSON.parse(preferences);
            if (collapsed && !this.isMobile) {
                this.isCollapsed = true;
                this.sidebar.classList.add('collapsed');
            }
        }
    }

    saveUserPreferences() {
        const preferences = {
            collapsed: this.isCollapsed,
            timestamp: Date.now()
        };
        localStorage.setItem('sidebarPreferences', JSON.stringify(preferences));
    }

    // Public methods for external use
    getCurrentState() {
        return {
            isCollapsed: this.isCollapsed,
            isMobile: this.isMobile,
            searchTerm: this.currentSearchTerm
        };
    }

    setActiveItem(href) {
        this.navItems.forEach(item => {
            item.classList.remove('active');
            if (item.href && item.href.includes(href)) {
                item.classList.add('active');
            }
        });
    }

    refreshSidebar() {
        // Reinitialize sidebar if needed
        this.setupEventListeners();
        this.loadUserPreferences();
    }
}

// Initialize sidebar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.enhancedSidebar = new EnhancedSidebar();
    
    // Set active item based on current page
    const currentPath = window.location.pathname;
    const fileName = currentPath.split('/').pop();
    window.enhancedSidebar.setActiveItem(fileName);
});

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
`;
document.head.appendChild(style);

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedSidebar;
} 