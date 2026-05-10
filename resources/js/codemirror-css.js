/**
 * Lazy-loaded CodeMirror 6 wrapper for the admin Custom CSS tab.
 * Kept in its own file so Vite emits a separate chunk that only loads
 * when the user actually opens that tab.
 *
 * mount(host, initial, onChange) → returns { setValue, destroy }.
 */
import { EditorView, keymap, lineNumbers, highlightActiveLine } from '@codemirror/view';
import { EditorState } from '@codemirror/state';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { bracketMatching, indentOnInput, syntaxHighlighting, defaultHighlightStyle } from '@codemirror/language';
import { css, cssCompletionSource } from '@codemirror/lang-css';
import { autocompletion, closeBrackets, closeBracketsKeymap, completionKeymap } from '@codemirror/autocomplete';
import { oneDark } from '@codemirror/theme-one-dark';

// Custom completion source: surface project-specific selectors and CSS
// variables so admins can discover what to target without leaving the editor.
const projectClasses = [
    // Base
    '.section', '.section-inner', '.section-inner--centered', '.section-title', '.section-lede',
    // Cover
    '.section--cover', '.section--cover-minimal', '.section--cover-portal',
    '.cover-inner', '.cover-inner--centered', '.cover-inner--portal',
    '.cover-title', '.cover-title--script', '.cover-eyebrow', '.cover-eyebrow--date', '.cover-date',
    '.cover-open-btn', '.cover-open-btn--minimal', '.cover-open-btn--portal',
    // Guest
    '.guest-greeting', '.guest-greeting--minimal', '.guest-honorific', '.guest-name', '.guest-relation',
    // Quotes
    '.section--quotes', '.ayat-arabic', '.ayat-translation', '.ayat-source',
    // Couple
    '.couple-grid', '.couple-person', '.couple-and', '.couple-photo', '.couple-photo-placeholder',
    '.couple-name', '.couple-fullname', '.couple-parents', '.couple-ig',
    // Story
    '.section--story', '.story-timeline', '.story-cards',
    '.story-entry', '.story-entry--left', '.story-entry--right',
    '.story-entry-card', '.story-entry-dot', '.story-entry-year', '.story-entry-title', '.story-entry-text', '.story-entry-photo',
    '.story-card', '.story-card-year', '.story-card-title', '.story-card-text', '.story-card-photo',
    // Event
    '.event-list', '.event-card', '.event-type', '.event-name', '.event-date', '.event-venue', '.event-address', '.event-maps-btn',
    // Countdown
    '.section--countdown', '.section--countdown-digital', '.section--countdown-minimal',
    '.countdown-cell', '.countdown-value', '.countdown-label',
    '.countdown-line', '.countdown-line-num', '.countdown-line-unit', '.countdown-eyebrow', '.countdown-message',
    // Gallery
    '.gallery-grid', '.gallery-item', '.gallery-staggered', '.gallery-staggered-item',
    '.gl-figure', '.gl-image', '.gl-counter',
    // Gift
    '.section--gift-inline', '.gift-card', '.gift-card-icon', '.gift-card-body',
    '.gift-card-bank', '.gift-card-number', '.gift-card-name', '.gift-card-copy',
    '.gift-account', '.gift-account-bank', '.gift-account-number', '.gift-account-name',
    '.gift-cta', '.gift-modal', '.gift-modal-card', '.gift-modal-title', '.gift-modal-close',
    // RSVP
    '.section--rsvp-card', '.rsvp-card-wrapper', '.rsvp-card-icon', '.rsvp-card-body',
    '.rsvp-form-wrap', '.rsvp-success',
    '.form-field', '.form-label', '.form-error', '.form-submit', '.form-radio-group',
    // Guestbook
    '.section--guestbook-wall', '.guestbook-list', '.guestbook-message',
    '.guestbook-name', '.guestbook-text', '.guestbook-time',
    '.guestbook-note', '.guestbook-note-text', '.guestbook-note-name', '.guestbook-note-time', '.guestbook-empty',
    // Thanks
    '.section--thanks', '.section--thanks-photo', '.section--thanks-elegant',
    '.thanks-message', '.thanks-couple', '.thanks-signature', '.thanks-divider', '.thanks-photo',
    // Deck UI
    '.invitation-deck', '.deck-nav', '.deck-nav-next', '.deck-nav-prev', '.deck-pagination',
    '.bgm-toggle', '.render-body', '.page-content',
];
const cssVars = ['--p', '--a', '--a2', '--bg', '--ink', '--muted', '--fd', '--fb', '--fs', '--fs-scale'];

function projectCompletions(context) {
    const word = context.matchBefore(/[.\-a-z0-9_]+/i);
    if (!word || (word.from === word.to && !context.explicit)) return null;
    const text = word.text;
    const options = [];

    // Selector chips when token starts with "."
    if (text.startsWith('.')) {
        for (const cls of projectClasses) {
            options.push({ label: cls, type: 'class' });
        }
    }
    // CSS var() autocompletion when user is mid-var-call or typed --
    if (text.startsWith('--') || context.matchBefore(/var\(\s*[-a-z]*$/i)) {
        for (const v of cssVars) {
            options.push({ label: v, type: 'variable', detail: 'theme var' });
        }
    }

    if (options.length === 0) return null;
    return { from: word.from, options, validFor: /^[.\-a-z0-9_]*$/i };
}

export function mount(host, initial, onChange) {
    let debounceId = null;
    const debounced = (value) => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => onChange?.(value), 250);
    };

    const state = EditorState.create({
        doc: initial ?? '',
        extensions: [
            lineNumbers(),
            highlightActiveLine(),
            history(),
            indentOnInput(),
            bracketMatching(),
            closeBrackets(),
            syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
            css(),
            autocompletion({
                override: [projectCompletions, cssCompletionSource],
                activateOnTyping: true,
                closeOnBlur: true,
            }),
            oneDark,
            keymap.of([
                indentWithTab,
                ...closeBracketsKeymap,
                ...completionKeymap,
                ...defaultKeymap,
                ...historyKeymap,
            ]),
            EditorView.updateListener.of((upd) => {
                if (upd.docChanged) {
                    debounced(upd.state.doc.toString());
                }
            }),
            EditorView.theme({
                '&': { height: '420px', fontSize: '13px', borderRadius: '12px', overflow: 'hidden' },
                '.cm-scroller': { fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace' },
                '.cm-gutters': { borderRight: '1px solid rgba(255,255,255,0.06)' },
            }),
        ],
    });

    const view = new EditorView({ state, parent: host });

    return {
        setValue(value) {
            view.dispatch({
                changes: { from: 0, to: view.state.doc.length, insert: value ?? '' },
            });
        },
        insertAtCursor(snippet) {
            const pos = view.state.selection.main.to;
            view.dispatch({
                changes: { from: pos, insert: snippet },
                selection: { anchor: pos + snippet.length },
            });
            view.focus();
        },
        destroy() {
            clearTimeout(debounceId);
            view.destroy();
        },
    };
}
