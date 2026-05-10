// Admin bundle entry.

// Lazy loader for the Custom CSS tab editor. Vite emits this as a separate
// chunk so codemirror only loads when the admin opens the CSS tab.
window.loadCustomCssEditor = () => import('./codemirror-css.js');
