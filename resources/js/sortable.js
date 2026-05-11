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
        forceFallback: true,
        fallbackTolerance: 4,
        onEnd: () => {
            const ids = Array.from(container.children)
                .map((el) => el.getAttribute('data-id'))
                .filter((id) => id !== null);
            onEnd?.(ids);
        },
    });
}
