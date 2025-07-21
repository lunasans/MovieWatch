// MovieWatch - Modern Design JavaScript with Fixed Tags

// Theme System
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
    
    // Tagify Theme auch aktualisieren
    applyTagifyTheme();
};

const displayThemeButtons = () => {
    const btnContainer = document.getElementById("themeSwitcher");
    if (!btnContainer) return;
    
    const savedTheme = localStorage.getItem('movieWatchTheme');
    let currentThemeBackground = themes[0].background;
    if (savedTheme) {
        currentThemeBackground = JSON.parse(savedTheme).background;
    }
    
    themes.forEach((theme, index) => {
        const div = document.createElement("div");
        div.className = "theme-btn";
        div.style.background = `linear-gradient(135deg, ${theme.primaryColor}, ${theme.accentColor})`;
        div.title = `Theme ${index + 1}: ${getThemeName(theme.background)}`;
        
        if (theme.background === currentThemeBackground) {
            div.classList.add('active');
        }
        
        div.addEventListener("click", () => {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            div.classList.add('active');
            setTheme(theme);
            updateFooterTheme(theme.background);
        });
        
        btnContainer.appendChild(div);
    });
};

function getThemeName(background) {
    const themeNames = {
        '#1a1a2e': 'Standard',
        '#461220': 'Romantic',
        '#192A51': 'Ocean', 
        '#2d1b69': 'Royal',
        '#0c5460': 'Forest'
    };
    return themeNames[background] || 'Custom';
}

function updateFooterTheme(background) {
    const currentThemeEl = document.getElementById('currentTheme');
    if (currentThemeEl) {
        currentThemeEl.textContent = getThemeName(background);
    }
}

// Variables
let currentMovieId = null;
let editTagify = null;
let addTagify = null;
let availableTags = [];

