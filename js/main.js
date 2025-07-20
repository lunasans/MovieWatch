    // Beim Laden prüfen
if (localStorage.getItem('dark-mode') === 'true') {
  document.documentElement.classList.add('dark');
  document.getElementById('darkModeSwitch').checked = true;
}

// Event-Listener
document.getElementById('darkModeSwitch').addEventListener('change', function() {
  if (this.checked) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('dark-mode', 'true');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('dark-mode', 'false');
  }
});

    function searchMovies(query) {
      fetch('search.php?q=' + encodeURIComponent(query))
        .then(response => response.text())
        .then(html => {
          document.getElementById('film-list').innerHTML = html;

        });
    }

    let currentMovieId = null;

    function openModal(id, title, count, date) {
      currentMovieId = id;
      document.getElementById('modalTitle').value = title;
      document.getElementById('modalCount').value = count;
      if (date) {
      const d = new Date(date);
      const iso = d.toISOString().split('T')[0];
      document.getElementById('modalDate').value = iso;
      } else {
      document.getElementById('modalDate').value = '';
      }
      const modal = document.getElementById('editModal');
      const content = document.getElementById('modalContent');
      modal.style.display = 'flex';
      content.classList.add('fade-enter');
      requestAnimationFrame(() => {
        content.classList.add('fade-enter-active');
      });
    }

    function closeModal() {
      const modal = document.getElementById('editModal');
      const content = document.getElementById('modalContent');
      content.classList.remove('fade-enter', 'fade-enter-active');
      modal.style.display = 'none';
    }

    function openModal(id, title, count, date, tags = []) {
      currentMovieId = id;
      document.getElementById('modalTitle').value = title;
      document.getElementById('modalCount').value = count;
      document.getElementById('modalDate').value = date ? date.split('T')[0] : '';

      if (window.tagifyEdit) {
        tagifyEdit.removeAllTags();
        tagifyEdit.addTags(tags);
      }

  document.getElementById('editModal').style.display = 'flex';
}

    function closeAddModal() {
      const modal = document.getElementById('addModal');
      const content = document.getElementById('addModalContent');
      content.classList.remove('fade-enter', 'fade-enter-active');
      modal.style.display = 'none';
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
        if (!toast) return;

        toast.textContent = message;
        toast.style.display = 'block';

      setTimeout(() => {
        toast.style.display = 'none';
      }, 3000);
    } 


   function saveModalData() {
  const title = document.getElementById('modalTitle').value.trim();
  const count = parseInt(document.getElementById('modalCount').value);
  const date = document.getElementById('modalDate').value;
  const tags = editTagify.value.map(tag => tag.value); // Nur Werte extrahieren

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

      const div = document.createElement('div');
      div.id = 'movie-' + data.id;
      div.className = "bg-white dark:bg-gray-800 p-4 rounded shadow hover:shadow-lg hover:scale-[1.02] transform transition duration-200 flex justify-between items-center";

      div.innerHTML = `
        <div>
          <h2 id="title-${data.id}" class="text-lg font-semibold text-gray-800 dark:text-gray-100">${title}</h2>
          <p id="info-${data.id}" class="text-gray-600 text-sm dark:text-gray-300">0x gesehen</p>
          <p id="tags-${data.id}" class="text-sm text-blue-500 mt-1">Tags: ${tags}</p>
        </div>
        <div class="flex space-x-2">
          <button onclick="openModal(${data.id}, '${title.replace(/'/g,"\\'")}', 0, null)"
            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transform hover:scale-105 transition">
            ✏️
          </button>
          <button onclick="deleteMovie(${data.id})"
            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transform hover:scale-105 transition">
            🗑️
          </button>
        </div>`;

      document.querySelector('.lg\\:col-span-2').prepend(div);
    } else {
      alert('Fehler beim Speichern');
    }
  });
}


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
      likeBtn.classList.remove('scale-110', 'text-green-700');
      neutralBtn.classList.remove('scale-110', 'text-gray-700');
      dislikeBtn.classList.remove('scale-110', 'text-red-700');

      // Active-Button markieren
      if (type === 'like') {
        likeBtn.classList.add('scale-110', 'text-green-700');
      } else if (type === 'neutral') {
        neutralBtn.classList.add('scale-110', 'text-gray-700');
      } else if (type === 'dislike') {
        dislikeBtn.classList.add('scale-110', 'text-red-700');
      }

      showToast('Film wurde Bewertet!');
    } else {
      alert(data.message || 'Fehler beim Bewerten');
    }
  });
}

function animateCounter(elementId, newValue) {
  const el = document.getElementById(elementId);
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

document.addEventListener('DOMContentLoaded', async () => {
  const tagInputs = [document.getElementById('addModalTags'), document.getElementById('modalTags')].filter(Boolean);

  const res = await fetch('get_tags.php');
  const tagList = await res.json(); // erwartet: [{value: 'Action'}, {value: 'Drama'}, ...]

  tagInputs.forEach(input => {
    new Tagify(input, {
      whitelist: tagList.map(tag => tag.value),
      dropdown: {
        enabled: 0,
        classname: "tags-look",
        maxItems: 10
      }
    });
  });
});

let editTagify;

function openModal(id, title, count, date, tags = []) {
  currentMovieId = id;
  document.getElementById("modalTitle").value = title;
  document.getElementById("modalCount").value = count;
  document.getElementById("modalDate").value = date || '';

  const tagInput = document.getElementById("modalTags");
  tagInput.value = '';
  if (editTagify) editTagify.destroy();

  editTagify = new Tagify(tagInput, {
    whitelist: [],
    enforceWhitelist: false,
    originalInputValueFormat: valuesArr => valuesArr.map(tag => tag.value)
  });

  // Falls Tags vorhanden: setzen
  if (tags && Array.isArray(tags)) {
    editTagify.addTags(tags);
  }

  document.getElementById("editModal").classList.add("is-active");
}

function closeModal() {
  document.getElementById("editModal").classList.remove("is-active");
}
