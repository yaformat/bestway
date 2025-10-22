// src/mixins/useCache.js
import hash from 'object-hash';
import { log } from '@/utils/logger';

const CACHE_EXPIRATION_TIME = 30 * 60 * 1000; // 30 минут

export const useCache = {
  cache: new Map(),
  generateCacheKey(resource, params = {}) {
    return `${resource}:${hash(params)}`;
  },
  isCacheValid(cacheEntry) {
    return cacheEntry && (Date.now() - cacheEntry.timestamp < CACHE_EXPIRATION_TIME);
  },
  clearCache(resource) {
    //this.cache.clear();
    for (let key of this.cache.keys()) {
      if (key.startsWith(`${resource}`)) {
        this.cache.delete(key);
      }
    }
    log(`Cache cleared for resource: ${resource}`);
  },
  getCachedData(resource, cacheKey) {
    if (this.cache.has(cacheKey) && this.isCacheValid(this.cache.get(cacheKey))) {
      log(`Cache hit for ${cacheKey} in resource: ${resource}`);
      return this.cache.get(cacheKey).data;
    }
    return null;
  },
  setCachedData(resource, cacheKey, data) {
    this.cache.set(cacheKey, { data, timestamp: Date.now() });
    log(`Saved to cache for ${cacheKey} in resource: ${resource}`);
  },
};
