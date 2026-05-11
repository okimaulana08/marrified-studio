// Admin bundle entry.

// Lazy loader for the Custom CSS tab editor. Vite emits this as a separate
// chunk so codemirror only loads when the admin opens the CSS tab.
window.loadCustomCssEditor = () => import('./codemirror-css.js');

// Lazy loader for the Analytics tab charts. ApexCharts (~150 KB) only
// fetched when the user actually opens that tab.
window.loadCharts = () => import('./charts.js');

// Lazy loader for SortableJS drag-and-drop. Used by Sections tab reorder.
window.loadSortable = () => import('./sortable.js');
