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
        <input type="text" id="modalTitle" class="input">
      </div>

      <div class="field">
        <label class="label">Anzahl Sichtungen</label>
        <input type="number" id="modalCount" class="input" min="0">
      </div>

      <div class="field">
        <label class="label">Letztes Datum</label>
        <input type="date" id="modalDate" class="input">
      </div>

      <div class="field">
        <label class="label">Tags</label>
        <input type="text" id="modalTags" class="input">
      </div>
    </section>
    <footer class="modal-card-foot">
      <button class="btn btn-success" onclick="saveModalData()">Speichern</button>
      <button class="btn btn-secondary" onclick="closeModal()">Abbrechen</button>
    </footer>
  </div>
</div>

<!-- Modal: Hinzufügen -->
<div id="addModal" class="modal">
  <div class="modal-background" onclick="closeAddModal()"></div>
  <div id="addModalContent" class="modal-card">
    <header class="modal-card-head">
      <h2 class="modal-card-title">Neuen Film hinzufügen</h2>
      <button class="delete" aria-label="close" onclick="closeAddModal()"></button>
    </header>
    <section class="modal-card-body">
      <!-- Titel-Eingabe -->
      <div class="field">
        <label class="label">Titel</label>
        <input type="text" id="addModalTitle" class="input">
      </div>

      <!-- Tags-Eingabe -->
      <div class="field">
        <label class="label">Tags</label>
        <input type="text" id="addModalTags" name="tags" class="input">
      </div>
    </section>
    <footer class="modal-card-foot">
      <button onclick="saveAddModal()" class="btn btn-primary">Hinzufügen</button>
      <button onclick="closeAddModal()" class="btn btn-secondary">Abbrechen</button>
    </footer>
  </div>
</div>