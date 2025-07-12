<?php $title = 'Neues Ticket - IFAK Ticketsystem'; include 'templates/header.php'; ?>
<form method="POST" enctype="multipart/form-data" action="index.php?action=new_ticket">
    <div class="form-group">
        <label for="title">Titel:</label>
        <input type="text" name="title" id="title" required>
    </div>
    <div class="form-group">
        <label for="description">Beschreibung:</label>
        <textarea name="description" id="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="priority">Priorität:</label>
        <select name="priority_id" id="priority">
            <?php foreach ($priorities as $p): ?>
            <option value="<?php echo $p['PriorityID']; ?>"><?php echo htmlspecialchars($p['PriorityName']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="team">Team:</label>
        <select name="team_id" id="team">
            <?php foreach ($teams as $t): ?>
            <option value="<?php echo $t['TeamID']; ?>" <?php if ($t['TeamID']==$agent['TeamID']) echo 'selected'; ?>><?php echo htmlspecialchars($t['TeamName']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="contact">Kontaktname:</label>
        <input type="text" name="contact_name" id="contact">
    </div>
    <div class="form-group">
        <label for="attachment">Anhang:</label>
        <input type="file" name="attachment" id="attachment">
    </div>
    <button type="submit">Ticket erstellen</button>
</form>
<?php include 'templates/footer.php'; ?>
