// Dark Mode funktionalität
if (localStorage.getItem('dark-mode') === 'true') {
  document.documentElement.setAttribute('data-theme', 'dark');
  if (document.getElementById('darkModeSwitch')) {
    document.getElementById('darkModeSwitch').checked = true;
  }
}

// Event-Listener für Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
  const darkModeSwitch = document.getElementById('darkModeSwitch');
  if (darkModeSwitch) {
    darkModeSwitch.addEventListener('change', function() {
      if (this.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('dark-mode', 'true');
      } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('dark-mode', 'false');
      }
    });
  }
});

// Suche
function searchMovies(query) {
  fetch('search.php?q=' + encodeURIComponent(query))
    .then(response => response.text())
    .then(html => {
      document.getElementById('film-list').innerHTML = html;
    });
}

let currentMovieId = null;

// Modal öffnen/schließen
function openModal(id, title, count, date, tags = []) {
  currentMovieId = id;
  document.getElementById('modalTitle').value = title;
  document.getElementById('modalCount').value = count;
  document.getElementById('modalDate').value = date ? date.split('T')[0] : '';

  if (window.tagifyEdit) {
    tagifyEdit.removeAllTags();
    tagifyEdit.addTags(tags);
  }

  document.getElementById('editModal').classList.add('is-active');
}

function closeModal() {
  document.getElementById('editModal').classList.remove('is-active');
}

function openAddModal() {
  document.getElementById('addModal').classList.add('is-active');
}

function closeAddModal() {
  document.getElementById('addModal').classList.remove('is-active');
  // Felder leeren
  document.getElementById('addModalTitle').value = '';
  document.getElementById('addModalTags').value = '';
}

// Toast Notifications
function showToast(message) {
  const toast = document.getElementById('toast');
  if (!toast) return;

  toast.textContent = message;
  toast.classList.add('show');
  toast.style.display = 'block';

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => {
      toast.style.display = 'none';
    }, 300);
  }, 3000);
} 

// Modal Daten speichern
function saveModalData() {
  const title = document.getElementById('modalTitle').value.trim();
  const count = parseInt(document.getElementById('modalCount').value);
  const date = document.getElementById('modalDate').value;
  const tags = editTagify ? editTagify.value.map(tag => tag.value) : [];

  if (!title) {
    alert("Bitte gib einen Titel ein.");
    return;
  }

  fetch('update_movie.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: currentMovieId, title, count, date, tags })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('title-' + currentMovieId).textContent = title;

      let info = count + 'x gesehen';
      if (date) {
        const parts = date.split('-');
        info += ' – Zuletzt: ' + parts[2] + '.' + parts[1] + '.' + parts[0];
      }
      document.getElementById('info-' + currentMovieId).textContent = info;

      closeModal();
      showToast('Erfolgreich gespeichert!');
    } else {
      alert(data.message || 'Fehler beim Speichern');
    }
  });
}

// Neuen Film hinzufügen
function saveAddModal() {
  const title = document.getElementById('addModalTitle').value.trim();
  const tags = document.getElementById('addModalTags').value;

  if (!title) {
    alert('Bitte Titel eingeben.');
    return;
  }

  fetch('add_movie.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title, tags })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      closeAddModal();
      showToast('Film hinzugefügt!');

      // Neuen Film zur Liste hinzufügen
      const filmList = document.getElementById('film-list');
      const div = document.createElement('div');
      div.id = 'movie-' + data.id;
      div.className = 'card movie-card hover-lift';

      div.innerHTML = `
        <div class="movie-info">
          <h2 id="title-${data.id}">${title}</h2>
          <p id="info-${data.id}" class="text-gray">0x gesehen</p>
        </div>
        <div class="movie-actions">
          <div class="rating-buttons">
            <button onclick="rateMovie(${data.id}, 'like')" class="rating-btn like" id="like-btn-${data.id}">
              👍 <span id="like-count-${data.id}">0</span>
            </button>
            <button onclick="rateMovie(${data.id}, 'neutral')" class="rating-btn neutral" id="neutral-btn-${data.id}">
              😐 <span id="neutral-count-${data.id}">0</span>
            </button>
            <button onclick="rateMovie(${data.id}, 'dislike')" class="rating-btn dislike" id="dislike-btn-${data.id}">
              👎 <span id="dislike-count-${data.id}">0</span>
            </button>
          </div>
          <div class="flex gap-2">
            <button onclick="openModal(${data.id}, '${title.replace(/'/g,"\\'")}', 0, null)"
              class="btn btn-success btn-small hover-scale">✏️</button>
            <button onclick="deleteMovie(${data.id})"
              class="btn btn-danger btn-small hover-scale">🗑️</button>
          </div>
        </div>`;

      filmList.prepend(div);
    } else {
      alert('Fehler beim Speichern');
    }
  });
}

// Film löschen
function deleteMovie(id) {
  if (!confirm('Wirklich löschen?')) return;

  fetch('delete_movie.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('movie-' + id).remove();
      showToast('Film gelöscht!');
    } else {
      alert('Fehler beim Löschen');
    }
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
      // Zähler smooth aktualisieren
      animateCounter('like-count-' + id, data.likes);
      animateCounter('neutral-count-' + id, data.neutral);
      animateCounter('dislike-count-' + id, data.dislikes);

      // Alle Buttons deaktivieren
      likeBtn.disabled = true;
      neutralBtn.disabled = true;
      dislikeBtn.disabled = true;

      // Alle Buttons optisch zurücksetzen
      likeBtn.classList.remove('active');
      neutralBtn.classList.remove('active');
      dislikeBtn.classList.remove('active');

      // Active-Button markieren
      if (type === 'like') {
        likeBtn.classList.add('active');
      } else if (type === 'neutral') {
        neutralBtn.classList.add('active');
      } else if (type === 'dislike') {
        dislikeBtn.classList.add('active');
      }

      showToast('Film wurde bewertet!');
    } else {
      alert(data.message || 'Fehler beim Bewerten');
    }
  });
}

// Counter Animation
function animateCounter(elementId, newValue) {
  const el = document.getElementById(elementId);
  if (!el) return;
  
  const oldValue = parseInt(el.textContent);
  const diff = newValue - oldValue;
  if (diff === 0) return;
  
  let current = oldValue;
  const step = diff > 0 ? 1 : -1;

  const interval = setInterval(() => {
    current += step;
    el.textContent = current;
    if (current === newValue) clearInterval(interval);
  }, 30);
}

// Tagify Setup
let editTagify;

document.addEventListener('DOMContentLoaded', async () => {
  // Tags laden
  try {
    const res = await fetch('get_tags.php');
    const tagList = await res.json();
    
    // Tag-Inputs initialisieren
    const addTagInput = document.getElementById('addModalTags');
    const editTagInput = document.getElementById('modalTags');
    
    if (addTagInput) {
      new Tagify(addTagInput, {
        whitelist: tagList.map(tag => tag.value),
        dropdown: {
          enabled: 0,
          classname: "tags-look",
          maxItems: 10
        }
      });
    }
    
    if (editTagInput) {
      editTagify = new Tagify(editTagInput, {
        whitelist: tagList.map(tag => tag.value),
        dropdown: {
          enabled: 0,
          classname: "tags-look", 
          maxItems: 10
        },
        originalInputValueFormat: valuesArr => valuesArr.map(tag => tag.value)
      });
    }
  } catch (error) {
    console.error('Fehler beim Laden der Tags:', error);
  }
});