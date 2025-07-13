<?php $title = 'Neues Ticket - IFAK Ticketsystem'; include 'templates/header.php'; ?>
<div class="ticket-form">
    <h1>Neues Ticket erstellen</h1>
    <form method="POST" enctype="multipart/form-data" action="index.php?action=new_ticket">
        <div class="form-row">
            <div class="form-group">
                <label for="title">Titel*:</label>
                <input type="text" name="title" id="title" required placeholder="Kurze Beschreibung des Problems">
            </div>
            <div class="form-group">
                <label for="priority_id">Priorität*:</label>
                <select name="priority_id" id="priority_id" required>
                    <?php foreach ($priorities as $p): ?>
                    <option value="<?php echo $p['PriorityID']; ?>"><?php echo htmlspecialchars($p['PriorityName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="team_id">Team*:</label>
                <select name="team_id" id="team_id" required>
                    <?php foreach ($teams as $t): ?>
                    <option value="<?php echo $t['TeamID']; ?>" <?php if ($t['TeamID']==$agent['TeamID']) echo 'selected'; ?>><?php echo htmlspecialchars($t['TeamName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="assigned_agent">Zuweisen an:</label>
                <select name="assigned_agent" id="assigned_agent">
                    <option value="">-- Nicht zuweisen --</option>
                    <?php foreach ($agents as $ag): ?>
                    <option value="<?php echo $ag['AgentID']; ?>"><?php echo htmlspecialchars($ag['AgentName'] . ' (' . $ag['TeamName'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Beschreibung*:</label>
            <textarea name="description" id="description" rows="6" required placeholder="Detaillierte Beschreibung des Problems..."></textarea>
        </div>
        <h3>Kontakt</h3>
        <div class="form-group">
            <label for="contact_search">Kontakt suchen:</label>
            <input type="text" id="contact_search" placeholder="Name des Mitarbeiters eingeben...">
            <small>Mindestens 2 Zeichen eingeben für Suche im Adressbuch</small>
        </div>
        <div id="contact_details" class="contact-details" style="display:none;">
            <h4>Ausgewählter Kontakt:</h4>
            <div id="selected_contact_info"></div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="contact_name">Name*:</label>
                <input type="text" name="contact_name" id="contact_name" required>
                <input type="hidden" name="contact_employee_id" id="contact_employee_id">
                <input type="hidden" name="facility_id" id="facility_id">
                <input type="hidden" name="location_id" id="location_id">
                <input type="hidden" name="department_id" id="department_id">
            </div>
            <div class="form-group">
                <label for="contact_phone">Telefon:</label>
                <input type="tel" name="contact_phone" id="contact_phone">
            </div>
        </div>
        <div class="form-group">
            <label for="contact_email">E-Mail:</label>
            <input type="email" name="contact_email" id="contact_email">
        </div>
        <div class="form-group">
            <label for="source">Ticketquelle:</label>
            <select name="source" id="source">
                <option value="Mail">Mail</option>
                <option value="Anruf">Anruf</option>
                <option value="Flur">Flur</option>
            </select>
        </div>
        <div class="form-group">
            <label for="attachment">Anhang:</label>
            <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
            <small class="form-hint">Erlaubte Dateiformate: jpg, jpeg, png, gif, pdf, doc, docx, txt, zip</small>
        </div>
        <div class="form-actions">
            <button type="submit" class="submit-button">Ticket erstellen</button>
        </div>
    </form>
</div>
<?php include 'templates/footer.php'; ?>
