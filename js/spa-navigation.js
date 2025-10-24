// SPA Navigation System
class SPANavigation {
    constructor() {
        this.navItems = document.querySelectorAll('.list');
        this.contentSections = document.querySelectorAll('.content-section');
        this.indicator = document.querySelector('.indicator');
        this.currentSection = 'home-content';
        
        this.init();
    }
    
    init() {
        // Add event listeners to navigation items
        this.navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const target = item.querySelector('a').getAttribute('data-target');
                this.navigateTo(target, item);
            });
        });
        
        // Initialize indicator position
        this.setIndicatorPosition();
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.setIndicatorPosition();
        });
    }
    
    navigateTo(targetId, clickedItem) {
        // Hide all content sections
        this.contentSections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
            this.currentSection = targetId;
        }
        
        // Update navigation active state
        this.navItems.forEach(item => {
            item.classList.remove('active');
        });
        clickedItem.classList.add('active');
        
        // Update indicator position
        this.setIndicatorPosition();
        
        // Add smooth transition effect
        this.addTransitionEffect();
    }
    
    setIndicatorPosition() {
        const activeItem = document.querySelector('.list.active');
        if (activeItem && this.indicator) {
            const itemWidth = activeItem.offsetWidth;
            const itemLeft = activeItem.offsetLeft;
            this.indicator.style.left = (itemLeft + itemWidth / 2 - 35) + 'px';
        }
    }
    
    addTransitionEffect() {
        const activeContent = document.querySelector('.content-section.active');
        if (activeContent) {
            activeContent.style.opacity = '0';
            activeContent.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                activeContent.style.opacity = '1';
                activeContent.style.transform = 'translateY(0)';
            }, 50);
        }
    }
}

// Initialize SPA Navigation when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SPANavigation();
});