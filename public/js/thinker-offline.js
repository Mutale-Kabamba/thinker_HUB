/**
 * think.er HUB Offline Learning Engine (IndexedDB + Cache Storage + Auto-Sync)
 */
(function () {
    const DB_NAME = 'thinker_hub_offline_v1';
    const DB_VERSION = 1;
    const COURSE_CACHE_NAME = 'thinkerhub-offline-courses-v1';

    function openDb() {
        return new Promise((resolve, reject) => {
            if (!('indexedDB' in window)) {
                reject(new Error('IndexedDB is not supported in this browser.'));
                return;
            }
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('courses')) {
                    db.createObjectStore('courses', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('outbox')) {
                    db.createObjectStore('outbox', { keyPath: 'id' });
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    async function putRecord(storeName, value) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readwrite');
            tx.objectStore(storeName).put(value);
            tx.oncomplete = () => resolve(value);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function deleteRecord(storeName, key) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readwrite');
            tx.objectStore(storeName).delete(key);
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function getAllRecords(storeName) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const req = tx.objectStore(storeName).getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => reject(req.error);
        });
    }

    async function getRecord(storeName, key) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const req = tx.objectStore(storeName).get(key);
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = () => reject(req.error);
        });
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    const ThinkerOffline = {
        async downloadCourse(bundleUrl, onProgress) {
            const resp = await fetch(bundleUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!resp.ok) {
                throw new Error('Could not fetch course bundle (' + resp.status + ')');
            }
            const data = await resp.json();
            const course = data.course;
            const assetUrls = Array.isArray(data.asset_urls) ? data.asset_urls : [];

            const urlsToCache = ['/learn/offline', ...assetUrls];
            let completed = 0;

            if ('caches' in window) {
                const cache = await caches.open(COURSE_CACHE_NAME);
                for (const url of urlsToCache) {
                    try {
                        const assetResp = await fetch(url, { credentials: 'same-origin' });
                        if (assetResp && assetResp.ok) {
                            await cache.put(url, assetResp.clone());
                        }
                    } catch (_) {
                        // Skip individual asset errors without aborting the course download
                    }
                    completed++;
                    if (typeof onProgress === 'function') {
                        onProgress(completed, urlsToCache.length);
                    }
                }
            }

            course.asset_urls = assetUrls;
            await putRecord('courses', course);
            window.dispatchEvent(new CustomEvent('thinker-offline-updated'));
            return course;
        },

        async removeCourse(courseId) {
            const course = await getRecord('courses', Number(courseId));
            if (course && Array.isArray(course.asset_urls) && 'caches' in window) {
                const cache = await caches.open(COURSE_CACHE_NAME);
                for (const url of course.asset_urls) {
                    try {
                        await cache.delete(url);
                    } catch (_) {}
                }
            }
            await deleteRecord('courses', Number(courseId));
            window.dispatchEvent(new CustomEvent('thinker-offline-updated'));
        },

        async listCourses() {
            return getAllRecords('courses');
        },

        async getCourse(courseId) {
            return getRecord('courses', Number(courseId));
        },

        async saveCourseState(course) {
            await putRecord('courses', course);
        },

        async listOutbox() {
            return getAllRecords('outbox');
        },

        async queueAction(type, payload) {
            const item = {
                id: 'evt_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
                type,
                payload,
                queued_at: new Date().toISOString(),
            };
            await putRecord('outbox', item);
            window.dispatchEvent(new CustomEvent('thinker-offline-updated'));

            if (navigator.onLine) {
                this.syncOutbox().catch(() => {});
            }

            return item;
        },

        async syncOutbox(syncUrl = '/offline/sync', csrfToken = null) {
            if (!navigator.onLine) {
                return { synced: 0, remaining: (await this.listOutbox()).length };
            }

            const events = await this.listOutbox();
            if (!events.length) {
                return { synced: 0, remaining: 0 };
            }

            const token = csrfToken || getCsrfToken();
            const resp = await fetch(syncUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                },
                body: JSON.stringify({ events }),
            });

            if (!resp.ok) {
                throw new Error('Sync failed with status ' + resp.status);
            }

            const result = await resp.json();
            const syncedIds = Array.isArray(result.synced_ids) ? result.synced_ids : [];
            for (const id of syncedIds) {
                await deleteRecord('outbox', id);
            }

            window.dispatchEvent(new CustomEvent('thinker-offline-synced', { detail: { synced: syncedIds.length } }));
            window.dispatchEvent(new CustomEvent('thinker-offline-updated'));

            return {
                synced: syncedIds.length,
                remaining: (await this.listOutbox()).length,
            };
        },
    };

    window.ThinkerOffline = ThinkerOffline;

    // Automatically replay offline outbox when connectivity returns
    window.addEventListener('online', () => {
        ThinkerOffline.syncOutbox().catch(() => {});
    });
})();
