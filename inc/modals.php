<!-- Modal: Bearbeiten -->
<div class="modal" id="editModal">
  <div class="modal-background" onclick="closeModal()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Film bearbeiten</p>
      <button class="delete" aria-label="close" onclick="closeModal()"></button>
    </header>
    <section class="modal-card-body">
      <div class="field">
        <label class="label">Titel</label>
        <div class="control">
          <input type="text" id="modalTitle" class="input">
        </div>
      </div>

      <div class="field">
        <label class="label">Anzahl Sichtungen</label>
        <div class="control">
          <input type="number" id="modalCount" class="input" min="0">
        </div>
      </div>

      <div class="field">
        <label class="label">Letztes Datum</label>
        <div class="control">
          <input type="date" id="modalDate" class="input">
        </div>
      </div>

      <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Tags</label>
        <input type="text" id="modalTags" class="border p-2 w-full rounded bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
      </div>
    </section>
    <footer class="modal-card-foot">
      <button class="button is-success" onclick="saveModalData()">Speichern</button>
      <button class="button" onclick="closeModal()">Abbrechen</button>
    </footer>
  </div>
</div>


<!-- Modal: Hinzufügen -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center" style="display:none;">
  <div id="addModalContent" class="bg-white dark:bg-gray-800 p-6 rounded shadow max-w-sm w-full transform transition duration-200">
    <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Neuen Film hinzufügen</h2>
    <div class="space-y-3">
      
      <!-- Titel-Eingabe -->
      <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Titel</label>
        <input type="text" id="addModalTitle"
               class="border p-2 w-full rounded bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
      </div>

      <!-- 🔽 Hier kommt das neue Feld für Tags -->
      <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Tags</label>
        <input type="text" id="addModalTags" name="tags"
               class="border p-2 w-full rounded bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
      </div>

    </div>

    <!-- Buttons -->
    <div class="flex justify-end space-x-2 mt-4">
      <button onclick="closeAddModal()"
              class="bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-1 rounded hover:bg-gray-400 dark:hover:bg-gray-500 transition">
        Abbrechen
      </button>
      <button onclick="saveAddModal()"
              class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
        Hinzufügen
      </button>
    </div>
  </div>
</div>

