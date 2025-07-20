// MovieWatch - Modern Design JavaScript

// Theme System (wie im Login)
const themes = [
    { background: "#1a1a2e", color: "#ffffff", primaryColor: "#0f3460", accentColor: "#3498db" },
    { background: "#461220", color: "#ffffff", primaryColor: "#E94560", accentColor: "#ff6b8a" },
    { background: "#192A51", color: "#ffffff", primaryColor: "#967AA1", accentColor: "#c39bd3" },
    { background: "#2d1b69", color: "#ffffff", primaryColor: "#8e44ad", accentColor: "#9b59b6" },
    { background: "#0c5460", color: "#ffffff", primaryColor: "#16a085", accentColor: "#1abc9c" }
];

const setTheme = (theme) => {
    const root = document.documentElement;
    Object.entries(theme).forEach(([key, value]) => {
        root.style.setProperty(`--${key.replace(/([A-Z])/g, '-$1').toLowerCase()}`, value);
    });
    localStorage.setItem('movieWatchTheme', JSON.stringify(theme));
};

const displayThemeButtons = () => {
    const btnContainer = document.getElementById("themeSwitcher");
    if (!btnContainer) return;
    
    themes.forEach((theme, index) => {
        const div = document.createElement("div");
        div.className = "theme-btn";
        div.style.background = `linear-gradient(135deg, ${theme.primaryColor}, ${theme.accentColor})`;
        div.title = `Theme ${index + 1}`;
        btnContainer.appendChild(div);
        div.addEventListener("click", () => setTheme(theme));
    });
};

// Variables
let currentMovieId = null;
let editTagify = null;
let addTagify = null;

// Dark Mode funktionalität (Legacy Support)
function initDarkMode() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (!darkModeSwitch) return;

    // Legacy Dark Mode Check
    if (localStorage.getItem('dark-mode') === 'true') {
        darkModeSwitch.checked = true;
    }

    darkModeSwitch.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('dark-mode', 'true');
            // Optionally switch to a dark theme
            setTheme(themes[0]); // Default dark theme
        } else {
            localStorage.setItem('dark-mode', 'false');
            // Switch to light theme if available
        }
    });
}

