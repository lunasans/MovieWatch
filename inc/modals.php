<!-- Modal: Film bearbeiten -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="bi bi-pencil-square"></i>
                Film bearbeiten
            </h2>
            <button class="modal-close" onclick="closeModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-film"></i>
                    Titel
                </label>
                <input type="text" id="modalTitle" class="form-input" placeholder="Film-Titel eingeben">
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-eye"></i>
                    Anzahl Sichtungen
                </label>
                <input type="number" id="modalCount" class="form-input" min="0" placeholder="0">
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-calendar-date"></i>
                    Letztes Datum
                </label>
                <input type="date" id="modalDate" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-tags"></i>
                    Tags
                </label>
                <input type="text" id="modalTags" class="form-input" placeholder="Tags durch Komma getrennt">
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">
                <i class="bi bi-x-circle"></i>
                Abbrechen
            </button>
            <button class="btn btn-success" onclick="saveModalData()">
                <i class="bi bi-check-circle"></i>
                Speichern
            </button>
        </div>
    </div>
</div>

<!-- Modal: Film hinzufügen -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="bi bi-plus-circle"></i>
                Neuen Film hinzufügen
            </h2>
            <button class="modal-close" onclick="closeAddModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-film"></i>
                    Titel *
                </label>
                <input type="text" id="addModalTitle" class="form-input" placeholder="Film-Titel eingeben" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-tags"></i>
                    Tags
                </label>
                <input type="text" id="addModalTags" class="form-input" placeholder="z.B. Action, Drama, Komödie">
                <small style="color: var(--clr-text-muted); font-size: 0.8rem; margin-top: var(--spacing-xs); display: block;">
                    Tags durch Komma getrennt eingeben
                </small>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeAddModal()">
                <i class="bi bi-x-circle"></i>
                Abbrechen
            </button>
            <button class="btn btn-primary" onclick="saveAddModal()">
                <i class="bi bi-plus-circle"></i>
                Hinzufügen
            </button>
        </div>
    </div>
</div>

<!-- Modal: Film-Details (für erweiterte Ansicht) -->
<div class="modal" id="detailModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="bi bi-info-circle"></i>
                Film-Details
            </h2>
            <button class="modal-close" onclick="closeDetailModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="modal-body" id="detailModalContent">
            <!-- Wird dynamisch gefüllt -->
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDetailModal()">
                <i class="bi bi-x-circle"></i>
                Schließen
            </button>
        </div>
    </div>
</div>