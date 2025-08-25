// sw.js — minimal do-nothing service worker

self.addEventListener('install', event => {
  // activate immediately
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  // take control of pages immediately
  self.clients.claim();
});

// fetch event that does nothing — just goes to network
self.addEventListener('fetch', event => {
  // don’t intercept anything, just let network handle it
});