// Suche
function searchMovies(query) {
    fetch('search.php?q=' + encodeURIComponent(query))
        .then(response => response.text())
        .then(html => {
            const filmList = document.getElementById('film-list');
            if (filmList) {
                filmList.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showToast('Fehler bei der Suche', 'error');
        });
}

// Modal Funktionen
function openModal(id, title, count, date, tags = []) {
    currentMovieId = id;
    
    document.getElementById('modalTitle').value = title;
    document.getElementById('modalCount').value = count;
    document.getElementById('modalDate').value = date ? date.split('T')[0] : '';

    // Tags setzen
    if (editTagify) {
        editTagify.removeAllTags();
        if (Array.isArray(tags) && tags.length > 0) {
            editTagify.addTags(tags);
        }
    }

    document.getElementById('editModal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('editModal').classList.remove('is-active');
    document.body.style.overflow = '';
}

function openAddModal() {
    // Felder leeren
    document.getElementById('addModalTitle').value = '';
    if (addTagify) {
        addTagify.removeAllTags();
    }
    
    document.getElementById('addModal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
    
    // Focus auf Titel-Feld
    setTimeout(() => {
        document.getElementById('addModalTitle').focus();
    }, 100);
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('is-active');
    document.body.style.overflow = '';
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('is-active');
    document.body.style.overflow = '';
}

// Toast Notifications
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    // Remove existing classes
    toast.className = 'toast';
    
    // Add type class
    toast.classList.add(`toast-${type}`);
    
    toast.innerHTML = `
        <i class="bi bi-${getToastIcon(type)}"></i>
        ${message}
    `;
    
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-triangle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

// Modal Daten speichern
function saveModalData() {
    const title = document.getElementById('modalTitle').value.trim();
    const count = parseInt(document.getElementById('modalCount').value) || 0;
    const date = document.getElementById('modalDate').value;
    const tags = editTagify ? editTagify.value.map(tag => tag.value) : [];

    if (!title) {
        showToast("Bitte gib einen Titel ein.", 'error');
        return;
    }

    // Button Loading State
    const saveBtn = document.querySelector('#editModal .btn-success');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite;"></i> Speichern...';

    fetch('update_movie.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentMovieId, title, count, date, tags })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // UI aktualisieren
            const titleEl = document.getElementById('title-' + currentMovieId);
            const infoEl = document.getElementById('info-' + currentMovieId);
            
            if (titleEl) titleEl.textContent = title;
            
            if (infoEl) {
                let info = count + 'x gesehen';
                if (date) {
                    const parts = date.split('-');
                    info += ' – Zuletzt: ' + parts[2] + '.' + parts[1] + '.' + parts[0];
                }
                infoEl.textContent = info;
            }

            closeModal();
            showToast('Film erfolgreich aktualisiert!', 'success');
        } else {
            showToast(data.message || 'Fehler beim Speichern', 'error');
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        showToast('Netzwerkfehler beim Speichern', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Neuen Film hinzufügen
function saveAddModal() {
    const title = document.getElementById('addModalTitle').value.trim();
    const tags = addTagify ? addTagify.value.map(tag => tag.value).join(',') : '';

    if (!title) {
        showToast('Bitte Titel eingeben.', 'error');
        return;
    }

    // Button Loading State
    const addBtn = document.querySelector('#addModal .btn-primary');
    const originalText = addBtn.innerHTML;
    addBtn.disabled = true;
    addBtn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite;"></i> Hinzufügen...';

    fetch('add_movie.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, tags })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeAddModal();
            showToast('Film erfolgreich hinzugefügt!', 'success');

            // Neuen Film zur Liste hinzufügen
            const filmList = document.getElementById('film-list');
            if (filmList) {
                const movieHtml = createMovieCardHtml(data.id, title, 0, null, tags);
                filmList.insertAdjacentHTML('afterbegin', movieHtml);
                
                // Animation für neuen Film
                const newMovie = document.getElementById('movie-' + data.id);
                if (newMovie) {
                    newMovie.style.opacity = '0';
                    newMovie.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        newMovie.style.transition = 'all 0.3s ease';
                        newMovie.style.opacity = '1';
                        newMovie.style.transform = 'translateY(0)';
                    }, 100);
                }
            }
        } else {
            showToast('Fehler beim Hinzufügen', 'error');
        }
    })
    .catch(error => {
        console.error('Add error:', error);
        showToast('Netzwerkfehler beim Hinzufügen', 'error');
    })
    .finally(() => {
        addBtn.disabled = false;
        addBtn.innerHTML = originalText;
    });
}

// HTML für neue Movie Card erstellen
function createMovieCardHtml(id, title, count, lastDate, tags) {
    const lastInfo = lastDate ? ` – Zuletzt: ${formatDate(lastDate)}` : '';
    const tagsInfo = tags ? ` | Tags: ${tags}` : '';
    
    return `
        <div id="movie-${id}" class="card movie-card">
            <div class="movie-info">
                <h2 id="title-${id}">${escapeHtml(title)}</h2>
                <p id="info-${id}">${count}x gesehen${lastInfo}${tagsInfo}</p>
            </div>
            
            <div class="movie-actions">
                <div class="rating-buttons">
                    <button onclick="rateMovie(${id}, 'like')" class="rating-btn like" id="like-btn-${id}">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span id="like-count-${id}">0</span>
                    </button>
                    <button onclick="rateMovie(${id}, 'neutral')" class="rating-btn neutral" id="neutral-btn-${id}">
                        <i class="bi bi-dash-circle"></i>
                        <span id="neutral-count-${id}">0</span>
                    </button>
                    <button onclick="rateMovie(${id}, 'dislike')" class="rating-btn dislike" id="dislike-btn-${id}">
                        <i class="bi bi-hand-thumbs-down"></i>
                        <span id="dislike-count-${id}">0</span>
                    </button>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="openModal(${id}, '${escapeHtml(title)}', ${count}, null)" class="btn btn-secondary btn-small">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="movie.php?id=${id}" class="btn btn-secondary btn-small" title="Sichtungen bearbeiten">
                        <i class="bi bi-calendar-event"></i>
                    </a>
                    <button onclick="deleteMovie(${id})" class="btn btn-danger btn-small">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Film löschen
function deleteMovie(id) {
    if (!confirm('Film wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) return;

    fetch('delete_movie.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const movieEl = document.getElementById('movie-' + id);
            if (movieEl) {
                // Animation beim Löschen
                movieEl.style.transition = 'all 0.3s ease';
                movieEl.style.opacity = '0';
                movieEl.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    movieEl.remove();
                }, 300);
            }
            showToast('Film erfolgreich gelöscht!', 'success');
        } else {
            showToast('Fehler beim Löschen', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Netzwerkfehler beim Löschen', 'error');
    });
}

// Film bewerten
function rateMovie(id, type) {
    const likeBtn = document.getElementById('like-btn-' + id);
    const neutralBtn = document.getElementById('neutral-btn-' + id);
    const dislikeBtn = document.getElementById('dislike-btn-' + id);

    fetch('rate_movie.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, type })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Zähler aktualisieren
            animateCounter('like-count-' + id, data.likes);
            animateCounter('neutral-count-' + id, data.neutral);
            animateCounter('dislike-count-' + id, data.dislikes);

            // Active-Status aktualisieren
            [likeBtn, neutralBtn, dislikeBtn].forEach(btn => {
                if (btn) btn.classList.remove('active');
            });

            // Active-Button markieren
            const activeBtn = type === 'like' ? likeBtn : 
                             type === 'neutral' ? neutralBtn : dislikeBtn;
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            showToast('Bewertung gespeichert!', 'success');
        } else {
            showToast(data.message || 'Fehler beim Bewerten', 'error');
        }
    })
    .catch(error => {
        console.error('Rating error:', error);
        showToast('Netzwerkfehler beim Bewerten', 'error');
    });
}

// Counter Animation
function animateCounter(elementId, newValue) {
    const el = document.getElementById(elementId);
    if (!el) return;
    
    const oldValue = parseInt(el.textContent) || 0;
    const diff = newValue - oldValue;
    if (diff === 0) return;
    
    let current = oldValue;
    const step = diff > 0 ? 1 : -1;
    const duration = Math.min(Math.abs(diff) * 50, 500);
    const interval = duration / Math.abs(diff);

    const animation = setInterval(() => {
        current += step;
        el.textContent = current;
        if (current === newValue) {
            clearInterval(animation);
        }
    }, interval);
}

// Sidebar Funktionen
function showTopRated() {
    // Implementierung für Top-bewertete Filme
    showToast('Funktion wird entwickelt...', 'info');
}

function showRecentlyWatched() {
    // Implementierung für zuletzt gesehene Filme
    showToast('Funktion wird entwickelt...', 'info');
}

function showUnwatched() {
    // Implementierung für noch nicht gesehene Filme
    showToast('Funktion wird entwickelt...', 'info');
}

// Utility Funktionen
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('de-DE');
}

// Keyboard Shortcuts
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // ESC zum Schließen von Modals
        if (e.key === 'Escape') {
            if (document.getElementById('editModal').classList.contains('is-active')) {
                closeModal();
            }
            if (document.getElementById('addModal').classList.contains('is-active')) {
                closeAddModal();
            }
            if (document.getElementById('detailModal').classList.contains('is-active')) {
                closeDetailModal();
            }
        }
        
        // Ctrl+N für neuen Film
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            openAddModal();
        }
        
        // Ctrl+F für Suche
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Tagify Setup
async function initTagify() {
    try {
        const res = await fetch('get_tags.php');
        const tagList = await res.json();
        const whitelist = tagList.map(tag => tag.value);
        
        // Add Modal Tagify
        const addTagInput = document.getElementById('addModalTags');
        if (addTagInput) {
            addTagify = new Tagify(addTagInput, {
                whitelist: whitelist,
                dropdown: {
                    enabled: 0,
                    maxItems: 10
                },
                enforceWhitelist: false
            });
        }
        
        // Edit Modal Tagify
        const editTagInput = document.getElementById('modalTags');
        if (editTagInput) {
            editTagify = new Tagify(editTagInput, {
                whitelist: whitelist,
                dropdown: {
                    enabled: 0,
                    maxItems: 10
                },
                enforceWhitelist: false,
                originalInputValueFormat: valuesArr => valuesArr.map(tag => tag.value)
            });
        }
    } catch (error) {
        console.error('Fehler beim Laden der Tags:', error);
    }
}

// Modal Click Outside to Close
function initModalClickOutside() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            if (e.target.id === 'editModal') closeModal();
            if (e.target.id === 'addModal') closeAddModal();
            if (e.target.id === 'detailModal') closeDetailModal();
        }
    });
}

// Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Theme System
    displayThemeButtons();
    const savedTheme = localStorage.getItem('movieWatchTheme');
    if (savedTheme) {
        setTheme(JSON.parse(savedTheme));
    }
    
    // Initialize components
    initDarkMode();
    initTagify();
    initKeyboardShortcuts();
    initModalClickOutside();
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
    
    console.log('MovieWatch initialized successfully!');
});