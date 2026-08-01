/* =========================================================
   StudySync - Main JavaScript
   Dark mode, sidebar toggle, toast helper, AJAX form helper
   ========================================================= */

// ---------- Dark Mode ----------
(function initTheme() {
    const saved = localStorage.getItem('studysync-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('studysync-theme', next);
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = next === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}

// ---------- Sidebar toggle (mobile) ----------
function toggleSidebar() {
    document.querySelector('.sidebar')?.classList.toggle('show');
}

// ---------- Toast Notifications ----------
function showToast(message, type = 'success') {
    const containerId = 'toastContainer';
    let container = document.getElementById(containerId);
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = 2050;
        document.body.appendChild(container);
    }
    const bg = { success: 'success', error: 'danger', warning: 'warning', info: 'info' }[type] || 'success';
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${bg} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// ---------- Loading Spinner ----------
function showSpinner() {
    if (document.getElementById('globalSpinner')) return;
    const div = document.createElement('div');
    div.id = 'globalSpinner';
    div.className = 'spinner-overlay';
    div.innerHTML = '<div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>';
    document.body.appendChild(div);
}
function hideSpinner() {
    document.getElementById('globalSpinner')?.remove();
}

// ---------- Generic AJAX form submit helper ----------
// Usage: ajaxSubmit(formElement, 'subjects_action.php', () => location.reload());
async function ajaxSubmit(form, url, onSuccess) {
    showSpinner();
    try {
        const formData = new FormData(form);
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        hideSpinner();
        if (data.status === 'success') {
            showToast(data.message || 'Success', 'success');
            if (onSuccess) onSuccess(data);
        } else {
            showToast(data.message || 'Something went wrong', 'error');
        }
    } catch (err) {
        hideSpinner();
        showToast('Network error. Please try again.', 'error');
        console.error(err);
    }
}

// ---------- Delete confirmation helper ----------
// Usage: confirmDelete('subjects_action.php?action=delete&id=5', () => location.reload());
function confirmDelete(url, onSuccess, msg = 'Are you sure you want to delete this item?') {
    if (!confirm(msg)) return;
    showSpinner();
    fetch(url)
        .then(r => r.json())
        .then(data => {
            hideSpinner();
            if (data.status === 'success') {
                showToast(data.message || 'Deleted successfully', 'success');
                if (onSuccess) onSuccess();
            } else {
                showToast(data.message || 'Delete failed', 'error');
            }
        })
        .catch(() => { hideSpinner(); showToast('Network error', 'error'); });
}

// =========================================================
// POMODORO TIMER
// =========================================================
const Pomodoro = (function () {
    let duration = 25 * 60; // seconds
    let remaining = duration;
    let timerInterval = null;
    let mode = 'study'; // study | short | long

    const modes = { study: 25 * 60, short: 5 * 60, long: 15 * 60 };

    function updateDisplay() {
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        const el = document.getElementById('pomodoroDisplay');
        if (el) el.textContent = `${m}:${s}`;

        // update circular progress
        const circle = document.getElementById('pomodoroProgress');
        if (circle) {
            const total = modes[mode];
            const pct = 1 - remaining / total;
            const circumference = 2 * Math.PI * 110;
            circle.style.strokeDashoffset = circumference * (1 - pct);
        }
    }

    function setMode(newMode) {
        mode = newMode;
        remaining = modes[newMode];
        clearInterval(timerInterval);
        timerInterval = null;
        updateDisplay();
        document.querySelectorAll('.pomo-mode-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('mode-' + newMode)?.classList.add('active');
    }

    function start() {
        if (timerInterval) return;
        timerInterval = setInterval(() => {
            remaining--;
            updateDisplay();
            if (remaining <= 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                showToast(mode === 'study' ? 'Study session complete! Time for a break.' : 'Break over! Back to studying.', 'success');
                if (mode === 'study') logStudySession(modes.study / 3600);
            }
        }, 1000);
    }

    function pause() {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    function reset() {
        pause();
        remaining = modes[mode];
        updateDisplay();
    }

    function logStudySession(hours) {
        fetch('pomodoro_log.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'hours=' + hours
        });
    }

    return { start, pause, reset, setMode, updateDisplay };
})();

// ---------- Search/Filter helper for tables & cards ----------
function liveFilter(inputId, itemSelector, textSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll(itemSelector).forEach(item => {
            const text = (textSelector ? item.querySelector(textSelector) : item).textContent.toLowerCase();
            item.style.display = text.includes(q) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = document.documentElement.getAttribute('data-theme') === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
});
