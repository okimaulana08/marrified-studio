/**
 * Lazy-loaded SortableJS wrapper for drag-and-drop reorder UIs. Kept in its
 * own file so Vite emits a separate chunk that only loads when the user opens
 * a tab that needs it.
 *
 * mount(container, handleSelector, onEnd) → returns the Sortable instance.
 * onEnd receives the new ordered list of `data-id` attribute values from the
 * children — so the caller doesn't need to deal with raw DOM events.
 */
import Sortable from 'sortablejs';

export function mount(container, { handle = '[data-drag-handle]', onEnd } = {}) {
    return Sortable.create(container, {
        handle,
        animation: 180,
        ghostClass: 'is-dragging-ghost',
        chosenClass: 'is-dragging-chosen',
        dragClass: 'is-dragging-active',
        // Native HTML5 drag (forceFallback: false) works better when each
        // row hosts its own Alpine x-data state — the JS-fallback clone
        // would otherwise re-mount Alpine and confuse the placeholder.
        forceFallback: false,
        onEnd: () => {
            const ids = Array.from(container.children)
                .map((el) => el.getAttribute('data-id'))
                .filter((id) => id !== null);
            onEnd?.(ids);
        },
    });
}
