/**
 * Mobile Sampling SPA (Single Page Application)
 * Loads all pages once and uses show/hide for navigation
 */

// Prevent redeclaration
if (typeof MobileSamplingSPA === 'undefined') {
  class MobileSamplingSPA {
  constructor() {
    this.currentPage = null;
    this.pages = {};
    this.loadedPages = new Set();
    this.isLoading = false;
    this.isInitialLoad = true; // Flag to track initial page load
    this.init();
  }

  init() {
    // Initialize on page load
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setup());
    } else {
      this.setup();
    }
  }

  setup() {
    // Mark current page
    const currentPath = window.location.pathname;
    this.currentPage = this.getPageIdFromPath(currentPath);
    
    // IMPORTANT: Don't hide original body content on initial page load
    // Only hide when navigating to other pages via SPA navigation
    // This prevents showing blank blue screen on first load
    
    // Setup navigation
    this.setupNavigation();
    
    // Load ALL pages in background for future navigation (non-blocking)
    // Don't hide original content or show SPA pages on initial load
    if (this.currentPage !== 'login') {
    this.loadAllPagesImmediately();
    }
    
    // Setup background sync
    this.setupBackgroundSync();
  }
  
  hideOriginalBody() {
    // Don't hide original body on initial page load
    // Only hide when navigating to other pages via SPA
    if (this.isInitialLoad) {
      return;
    }
    
    // Hide original body content when SPA pages are loaded
    // This prevents showing both original page and SPA page
    // Only call this when actually showing an SPA page (during navigation)
    if (!document.body.hasAttribute('data-spa-active')) {
      // Mark body as SPA active
      document.body.setAttribute('data-spa-active', 'true');
      
      // Hide all direct children of body that are not SPA pages
      // Wait a bit for DOM to be ready
      setTimeout(() => {
        const bodyChildren = Array.from(document.body.children);
        bodyChildren.forEach(child => {
          if (!child.classList.contains('spa-page') && 
              child.id !== 'spa-loader' && 
              child.id !== 'offlineIndicator' &&
              !child.classList.contains('offline-notification')) {
            // Hide original content
            child.style.cssText += 'display: none !important; visibility: hidden !important; position: absolute !important; z-index: -9999 !important; opacity: 0 !important; pointer-events: none !important;';
          }
        });
      }, 100);
    }
  }
  
  async loadAllPagesImmediately() {
    // Get permohonan ID from current URL
    const currentPath = window.location.pathname;
    const idMatch = currentPath.match(/\/mobile\/sampling\/([^\/]+)/);
    if (!idMatch) return;
    
    const permohonanId = idMatch[1];
    const baseUrl = window.location.origin;
    
    // List of ALL pages to load immediately (all static pages)
    const pagesToLoad = [
      { id: 'draft-list', url: `${baseUrl}/mobile/sampling/${permohonanId}/drafts` },
      { id: 'form', url: `${baseUrl}/mobile/sampling/${permohonanId}/form` },
      { id: 'login', url: `${baseUrl}/mobile/sampling/${permohonanId}` },
      // Note: Dynamic pages (draft-edit, signature, success) will be loaded on-demand
    ];
    
    // Load all pages in parallel (non-blocking, silent)
    // Don't wait for completion, just start loading
    pagesToLoad.forEach(async ({ id, url }) => {
      if (!this.loadedPages.has(id)) {
        try {
          const html = await this.loadPage(url);
          if (html) {
            this.cachePage(id, html);
            console.log('[SPA] Preloaded page:', id);
          }
        } catch (error) {
          console.warn('[SPA] Failed to preload page:', id, error);
        }
      }
    });
    
    console.log('[SPA] Started preloading all pages in background');
  }
  
  // Load dynamic page (like draft-edit with draft_id)
  async loadDynamicPage(url, pageId) {
    if (this.pages[pageId]) {
      // Already loaded
      return this.pages[pageId];
    }
    
    try {
      const html = await this.loadPage(url);
      if (html) {
        this.cachePage(pageId, html);
        return this.pages[pageId];
      }
    } catch (error) {
      console.error('[SPA] Failed to load dynamic page:', pageId, error);
    }
    return null;
  }

  getPageIdFromPath(path) {
    // Extract page ID from path
    if (path.includes('/draft/') && path.includes('/edit')) {
      // Dynamic page - include draft_id in ID
      const match = path.match(/\/draft\/([^\/]+)\/edit/);
      if (match) {
        return `draft-edit-${match[1]}`;
      }
      return 'draft-edit';
    } else if (path.includes('/drafts')) {
      return 'draft-list';
    } else if (path.includes('/form')) {
      return 'form';
    } else if (path.includes('/login') || (path.includes('/mobile/sampling/') && !path.includes('/drafts') && !path.includes('/form') && !path.includes('/edit'))) {
      return 'login';
    } else if (path.includes('/signature')) {
      return 'signature';
    } else if (path.includes('/success')) {
      return 'success';
    } else if (path.includes('/edit/')) {
      // Dynamic page - include sample_id in ID
      const match = path.match(/\/edit\/([^\/]+)/);
      if (match) {
        return `edit-${match[1]}`;
      }
      return 'edit';
    }
    return 'home';
  }

  setupNavigation() {
    // Intercept all links
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href*="/mobile/sampling/"]');
      if (link && !link.hasAttribute('data-spa-ignore')) {
        e.preventDefault();
        const href = link.getAttribute('href');
        this.navigate(href);
      }
    });

    // Intercept form submissions
    document.addEventListener('submit', (e) => {
      const form = e.target;
      // Skip if form has data-spa-ignore attribute
      if (form.hasAttribute('data-spa-ignore')) {
        return; // Let form submit normally
      }
      if (form.action && form.action.includes('/mobile/sampling/')) {
        // Let form submit normally for POST requests
        // But we can handle navigation after success
        if (form.method.toLowerCase() === 'get') {
          e.preventDefault();
          this.navigate(form.action);
        }
      }
    });
  }

  async navigate(url) {
    if (this.isLoading) return;
    
    // Mark that we're navigating (not initial load anymore)
    this.isInitialLoad = false;
    
    try {
      const pageId = this.getPageIdFromPath(url);
      
      // Check if page is already loaded
      // For draft-list page, always reload to get fresh data
      if (this.pages[pageId] && pageId !== 'draft-list') {
        // Page already loaded, just show it (instant, no loading, no server call)
        // But skip cache for draft-list to ensure fresh data
        this.showPage(pageId);
        return;
      }
      
      // For draft-list, clear cache and reload
      if (pageId === 'draft-list' && this.pages[pageId]) {
        delete this.pages[pageId];
        // Also remove from DOM if exists
        const existingPage = document.getElementById(`spa-page-${pageId}`);
        if (existingPage) {
          existingPage.remove();
        }
      }

      // Page not loaded yet, load it (but don't show loading if it's a dynamic page)
      this.isLoading = true;
      
      // Only show loading for static pages that should have been preloaded
      const staticPages = ['draft-list', 'form', 'login'];
      if (staticPages.includes(pageId)) {
        this.showLoading();
      }

      const html = await this.loadPage(url);
      if (html) {
        this.cachePage(pageId, html);
        this.showPage(pageId);
      }

      this.isLoading = false;
      this.hideLoading();
    } catch (error) {
      console.error('[SPA] Navigation error:', error);
      // If offline, try to show cached version
      if (!navigator.onLine) {
        const pageId = this.getPageIdFromPath(url);
        if (this.pages[pageId]) {
          this.showPage(pageId);
          return;
        }
      }
      // Fallback to normal navigation only if really needed
      window.location.href = url;
    }
  }

  async loadPage(url) {
    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const html = await response.text();
      return html;
    } catch (error) {
      console.error('[SPA] Failed to load page:', url, error);
      // If offline, try to get from cache
      if (!navigator.onLine && window.mobileSamplingOffline) {
        return await this.getPageFromCache(url);
      }
      throw error;
    }
  }

  async getPageFromCache(url) {
    // Try to get from service worker cache
    try {
      const cache = await caches.open('mobile-sampling-v1');
      const cached = await cache.match(url);
      if (cached) {
        return await cached.text();
      }
    } catch (error) {
      console.error('[SPA] Cache error:', error);
    }
    return null;
  }

  cachePage(pageId, html) {
    // Remove old page if exists
    const oldPage = document.getElementById(`spa-page-${pageId}`);
    if (oldPage) {
      oldPage.remove();
    }
    
    // Create a temporary container to parse HTML
    const temp = document.createElement('div');
    temp.innerHTML = html;

    // Extract body content
    const bodyContent = temp.querySelector('body') || temp;
    
    // Create page container (completely hidden by default)
    const pageContainer = document.createElement('div');
    pageContainer.id = `spa-page-${pageId}`;
    pageContainer.className = 'spa-page';
    pageContainer.style.cssText = 'display: none; position: absolute; top: 0; left: 0; width: 100%; min-height: 100vh; z-index: -1; visibility: hidden; opacity: 0; pointer-events: none;';
    pageContainer.innerHTML = bodyContent.innerHTML;

    // Extract and move scripts (but don't execute yet)
    const scripts = bodyContent.querySelectorAll('script');
    const scriptContents = [];
    scripts.forEach(script => {
      if (script.src) {
        // External script - add cache busting if not already present
        let scriptSrc = script.src;
        if (!scriptSrc.includes('?v=') && !scriptSrc.includes('&v=')) {
          // Add cache busting parameter
          const separator = scriptSrc.includes('?') ? '&' : '?';
          scriptSrc = scriptSrc + separator + 'v=' + Date.now();
        }
        
        // Check if already loaded (compare base URL without cache busting)
        const baseUrl = scriptSrc.split('?')[0].split('&')[0];
        const existing = Array.from(document.querySelectorAll('script[data-spa-loaded]')).find(
          s => s.src && s.src.split('?')[0].split('&')[0] === baseUrl
        );
        
        if (!existing) {
          const newScript = document.createElement('script');
          newScript.src = scriptSrc;
          newScript.setAttribute('data-spa-page', pageId);
          newScript.setAttribute('data-spa-loaded', 'true');
          document.head.appendChild(newScript);
        }
      } else if (script.textContent && script.textContent.trim()) {
        // Inline script - store for later execution
        scriptContents.push(script.textContent);
      }
    });

    // Store script contents for later execution
    if (scriptContents.length > 0) {
      pageContainer.setAttribute('data-scripts', JSON.stringify(scriptContents));
    }

    // Extract and move styles (only if not already loaded)
    const styles = bodyContent.querySelectorAll('style, link[rel="stylesheet"]');
    styles.forEach(style => {
      if (style.tagName === 'STYLE') {
        // Check if style already exists
        const existing = document.querySelector(`style[data-spa-page="${pageId}"]`);
        if (!existing) {
          const newStyle = document.createElement('style');
          newStyle.textContent = style.textContent;
          newStyle.setAttribute('data-spa-page', pageId);
          document.head.appendChild(newStyle);
        }
      } else if (style.tagName === 'LINK') {
        // Check if already loaded
        const existing = document.querySelector(`link[href="${style.href}"]`);
        if (!existing) {
          const linkClone = style.cloneNode(true);
          linkClone.setAttribute('data-spa-page', pageId);
          document.head.appendChild(linkClone);
        }
      }
    });

    // Append to body
    document.body.appendChild(pageContainer);
    
    // Store reference
    this.pages[pageId] = pageContainer;
    this.loadedPages.add(pageId);
    
    console.log('[SPA] Cached page:', pageId);
  }

  showPage(pageId) {
    // Hide all pages (instant, no animation) - ensure they're completely hidden
    document.querySelectorAll('.spa-page').forEach(page => {
      page.style.cssText = 'display: none !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; min-height: 100vh !important; z-index: -1 !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;';
    });

    // Show target page (instant)
    const targetPage = document.getElementById(`spa-page-${pageId}`);
    if (targetPage) {
      targetPage.style.cssText = 'display: block !important; position: relative !important; width: 100% !important; min-height: 100vh !important; z-index: 1 !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;';
      this.currentPage = pageId;
      
      // Only hide original body AFTER we successfully show the SPA page
      // This prevents blank screen if SPA page fails to load
      this.hideOriginalBody();
      
      // Scroll to top (instant)
      window.scrollTo({ top: 0, behavior: 'instant' });
      
      // Update URL without reload (no history push for same page)
      const currentUrl = window.location.pathname;
      const expectedUrl = this.getUrlFromPageId(pageId);
      if (currentUrl !== expectedUrl) {
        this.updateURL(pageId);
      }
      
      // Reinitialize scripts if needed (only once per page load)
      if (!targetPage.hasAttribute('data-scripts-executed')) {
        this.reinitializePage(targetPage);
        targetPage.setAttribute('data-scripts-executed', 'true');
      }
      
      console.log('[SPA] Showing page:', pageId, '(instant, no server call)');
    } else {
      console.warn('[SPA] Page not found:', pageId, '- keeping original content visible');
      // Don't hide original body if SPA page doesn't exist
    }
  }
  
  getUrlFromPageId(pageId) {
    const baseUrl = window.location.origin;
    const currentPath = window.location.pathname;
    const idMatch = currentPath.match(/\/mobile\/sampling\/([^\/]+)/);
    const permohonanId = idMatch ? idMatch[1] : '';
    
    switch(pageId) {
      case 'draft-list':
        return `${baseUrl}/mobile/sampling/${permohonanId}/drafts`;
      case 'form':
        return `${baseUrl}/mobile/sampling/${permohonanId}/form`;
      case 'login':
        return `${baseUrl}/mobile/sampling/${permohonanId}`;
      default:
        return currentPath;
    }
  }

  updateURL(pageId) {
    // Update URL based on page ID (you may need to adjust this)
    const baseUrl = window.location.origin;
    let newUrl = baseUrl;
    
    // Get permohonan ID from current URL or session
    const currentPath = window.location.pathname;
    const idMatch = currentPath.match(/\/mobile\/sampling\/([^\/]+)/);
    const permohonanId = idMatch ? idMatch[1] : '';
    
    switch(pageId) {
      case 'draft-list':
        newUrl = `${baseUrl}/mobile/sampling/${permohonanId}/drafts`;
        break;
      case 'form':
        newUrl = `${baseUrl}/mobile/sampling/${permohonanId}/form`;
        break;
      case 'login':
        newUrl = `${baseUrl}/mobile/sampling/${permohonanId}`;
        break;
      default:
        return; // Don't update URL for unknown pages
    }
    
    window.history.pushState({ page: pageId }, '', newUrl);
  }

  reinitializePage(pageElement) {
    // Get stored script contents
    const scriptsJson = pageElement.getAttribute('data-scripts');
    if (scriptsJson) {
      try {
        const scriptContents = JSON.parse(scriptsJson);
        scriptContents.forEach((scriptContent, index) => {
          try {
            // Wait for jQuery if script uses jQuery/$
            const waitForJQuery = (callback) => {
              if (typeof jQuery !== 'undefined' || typeof $ !== 'undefined') {
                // Ensure jQuery is available globally
                if (typeof jQuery !== 'undefined') {
                  window.jQuery = window.$ = jQuery;
                } else if (typeof $ !== 'undefined') {
                  window.jQuery = window.$ = $;
                }
                callback();
              } else {
                // Wait a bit and try again
                setTimeout(() => waitForJQuery(callback), 50);
              }
            };
            
            // Wrap script in IIFE to prevent redeclaration errors
            // Use a unique scope name to avoid conflicts
            const scopeId = `spa_scope_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            const wrappedScript = `
              (function(${scopeId}) {
                try {
                  ${scriptContent}
                } catch(e) {
                  console.warn('[SPA] Script execution warning:', e.message);
                }
              })({});
            `;
            
            // Check if script uses jQuery
            const usesJQuery = scriptContent.includes('$(') || 
                              scriptContent.includes('jQuery(') ||
                              scriptContent.includes('select2') ||
                              scriptContent.includes('Select2');
            
            if (usesJQuery) {
              // Wait for jQuery before executing
              waitForJQuery(() => {
                const newScript = document.createElement('script');
                newScript.textContent = wrappedScript;
                newScript.setAttribute('data-spa-script-index', index);
                document.body.appendChild(newScript);
                setTimeout(() => {
                  if (document.body.contains(newScript)) {
                    document.body.removeChild(newScript);
                  }
                }, 100);
              });
            } else {
              // Execute immediately if no jQuery dependency
            const newScript = document.createElement('script');
            newScript.textContent = wrappedScript;
            newScript.setAttribute('data-spa-script-index', index);
            document.body.appendChild(newScript);
            setTimeout(() => {
              if (document.body.contains(newScript)) {
                document.body.removeChild(newScript);
              }
            }, 100);
            }
          } catch (error) {
            console.error('[SPA] Script execution error:', error);
          }
        });
      } catch (error) {
        console.error('[SPA] Failed to parse scripts:', error);
      }
    }
  }


  setupBackgroundSync() {
    // Sync in background without blocking UI (completely silent)
    if (window.mobileSamplingOffline) {
      // Sync every 30 seconds when online (silent, no UI blocking)
      setInterval(() => {
        if (navigator.onLine && !this.isLoading) {
          // Run sync in background without await (fire and forget)
          this.syncInBackground();
        }
      }, 30000); // Every 30 seconds
      
      // Sync immediately when coming online (silent)
      window.addEventListener('online', () => {
        // Run sync in background without await
        this.syncInBackground();
      });
    }
  }
  
  syncInBackground() {
    // Run sync completely in background, no UI blocking, fire and forget
    if (!window.mobileSamplingOffline) return;
    
    // Don't await, run completely in background (non-blocking)
    window.mobileSamplingOffline.sync().then(result => {
      if (result.synced > 0) {
        // Show subtle notification (non-blocking, auto-dismiss)
        this.showSyncNotification(`Disinkronkan ${result.synced} data`);
        
        // Silently refresh current page data if needed (non-blocking, fire and forget)
        if (this.currentPage === 'draft-list') {
          // Refresh in background without showing loading
          this.refreshDraftListSilently();
        }
      }
    }).catch(error => {
      // Silently log error, don't show to user
      console.error('[SPA] Background sync error:', error);
    });
  }
  
  async refreshDraftListSilently() {
    // Refresh draft list silently without showing loading indicator or blocking UI
    const currentPath = window.location.pathname;
    const idMatch = currentPath.match(/\/mobile\/sampling\/([^\/]+)/);
    if (!idMatch) return;
    
    const permohonanId = idMatch[1];
    const url = `${window.location.origin}/mobile/sampling/${permohonanId}/drafts`;
    
    // Run in background, don't await (fire and forget)
    this.loadPage(url).then(html => {
      if (html) {
        // Update cached page silently
        this.cachePage('draft-list', html);
        // If currently viewing draft-list, show updated version (instant)
        if (this.currentPage === 'draft-list') {
          this.showPage('draft-list');
        }
        console.log('[SPA] Draft list refreshed silently');
      }
    }).catch(error => {
      // Silently fail, don't show error
      console.error('[SPA] Silent refresh error:', error);
    });
  }

  async refreshDraftList() {
    // Silently refresh draft list without full page reload
    const currentPath = window.location.pathname;
    const idMatch = currentPath.match(/\/mobile\/sampling\/([^\/]+)/);
    if (!idMatch) return;
    
    const permohonanId = idMatch[1];
    const url = `${window.location.origin}/mobile/sampling/${permohonanId}/drafts`;
    
    try {
      const html = await this.loadPage(url);
      if (html) {
        this.cachePage('draft-list', html);
        if (this.currentPage === 'draft-list') {
          this.showPage('draft-list');
        }
      }
    } catch (error) {
      console.error('[SPA] Refresh error:', error);
    }
  }

  showLoading() {
    // Show subtle loading indicator (only if page not cached)
    let loader = document.getElementById('spa-loader');
    if (!loader) {
      loader = document.createElement('div');
      loader.id = 'spa-loader';
      loader.style.cssText = `
        position: fixed;
        top: 10px;
        right: 10px;
        background: rgba(45, 107, 207, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 10000;
        display: none;
        align-items: center;
        gap: 8px;
        transition: opacity 0.3s;
      `;
      loader.innerHTML = '<span>⏳</span> <span>Memuat...</span>';
      document.body.appendChild(loader);
    }
    loader.style.display = 'flex';
    loader.style.opacity = '1';
  }

  hideLoading() {
    const loader = document.getElementById('spa-loader');
    if (loader) {
      loader.style.opacity = '0';
      setTimeout(() => {
        loader.style.display = 'none';
      }, 300);
    }
  }

  showSyncNotification(message) {
    // Show subtle sync notification (non-blocking, auto-dismiss)
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 60px;
      right: 10px;
      background: #28a745;
      color: white;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 12px;
      z-index: 10000;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      opacity: 0;
      transform: translateX(20px);
      transition: all 0.3s ease;
      pointer-events: none;
    `;
    notification.textContent = `✅ ${message}`;
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
      notification.style.opacity = '1';
      notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto-dismiss after 2 seconds
    setTimeout(() => {
      notification.style.opacity = '0';
      notification.style.transform = 'translateX(20px)';
      setTimeout(() => {
        if (document.body.contains(notification)) {
          document.body.removeChild(notification);
        }
      }, 300);
    }, 2000);
  }
}

  // Initialize SPA (only if not already initialized)
  if (!window.mobileSamplingSPA) {
    window.mobileSamplingSPA = new MobileSamplingSPA();
  }

  // Handle browser back/forward
  window.addEventListener('popstate', (e) => {
    if (e.state && e.state.page && window.mobileSamplingSPA) {
      window.mobileSamplingSPA.showPage(e.state.page);
    }
  });
}
