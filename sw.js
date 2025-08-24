// sw.js — minimal service worker to make Chrome happy
self.addEventListener("install", event => {
  // Skip waiting so it activates right away
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  // Claim clients so the SW takes control immediately
  event.waitUntil(clients.claim());
});

self.addEventListener("fetch", event => {
  // Pass requests straight through to the network
  event.respondWith(fetch(event.request));
});