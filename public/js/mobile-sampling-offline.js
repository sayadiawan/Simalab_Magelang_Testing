/**
 * Mobile Sampling Offline Support
 * Handles offline data storage and sync
 */

// Prevent redeclaration
if (typeof MobileSamplingOffline === 'undefined') {
  class MobileSamplingOffline {
  constructor() {
    this.dbName = 'MobileSamplingDB';
    this.dbVersion = 1;
    this.db = null;
    this.init();
  }

  async init() {
    try {
      this.db = await this.openDB();
      this.setupOnlineListener();
      console.log('[Offline] Database initialized');
    } catch (error) {
      console.error('[Offline] Failed to initialize:', error);
    }
  }

  openDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, this.dbVersion);

      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);

      request.onupgradeneeded = (event) => {
        const db = event.target.result;

        // Store for session data
        if (!db.objectStoreNames.contains('sessions')) {
          const sessionStore = db.createObjectStore('sessions', { keyPath: 'id' });
          sessionStore.createIndex('userId', 'userId', { unique: false });
          sessionStore.createIndex('timestamp', 'timestamp', { unique: false });
        }

        // Store for draft data
        if (!db.objectStoreNames.contains('drafts')) {
          const draftStore = db.createObjectStore('drafts', { keyPath: 'id' });
          draftStore.createIndex('permohonanId', 'permohonanId', { unique: false });
          draftStore.createIndex('draftId', 'draftId', { unique: false });
          draftStore.createIndex('status', 'status', { unique: false });
        }

        // Store for sync queue
        if (!db.objectStoreNames.contains('syncQueue')) {
          const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
          syncStore.createIndex('type', 'type', { unique: false });
          syncStore.createIndex('status', 'status', { unique: false });
          syncStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
      };
    });
  }

  // Session Management
  async saveSession(sessionData) {
    try {
      const session = {
        id: 'current',
        ...sessionData,
        timestamp: Date.now(),
      };

      const transaction = this.db.transaction(['sessions'], 'readwrite');
      const store = transaction.objectStore('sessions');
      await store.put(session);

      // Also save to localStorage as backup
      localStorage.setItem('mobile_sampling_session', JSON.stringify(sessionData));
      localStorage.setItem('mobile_sampling_session_timestamp', Date.now().toString());

      console.log('[Offline] Session saved');
      return true;
    } catch (error) {
      console.error('[Offline] Failed to save session:', error);
      return false;
    }
  }

  async getSession() {
    try {
      const transaction = this.db.transaction(['sessions'], 'readonly');
      const store = transaction.objectStore('sessions');
      const request = store.get('current');

      return new Promise((resolve, reject) => {
        request.onsuccess = () => {
          const session = request.result;
          if (session) {
            resolve(session);
          } else {
            // Fallback to localStorage
            const localSession = localStorage.getItem('mobile_sampling_session');
            if (localSession) {
              resolve(JSON.parse(localSession));
            } else {
              resolve(null);
            }
          }
        };
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error('[Offline] Failed to get session:', error);
      // Fallback to localStorage
      const localSession = localStorage.getItem('mobile_sampling_session');
      return localSession ? JSON.parse(localSession) : null;
    }
  }

  async clearSession() {
    try {
      const transaction = this.db.transaction(['sessions'], 'readwrite');
      const store = transaction.objectStore('sessions');
      await store.delete('current');
      localStorage.removeItem('mobile_sampling_session');
      localStorage.removeItem('mobile_sampling_session_timestamp');
      console.log('[Offline] Session cleared');
      return true;
    } catch (error) {
      console.error('[Offline] Failed to clear session:', error);
      return false;
    }
  }

  // Draft Management
  async saveDraft(permohonanId, draftId, draftData) {
    try {
      const draft = {
        id: `${permohonanId}_${draftId}`,
        permohonanId,
        draftId,
        data: draftData,
        timestamp: Date.now(),
        status: 'pending',
      };

      const transaction = this.db.transaction(['drafts'], 'readwrite');
      const store = transaction.objectStore('drafts');
      await store.put(draft);

      console.log('[Offline] Draft saved:', draftId);
      return true;
    } catch (error) {
      console.error('[Offline] Failed to save draft:', error);
      return false;
    }
  }

  async getDraft(permohonanId, draftId) {
    try {
      const transaction = this.db.transaction(['drafts'], 'readonly');
      const store = transaction.objectStore('drafts');
      const request = store.get(`${permohonanId}_${draftId}`);

      return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error('[Offline] Failed to get draft:', error);
      return null;
    }
  }

  async getAllDrafts(permohonanId) {
    try {
      const transaction = this.db.transaction(['drafts'], 'readonly');
      const store = transaction.objectStore('drafts');
      const index = store.index('permohonanId');
      const request = index.getAll(permohonanId);

      return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error('[Offline] Failed to get drafts:', error);
      return [];
    }
  }

  // Sync Queue Management
  async addToSyncQueue(type, data, url, method = 'POST') {
    try {
      const syncItem = {
        type, // 'verify', 'update', 'delete'
        data,
        url,
        method,
        status: 'pending',
        timestamp: Date.now(),
        retries: 0,
      };

      const transaction = this.db.transaction(['syncQueue'], 'readwrite');
      const store = transaction.objectStore('syncQueue');
      await store.add(syncItem);

      console.log('[Offline] Added to sync queue:', type);
      return true;
    } catch (error) {
      console.error('[Offline] Failed to add to sync queue:', error);
      return false;
    }
  }

  async getSyncQueue() {
    try {
      const transaction = this.db.transaction(['syncQueue'], 'readonly');
      const store = transaction.objectStore('syncQueue');
      const index = store.index('status');
      const request = index.getAll('pending');

      return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error('[Offline] Failed to get sync queue:', error);
      return [];
    }
  }

  async updateSyncQueueItem(id, updates) {
    try {
      const transaction = this.db.transaction(['syncQueue'], 'readwrite');
      const store = transaction.objectStore('syncQueue');
      const item = await store.get(id);
      
      if (item) {
        Object.assign(item, updates);
        await store.put(item);
        return true;
      }
      return false;
    } catch (error) {
      console.error('[Offline] Failed to update sync queue item:', error);
      return false;
    }
  }

  async removeSyncQueueItem(id) {
    try {
      const transaction = this.db.transaction(['syncQueue'], 'readwrite');
      const store = transaction.objectStore('syncQueue');
      await store.delete(id);
      return true;
    } catch (error) {
      console.error('[Offline] Failed to remove sync queue item:', error);
      return false;
    }
  }

  // Sync Process (Non-blocking, background sync)
  async sync() {
    if (!navigator.onLine) {
      console.log('[Offline] Cannot sync - offline');
      return { success: false, message: 'Tidak ada koneksi internet', synced: 0, failed: 0 };
    }

    const queue = await this.getSyncQueue();
    if (queue.length === 0) {
      console.log('[Offline] No items to sync');
      return { success: true, message: 'Tidak ada data yang perlu disinkronkan', synced: 0, failed: 0 };
    }

    let synced = 0;
    let failed = 0;

    // Process sync in background without blocking UI
    // Use Promise.allSettled to handle all items even if some fail
    const syncPromises = queue.map(async (item) => {
      try {
        const response = await fetch(item.url, {
          method: item.method,
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
          body: JSON.stringify(item.data),
        });

        if (response.ok) {
          await this.removeSyncQueueItem(item.id);
          synced++;
          console.log('[Offline] Synced:', item.type, item.id);
          return { success: true, item };
        } else {
          item.retries++;
          if (item.retries >= 3) {
            await this.updateSyncQueueItem(item.id, { status: 'failed' });
            failed++;
          } else {
            await this.updateSyncQueueItem(item.id, { retries: item.retries });
          }
          console.error('[Offline] Sync failed:', item.type, item.id, response.status);
          return { success: false, item };
        }
      } catch (error) {
        item.retries++;
        if (item.retries >= 3) {
          await this.updateSyncQueueItem(item.id, { status: 'failed' });
          failed++;
        } else {
          await this.updateSyncQueueItem(item.id, { retries: item.retries });
        }
        console.error('[Offline] Sync error:', item.type, item.id, error);
        return { success: false, item };
      }
    });

    // Wait for all sync operations to complete (non-blocking, uses allSettled)
    await Promise.allSettled(syncPromises);

    return {
      success: true,
      message: `Disinkronkan: ${synced}, Gagal: ${failed}`,
      synced,
      failed,
    };
  }

  // Online/Offline Detection
  setupOnlineListener() {
    window.addEventListener('online', async () => {
      console.log('[Offline] Connection restored');
      this.showNotification('Koneksi internet tersambung. Menyinkronkan data...', 'success');
      
      // Auto sync when online (non-blocking)
      // Don't reload page, let SPA handle refresh
      this.sync().then(result => {
        if (result.synced > 0) {
          this.showNotification(`Berhasil menyinkronkan ${result.synced} data`, 'success');
          // Trigger custom event for SPA to handle refresh
          window.dispatchEvent(new CustomEvent('offline-sync-complete', { detail: result }));
        }
      }).catch(error => {
        console.error('[Offline] Sync error:', error);
      });
    });

    window.addEventListener('offline', () => {
      console.log('[Offline] Connection lost');
      this.showNotification('Tidak ada koneksi internet. Mode offline aktif.', 'warning');
    });
  }

  // Utility Methods
  isOnline() {
    return navigator.onLine;
  }

  showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `offline-notification offline-notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: ${type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#17a2b8'};
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      font-size: 14px;
      font-weight: 500;
      max-width: 90%;
      text-align: center;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      notification.style.transition = 'opacity 0.3s';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    }, 3000);
  }
}

  // Initialize offline support (only if not already initialized)
  if (!window.mobileSamplingOffline) {
    window.mobileSamplingOffline = new MobileSamplingOffline();

    // Register service worker
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw-mobile-sampling.js')
          .then((registration) => {
            console.log('[SW] Service Worker registered:', registration.scope);
          })
          .catch((error) => {
            console.error('[SW] Service Worker registration failed:', error);
          });
      });
    }
  }
}
