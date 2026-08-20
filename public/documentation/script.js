// Sidebar Toggle for Mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Active Navigation Item
const navItems = document.querySelectorAll('.nav-item');
const sections = document.querySelectorAll('.doc-section');

function updateActiveNav() {
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (window.pageYOffset >= sectionTop - 200) {
            current = section.getAttribute('id');
        }
    });

    navItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('href') === `#${current}`) {
            item.classList.add('active');
        }
    });
}

// Smooth scroll for navigation links
navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = item.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        
        if (targetSection) {
            const headerHeight = 64;
            const targetPosition = targetSection.offsetTop - headerHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            // Close sidebar on mobile after clicking
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('open');
            }
        }
    });
});

// Update active nav on scroll
window.addEventListener('scroll', updateActiveNav);
updateActiveNav(); // Initial call

// Header navigation links
const headerNavLinks = document.querySelectorAll('.header-nav .nav-link');
headerNavLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = link.getAttribute('href');
        
        if (targetId === '#home') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (targetId === '#docs') {
            const firstSection = document.querySelector('.doc-section');
            if (firstSection) {
                const headerHeight = 64;
                const targetPosition = firstSection.offsetTop - headerHeight;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        }
    });
});

// Language Select (placeholder for future implementation)
const languageSelect = document.getElementById('language-select');
if (languageSelect) {
    languageSelect.addEventListener('change', (e) => {
        // Future: Implement language switching
        console.log('Language changed to:', e.target.value);
    });
}

// Copy code blocks functionality
const codeBlocks = document.querySelectorAll('.code-block');
codeBlocks.forEach(block => {
    block.style.position = 'relative';
    block.style.cursor = 'pointer';
    
    block.addEventListener('click', () => {
        const text = block.textContent;
        navigator.clipboard.writeText(text).then(() => {
            // Show feedback
            const originalBg = block.style.backgroundColor;
            block.style.backgroundColor = '#d1fae5';
            setTimeout(() => {
                block.style.backgroundColor = originalBg;
            }, 500);
        });
    });
    
    // Add copy icon
    const copyIcon = document.createElement('i');
    copyIcon.className = 'fas fa-copy';
    copyIcon.style.cssText = 'position: absolute; top: 10px; right: 10px; color: #64748b; cursor: pointer; opacity: 0.6; transition: opacity 0.2s;';
    block.appendChild(copyIcon);
    
    block.addEventListener('mouseenter', () => {
        copyIcon.style.opacity = '1';
    });
    
    block.addEventListener('mouseleave', () => {
        copyIcon.style.opacity = '0.6';
    });
});

// Search functionality (future enhancement)
function initSearch() {
    // This can be enhanced with a search input
    console.log('Search functionality ready');
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    updateActiveNav();
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('open');
        }
    }, 250);
});

// Screenshot Modal Functionality
function initScreenshotModal() {
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'screenshot-modal';
    modal.id = 'screenshotModal';
    
    const closeBtn = document.createElement('span');
    closeBtn.className = 'screenshot-modal-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = () => {
        modal.classList.remove('active');
    };
    
    const img = document.createElement('img');
    img.id = 'screenshotModalImg';
    
    modal.appendChild(closeBtn);
    modal.appendChild(img);
    document.body.appendChild(modal);
    
    // Add click handlers to all screenshots
    const screenshots = document.querySelectorAll('.screenshot-container img');
    screenshots.forEach(screenshot => {
        screenshot.style.cursor = 'pointer';
        screenshot.addEventListener('click', () => {
            const imgSrc = screenshot.src;
            document.getElementById('screenshotModalImg').src = imgSrc;
            modal.classList.add('active');
        });
    });
    
    // Close modal when clicking outside image
    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target === closeBtn) {
            modal.classList.remove('active');
        }
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
}

// Initialize screenshot modal
document.addEventListener('DOMContentLoaded', () => {
    initScreenshotModal();
});