// Dark Mode funktionalität
function initDarkMode() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (!darkModeSwitch) return;

    if (localStorage.getItem('dark-mode') === 'true') {
        darkModeSwitch.checked = true;
    }

    darkModeSwitch.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('dark-mode', 'true');
            setTheme(themes[0]);
        } else {
            localStorage.setItem('dark-mode', 'false');
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

// Modal Funktionen - KORRIGIERT
function openModal(id, title, count, date, tags = []) {
    currentMovieId = id;
    
    document.getElementById('modalTitle').value = title;
    document.getElementById('modalCount').value = count;
    document.getElementById('modalDate').value = date ? date.split('T')[0] : '';

    // Tags laden und setzen - KORRIGIERT mit Debug-Output
    console.log('Loading tags for movie:', id);
    loadMovieTags(id).then(movieTags => {
        console.log('Loaded movie tags:', movieTags);
        if (editTagify) {
            editTagify.removeAllTags();
            if (movieTags && movieTags.length > 0) {
                // Tags als Array von Objekten hinzufügen für bessere Kompatibilität
                const tagObjects = movieTags.map(tag => ({ value: tag }));
                editTagify.addTags(tagObjects);
                console.log('Tags added to editTagify:', tagObjects);
            }
        } else {
            console.warn('editTagify not initialized');
        }
    }).catch(error => {
        console.error('Error loading movie tags:', error);
        showToast('Fehler beim Laden der Tags', 'warning');
    });

    document.getElementById('editModal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

// Neue Funktion: Tags für einen Film laden
async function loadMovieTags(movieId) {
    try {
        const response = await fetch(`get_movie_tags.php?movie_id=${movieId}`);
        const data = await response.json();
        return data.tags || [];
    } catch (error) {
        console.error('Error loading movie tags:', error);
        return [];
    }
}

function closeModal() {
    document.getElementById('editModal').classList.remove('is-active');
    document.body.style.overflow = '';
}

function openAddModal() {
    document.getElementById('addModalTitle').value = '';
    if (addTagify) {
        addTagify.removeAllTags();
    }
    
    document.getElementById('addModal').classList.add('is-active');
    document.body.style.overflow = 'hidden';
    
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

    toast.className = 'toast';
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

// Modal Daten speichern - KORRIGIERT
function saveModalData() {
    const title = document.getElementById('modalTitle').value.trim();
    const count = parseInt(document.getElementById('modalCount').value) || 0;
    const date = document.getElementById('modalDate').value;
    
    // Tags korrekt aus Tagify extrahieren
    let tags = [];
    if (editTagify && editTagify.value) {
        tags = editTagify.value.map(tag => tag.value || tag);
    }

    if (!title) {
        showToast("Bitte gib einen Titel ein.", 'error');
        return;
    }

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
            updateMovieDisplay(currentMovieId, title, count, date, data.data?.tags || []);
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

// Neue Funktion: Film-Anzeige aktualisieren
function updateMovieDisplay(movieId, title, count, date, tags) {
    const titleEl = document.getElementById('title-' + movieId);
    const infoEl = document.getElementById('info-' + movieId);
    
    if (titleEl) titleEl.textContent = title;
    
    if (infoEl) {
        let info = count + 'x gesehen';
        if (date) {
            const parts = date.split('-');
            info += ' – Zuletzt: ' + parts[2] + '.' + parts[1] + '.' + parts[0];
        }
        if (tags && tags.length > 0) {
            info += ' | Tags: ' + tags.join(', ');
        }
        infoEl.textContent = info;
    }
}

// Neuen Film hinzufügen - KORRIGIERT
function saveAddModal() {
    const title = document.getElementById('addModalTitle').value.trim();
    
    // Tags korrekt aus Tagify extrahieren
    let tags = [];
    if (addTagify && addTagify.value) {
        tags = addTagify.value.map(tag => tag.value || tag);
    }

    if (!title) {
        showToast('Bitte Titel eingeben.', 'error');
        return;
    }

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

            const filmList = document.getElementById('film-list');
            if (filmList) {
                const movieHtml = createMovieCardHtml(
                    data.id, 
                    title, 
                    0, 
                    null, 
                    data.data?.tags || []
                );
                filmList.insertAdjacentHTML('afterbegin', movieHtml);
                
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
            showToast('Fehler beim Hinzufügen: ' + (data.message || 'Unbekannter Fehler'), 'error');
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

// HTML für neue Movie Card erstellen - MIT TAGS
function createMovieCardHtml(id, title, count, lastDate, tags = []) {
    const lastInfo = lastDate ? ` – Zuletzt: ${formatDate(lastDate)}` : '';
    const tagsInfo = tags && tags.length > 0 ? ` | Tags: ${tags.join(', ')}` : '';
    
    return `
        <div id="movie-${id}" class="card movie-card">
            <div class="movie-info">
                <h2 id="title-${id}">${escapeHtml(title)}</h2>
                <p id="info-${id}" class="text-gray">
                    ${count}x gesehen${lastInfo}${tagsInfo}
                </p>
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
            animateCounter('like-count-' + id, data.likes);
            animateCounter('neutral-count-' + id, data.neutral);
            animateCounter('dislike-count-' + id, data.dislikes);

            [likeBtn, neutralBtn, dislikeBtn].forEach(btn => {
                if (btn) btn.classList.remove('active');
            });

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
    showToast('Funktion wird entwickelt...', 'info');
}

function showRecentlyWatched() {
    showToast('Funktion wird entwickelt...', 'info');
}

function showUnwatched() {
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
        
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            openAddModal();
        }
        
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Tagify Setup - VERBESSERT mit besserem CSS-Support
async function initTagify() {
    try {
        console.log('Initializing Tagify...');
        const res = await fetch('get_tags.php');
        if (!res.ok) {
            throw new Error('Failed to fetch tags');
        }
        
        const tagList = await res.json();
        availableTags = Array.isArray(tagList) ? tagList.map(tag => tag.value) : [];
        
        console.log('Loaded available tags:', availableTags);
        
        // Add Modal Tagify mit verbesserter Konfiguration
        const addTagInput = document.getElementById('addModalTags');
        if (addTagInput) {
            addTagify = new Tagify(addTagInput, {
                whitelist: availableTags,
                dropdown: {
                    enabled: 1,
                    maxItems: 10,
                    closeOnSelect: false,
                    highlightFirst: true,
                    searchKeys: ['value']
                },
                enforceWhitelist: false,
                maxTags: 10,
                trim: true,
                duplicates: false,
                editTags: false,
                placeholder: 'Tags hinzufügen...',
                validate: function(tagData) {
                    return tagData.value.length >= 2 && tagData.value.length <= 50;
                },
                transformTag: function(tagData) {
                    tagData.class = 'tag-item';
                }
            });
            
            // Event Listeners für besseres Debugging
            addTagify.on('add', function(e) {
                console.log('Tag added to add modal:', e.detail.data.value);
            });
            
            addTagify.on('remove', function(e) {
                console.log('Tag removed from add modal:', e.detail.data.value);
            });
            
            console.log('Add modal Tagify initialized successfully');
        }
        
        // Edit Modal Tagify mit verbesserter Konfiguration
        const editTagInput = document.getElementById('modalTags');
        if (editTagInput) {
            editTagify = new Tagify(editTagInput, {
                whitelist: availableTags,
                dropdown: {
                    enabled: 1,
                    maxItems: 10,
                    closeOnSelect: false,
                    highlightFirst: true,
                    searchKeys: ['value']
                },
                enforceWhitelist: false,
                maxTags: 10,
                trim: true,
                duplicates: false,
                editTags: false,
                placeholder: 'Tags bearbeiten...',
                validate: function(tagData) {
                    return tagData.value.length >= 2 && tagData.value.length <= 50;
                },
                transformTag: function(tagData) {
                    tagData.class = 'tag-item';
                },
                originalInputValueFormat: valuesArr => valuesArr.map(tag => tag.value || tag)
            });
            
            // Event Listeners für besseres Debugging
            editTagify.on('add', function(e) {
                console.log('Tag added to edit modal:', e.detail.data.value);
            });
            
            editTagify.on('remove', function(e) {
                console.log('Tag removed from edit modal:', e.detail.data.value);
            });
            
            console.log('Edit modal Tagify initialized successfully');
        }
        
        // CSS-Klassen dynamisch anwenden für bessere Theme-Integration
        applyTagifyTheme();
        
    } catch (error) {
        console.error('Fehler beim Laden der Tags:', error);
        showToast('Fehler beim Laden der Tags', 'warning');
    }
}

// Neue Funktion: Tagify Theme anwenden
function applyTagifyTheme() {
    // CSS-Variablen für aktuelles Theme setzen
    const savedTheme = localStorage.getItem('movieWatchTheme');
    if (savedTheme) {
        const theme = JSON.parse(savedTheme);
        const root = document.documentElement;
        
        // Tagify-spezifische CSS-Variablen setzen
        root.style.setProperty('--tagify-tag-bg', theme.accentColor);
        root.style.setProperty('--tagify-tag-hover', theme.primaryColor);
    }
    
    // KRITISCHES FIX: Dropdown-Styles zur Laufzeit setzen
    setTimeout(() => {
        const style = document.createElement('style');
        style.id = 'tagify-dropdown-fix';
        style.innerHTML = `
            .tagify__dropdown,
            .modal .tagify__dropdown,
            .modal.is-active .tagify__dropdown {
                background: var(--clr-background, #1a1a2e) !important;
                border: 2px solid var(--clr-accent, #3498db) !important;
                color: var(--clr-text, #ffffff) !important;
                z-index: 999999 !important;
            }
            
            .tagify__dropdown__item,
            .modal .tagify__dropdown__item {
                color: var(--clr-text, #ffffff) !important;
                background: transparent !important;
                text-shadow: none !important;
                font-weight: 500 !important;
            }
            
            .tagify__dropdown__item:hover,
            .tagify__dropdown__item.tagify__dropdown__item--active {
                background: var(--clr-accent, #3498db) !important;
                color: #ffffff !important;
            }
        `;
        
        // Alten Style entfernen falls vorhanden
        const oldStyle = document.getElementById('tagify-dropdown-fix');
        if (oldStyle) {
            oldStyle.remove();
        }
        
        document.head.appendChild(style);
    }, 100);
    
    console.log('Tagify theme applied with dropdown fix');
}

// Neue Funktion: Dropdown-Styles direkt setzen
function forceTagifyDropdownStyles() {
    // Observer für dynamisch erstellte Dropdowns
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('tagify__dropdown')) {
                        console.log('Tagify dropdown detected, applying styles...');
                        
                        // Direkte Style-Anwendung
                        node.style.background = 'var(--clr-background, #1a1a2e)';
                        node.style.border = '2px solid var(--clr-accent, #3498db)';
                        node.style.color = 'var(--clr-text, #ffffff)';
                        node.style.zIndex = '999999';
                        node.style.borderRadius = 'var(--radius-md, 1rem)';
                        node.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.8)';
                        node.style.maxHeight = '200px';
                        node.style.overflowY = 'auto';
                        
                        // Items innerhalb des Dropdowns stylen
                        const items = node.querySelectorAll('.tagify__dropdown__item');
                        items.forEach(item => {
                            item.style.color = 'var(--clr-text, #ffffff)';
                            item.style.background = 'transparent';
                            item.style.padding = '12px 16px';
                            item.style.borderBottom = '1px solid var(--clr-border, rgba(255,255,255,0.1))';
                            item.style.fontSize = '0.9rem';
                            item.style.fontWeight = '500';
                            item.style.cursor = 'pointer';
                            item.style.textShadow = 'none';
                            
                            // Hover-Event hinzufügen
                            item.addEventListener('mouseenter', function() {
                                this.style.background = 'var(--clr-accent, #3498db)';
                                this.style.color = '#ffffff';
                            });
                            
                            item.addEventListener('mouseleave', function() {
                                if (!this.classList.contains('tagify__dropdown__item--active')) {
                                    this.style.background = 'transparent';
                                    this.style.color = 'var(--clr-text, #ffffff)';
                                }
                            });
                        });
                    }
                });
            }
        });
    });
    
    // Observer starten
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('Tagify dropdown observer started');
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
    console.log('MovieWatch initializing...');
    
    // Theme System
    displayThemeButtons();
    const savedTheme = localStorage.getItem('movieWatchTheme');
    if (savedTheme) {
        setTheme(JSON.parse(savedTheme));
    }
    
    // Initialize components
    initDarkMode();
    
    // Tagify initialisieren mit Verzögerung für bessere Theme-Integration
    setTimeout(() => {
        initTagify();
    }, 100);
    
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
        
        /* Zusätzliche Tagify-Anpassungen */
        .tagify {
            --tag-bg: var(--clr-accent, #3498db) !important;
            --tag-hover: var(--clr-primary, #0f3460) !important;
            --tag-text-color: #ffffff !important;
            --tag-border-color: var(--clr-accent, #3498db) !important;
            --tags-border-color: var(--glass-border, rgba(255, 255, 255, 0.2)) !important;
            --placeholder-color: var(--clr-text-muted, rgba(255, 255, 255, 0.7)) !important;
        }
        
        .tagify__input {
            color: var(--clr-text, #ffffff) !important;
        }
        
        .tagify__tag {
            background: var(--clr-accent, #3498db) !important;
            color: #ffffff !important;
            border-radius: var(--radius-sm, 0.5rem) !important;
        }
        
        .tagify__tag:hover {
            background: var(--clr-primary, #0f3460) !important;
        }
        
        .tagify__dropdown {
            background: var(--glass-bg, rgba(255, 255, 255, 0.1)) !important;
            backdrop-filter: var(--glass-backdrop, blur(20px)) !important;
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.2)) !important;
        }
        
        .tagify__dropdown__item {
            color: var(--clr-text, #ffffff) !important;
        }
        
        .tagify__dropdown__item:hover {
            background: var(--clr-surface-hover, rgba(255, 255, 255, 0.15)) !important;
        }
    `;
    document.head.appendChild(style);
    
    console.log('MovieWatch initialized successfully!');
});