/**
 * archive-progress.js
 * -------------------
 * Polls the /charts/progress/{jobId} endpoint and updates the UI.
 *
 * REPLACES the old SSE (EventSource) approach.
 *
 * Usage
 * -----
 *   import { startProgressPolling } from './archive-progress.js';
 *
 *   // After store() returns { job_id }:
 *   startProgressPolling(jobId, {
 *       onStep    : (steps)   => renderSteps(steps),
 *       onDone    : (payload) => window.location = payload.redirect,
 *       onError   : (message) => showError(message),
 *       onProgress: (percent) => updateProgressBar(percent),
 *   });
 *
 * OR drop the <script> tag into a Blade view and call window.startProgressPolling(...)
 */

;(function (global) {
    'use strict';

    /**
     * @param {string}   jobId     - The job_id returned by the store endpoint.
     * @param {object}   callbacks
     * @param {Function} [callbacks.onStep]      - Called with the steps array on every poll.
     * @param {Function} [callbacks.onProgress]  - Called with a 0-100 integer on every poll.
     * @param {Function} [callbacks.onDone]      - Called once when status === 'done'.
     * @param {Function} [callbacks.onError]     - Called once when status === 'error' | 'not_found'.
     * @param {number}   [intervalMs=1500]       - How often to poll (milliseconds).
     */
    function startProgressPolling(jobId, callbacks, intervalMs) {
        intervalMs = intervalMs || 1500;

        var TERMINAL_STATES = ['done', 'error', 'not_found'];
        var timerId         = null;
        var stopped         = false;

        function stop() {
            stopped = true;
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        }

        async function poll() {
            if (stopped) return;

            var url = '/charts/progress/' + encodeURIComponent(jobId);

            try {
                var response = await fetch(url, {
                    method      : 'GET',
                    headers     : {
                        'Accept'           : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    credentials : 'same-origin',
                });

                // Handle HTTP-level errors (401, 403, 500 …)
                if (!response.ok && response.status !== 404) {
                    console.warn('[archive-progress] HTTP ' + response.status + ' — will retry.');
                    return;
                }

                var data = await response.json();

                // ── Fire callbacks ───────────────────────────────────────────
                if (typeof callbacks.onProgress === 'function') {
                    callbacks.onProgress(data.percent || 0);
                }

                if (data.steps && typeof callbacks.onStep === 'function') {
                    callbacks.onStep(data.steps);
                }

                // Terminal states — stop polling, then fire the right callback
                if (TERMINAL_STATES.indexOf(data.status) !== -1) {
                    stop();

                    if (data.status === 'done') {
                        if (typeof callbacks.onDone === 'function') {
                            callbacks.onDone(data);
                        }
                    } else {
                        var message = data.message || 'An unexpected error occurred.';
                        if (typeof callbacks.onError === 'function') {
                            callbacks.onError(message);
                        }
                    }
                }
            } catch (networkError) {
                // Network hiccup — just wait for the next interval
                console.warn('[archive-progress] Network error:', networkError.message);
            }
        }

        // Kick off immediately, then repeat at intervalMs
        poll();
        timerId = setInterval(poll, intervalMs);

        // Return a handle so the caller can cancel early if needed
        return { stop: stop };
    }

    // Export for module systems (Vite/Webpack) and browser globals
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { startProgressPolling: startProgressPolling };
    } else {
        global.startProgressPolling = startProgressPolling;
    }

}(typeof window !== 'undefined' ? window : this));


/* =============================================================================
 * BLADE / ALPINE INTEGRATION EXAMPLE
 * Copy the block below into your charts/create.blade.php (or a partial).
 * =============================================================================

<div x-data="archiveUploader()" x-init="init()">

    {{-- Progress bar --}}
    <div x-show="uploading" class="mt-4">
        <div class="flex justify-between text-sm mb-1">
            <span x-text="statusMessage">Preparing…</span>
            <span x-text="percent + '%'"></span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                 :style="'width:' + percent + '%'"></div>
        </div>
    </div>

    {{-- Step list --}}
    <ul x-show="steps.length" class="mt-3 space-y-1 text-sm">
        <template x-for="s in steps" :key="s.step">
            <li class="flex items-center gap-2">
                <span x-text="stepIcon(s.state)"></span>
                <span x-text="s.label"></span>
                <span x-show="s.detail" class="text-gray-400 text-xs" x-text="'(' + s.detail + ')'"></span>
            </li>
        </template>
    </ul>

    {{-- Error alert --}}
    <div x-show="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-300 rounded text-red-700 text-sm"
         x-text="errorMessage"></div>

</div>

<script>
function archiveUploader() {
    return {
        uploading     : false,
        percent       : 0,
        statusMessage : 'Queued…',
        steps         : [],
        errorMessage  : '',

        init() {
            // Listen for a custom event dispatched by your form submit handler
            window.addEventListener('archive:job-dispatched', (e) => {
                this.start(e.detail.jobId);
            });
        },

        start(jobId) {
            this.uploading     = true;
            this.errorMessage  = '';
            this.steps         = [];
            this.percent       = 0;
            this.statusMessage = 'Queued — waiting for worker…';

            startProgressPolling(jobId, {
                onProgress : (pct)     => { this.percent = pct; },
                onStep     : (steps)   => { this.steps = steps; },
                onDone     : (payload) => {
                    this.percent       = 100;
                    this.statusMessage = payload.message || 'Done!';
                    setTimeout(() => { window.location = payload.redirect; }, 800);
                },
                onError    : (msg) => {
                    this.uploading    = false;
                    this.errorMessage = msg;
                },
            });
        },

        stepIcon(state) {
            return { done: '✅', active: '⏳', error: '❌', skipped: '⏭️', pending: '⬜' }[state] || '⬜';
        },
    };
}
</script>

============================================================================= */