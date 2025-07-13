<?php $title = 'Ticket #' . $ticket['TicketID']; include 'templates/header.php'; ?>
<div class="ticket-view">
    <div class="ticket-header">
        <div class="ticket-header-line">
            <div class="ticket-meta">
                <span class="team-badge" style="background-color: <?php echo htmlspecialchars($ticket['TeamColor']); ?>"><?php echo htmlspecialchars($ticket['TeamName']); ?></span>
                <span class="status-badge" style="background-color: <?php echo htmlspecialchars($ticket['StatusColor']); ?>"><?php echo htmlspecialchars($ticket['StatusName']); ?></span>
                <span class="priority-badge" style="background-color: <?php echo htmlspecialchars($ticket['PriorityColor']); ?>"><?php echo htmlspecialchars($ticket['PriorityName']); ?></span>
                <span>Erstellt am: <?php echo $ticket['CreatedAt']; ?></span>
                <span>von <?php echo htmlspecialchars($ticket['CreatedByName']); ?></span>
                <?php if ($ticket['Source']): ?><span>via <?php echo htmlspecialchars($ticket['Source']); ?></span><?php endif; ?>
            </div>
            <span class="ticket-id">Ticket-ID: <?php echo $ticket['TicketID']; ?></span>
        </div>
        <h1><?php echo htmlspecialchars($ticket['Title']); ?></h1>
    </div>

    <div class="ticket-content">
        <div class="ticket-sidebar">
            <h3>Kontakt</h3>
            <div class="contact-info">
                <p><strong><?php echo htmlspecialchars($ticket['ContactName']); ?></strong></p>
                <?php if ($ticket['ContactPhone']): ?>
                <p>Tel: <a href="tel:<?php echo htmlspecialchars($ticket['ContactPhone']); ?>"><?php echo htmlspecialchars($ticket['ContactPhone']); ?></a></p>
                <?php endif; ?>
                <?php if ($ticket['ContactEmail']): ?>
                <p>E-Mail: <a href="mailto:<?php echo htmlspecialchars($ticket['ContactEmail']); ?>"><?php echo htmlspecialchars($ticket['ContactEmail']); ?></a></p>
                <?php endif; ?>
            </div>

            <?php if ($facility_info || $location_info): ?>
            <h3>Organisation</h3>
            <div class="organization-info">
                <?php if ($facility_info): ?>
                <p><strong>Einrichtung:</strong><br><?php echo htmlspecialchars($facility_info['Facility']); ?></p>
                <?php endif; ?>
                <?php if ($location_info): ?>
                <p><strong>Standort:</strong><br><?php echo htmlspecialchars($location_info['Location']); ?></p>
                <p><?php echo htmlspecialchars($location_info['Street']); ?>, <?php echo htmlspecialchars($location_info['ZIP']); ?> <?php echo htmlspecialchars($location_info['Town']); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($related_person): ?>
            <h3>Weitere Tickets dieser Person</h3>
            <div class="related-tickets">
                <?php foreach ($related_person as $rel): ?>
                <p>
                    <span class="team-badge small" style="background-color: <?php echo htmlspecialchars($rel['TeamColor']); ?>">
                        <?php echo htmlspecialchars($rel['TeamName']); ?>
                    </span>
                    <a href="index.php?action=view_ticket&id=<?php echo $rel['TicketID']; ?>">
                        #<?php echo $rel['TicketID']; ?>: <?php echo htmlspecialchars(mb_strimwidth($rel['Title'],0,40,'...')); ?>
                    </a>
                    <small>(<?php echo $rel['CreatedAt']; ?>)</small>
                </p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($related_facility): ?>
            <h3>Weitere Tickets dieser Einrichtung</h3>
            <div class="related-tickets">
                <?php foreach ($related_facility as $rel): ?>
                <p>
                    <span class="team-badge small" style="background-color: <?php echo htmlspecialchars($rel['TeamColor']); ?>">
                        <?php echo htmlspecialchars($rel['TeamName']); ?>
                    </span>
                    <a href="index.php?action=view_ticket&id=<?php echo $rel['TicketID']; ?>">
                        #<?php echo $rel['TicketID']; ?>: <?php echo htmlspecialchars(mb_strimwidth($rel['Title'],0,40,'...')); ?>
                    </a>
                    <em>(<?php echo htmlspecialchars(explode(' ', trim($rel['ContactName']))[0]); ?>)</em>
                    <small>(<?php echo $rel['CreatedAt']; ?>)</small>
                </p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($related_location): ?>
            <h3>Weitere Tickets an diesem Standort</h3>
            <div class="related-tickets">
                <?php foreach ($related_location as $rel): ?>
                <p>
                    <span class="team-badge small" style="background-color: <?php echo htmlspecialchars($rel['TeamColor']); ?>">
                        <?php echo htmlspecialchars($rel['TeamName']); ?>
                    </span>
                    <a href="index.php?action=view_ticket&id=<?php echo $rel['TicketID']; ?>">
                        #<?php echo $rel['TicketID']; ?>: <?php echo htmlspecialchars(mb_strimwidth($rel['Title'],0,30,'...')); ?>
                    </a>
                    <em>(<?php echo htmlspecialchars(explode(' ', trim($rel['ContactName']))[0]); ?>)</em>
                    <small>(<?php echo $rel['CreatedAt']; ?>)</small>
                </p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h3>Zugewiesen an</h3>
            <div class="assignees">
                <?php if ($assignees): ?>
                <div>
                    <?php foreach ($assignees as $as): ?>
                    <p><?php echo htmlspecialchars($as['AgentName']); ?> (<?php echo $as['AssignedAt']; ?>)</p>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Noch keinem Agenten zugewiesen</p>
                <?php endif; ?>
            </div>

            <h3>Anhänge</h3>
            <div class="attachments">
                <?php if ($attachments): ?>
                <?php foreach ($attachments as $a): ?>
                <div class="attachment-item">
                    <div class="attachment-preview">
                        <?php $ext = strtolower(pathinfo($a['FileName'], PATHINFO_EXTENSION)); ?>
                        <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                            <img src="../static/uploads/<?php echo $a['StoragePath']; ?>" alt="<?php echo htmlspecialchars($a['FileName']); ?>">
                        <?php elseif ($ext == 'pdf'): ?>
                            📄
                        <?php elseif (in_array($ext, ['doc','docx'])): ?>
                            📝
                        <?php else: ?>
                            📎
                        <?php endif; ?>
                    </div>
                    <div class="attachment-info">
                        <a href="../static/uploads/<?php echo $a['StoragePath']; ?>" target="_blank" class="attachment-name"><?php echo htmlspecialchars($a['FileName']); ?></a>
                        <div class="attachment-meta"><?php echo $a['FormattedUploadedAt']; ?> • <?php echo round($a['FileSize']/1024,1); ?> KB</div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>Keine Anhänge vorhanden</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="ticket-details">
            <h3>Beschreibung</h3>
            <div class="description-bubble">
                <div class="bubble-content"><?php echo nl2br(htmlspecialchars($ticket['Description'])); ?></div>
            </div>

            <h3>Verlauf</h3>
            <div class="updates-list">
                <?php foreach ($updates as $u): ?>
                <div class="update-bubble <?php if ($u['IsSolution']) echo 'solution'; ?>">
                    <div class="bubble-content"><?php echo nl2br(htmlspecialchars($u['UpdateText'])); ?></div>
                    <div class="bubble-meta">
                        <span class="bubble-author"><?php echo htmlspecialchars($u['UpdatedByName']); ?></span>
                        <span class="bubble-time"><?php echo $u['FormattedUpdatedAt']; ?></span>
                        <?php if ($u['IsSolution']): ?><span class="solution-badge">Lösung</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($ticket['StatusName'] != 'Gelöst'): ?>
            <div class="update-form">
                <form method="POST" action="index.php?action=update_ticket&id=<?php echo $ticket['TicketID']; ?>" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status_id">Status ändern:</label>
                            <select id="status_id" name="status_id">
                                <option value="">-- Unverändert --</option>
                                <?php foreach ($statuses as $s): if ($s['StatusName'] != 'Neu' && $s['StatusName'] != 'Gelöst'): ?>
                                <option value="<?php echo $s['StatusID']; ?>" <?php if ($s['StatusID'] == $ticket['StatusID']) echo 'selected'; ?>><?php echo htmlspecialchars($s['StatusName']); ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority_id">Priorität ändern:</label>
                            <select id="priority_id" name="priority_id">
                                <option value="">-- Unverändert --</option>
                                <?php foreach ($priorities as $p): ?>
                                <option value="<?php echo $p['PriorityID']; ?>" <?php if ($p['PriorityID'] == $ticket['PriorityID']) echo 'selected'; ?>><?php echo htmlspecialchars($p['PriorityName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="assign_agent">Agent zuweisen:</label>
                        <select id="assign_agent" name="assign_agent">
                            <option value="">-- Niemanden zuweisen --</option>
                            <?php foreach ($agents as $ag): ?>
                            <option value="<?php echo $ag['AgentID']; ?>"><?php echo htmlspecialchars($ag['AgentName'] . ' (' . $ag['TeamName'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_text">Kommentar:</label>
                        <textarea id="update_text" name="update_text" rows="4"></textarea>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_solution" name="is_solution">
                        <label for="is_solution">Als Lösung markieren. Lösung ist Lösung! Das Ticket kann danach nicht mehr bearbeitet werden.</label>                    
                    </div>
                    <div class="form-group">
                        <label for="attachment">Anhang hinzufügen:</label>
                        <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                        <small class="form-hint">Erlaubte Dateiformate: jpg, jpeg, png, gif, pdf, doc, docx, txt, zip</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-button">Aktualisieren</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="ticket-solved">
                <h3>Ticket gelöst</h3>
                <p>Kann daher nicht mehr bearbeitet werden.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isSolution = document.getElementById('is_solution');
    const statusSelect = document.getElementById('status_id');
    if (isSolution && statusSelect) {
        isSolution.addEventListener('change', function() {
            if (isSolution.checked) {
                statusSelect.value = '3';
                statusSelect.disabled = true;
            } else {
                statusSelect.disabled = false;
            }
        });

        const currentStatus = <?php echo $ticket['StatusID']; ?>;
        Array.from(statusSelect.options).forEach(function(opt) {
            const val = parseInt(opt.value);
            if ((val === 1 && currentStatus > 1) || val === 3) {
                opt.remove();
            }
        });
    }
});
</script>
<?php include 'templates/footer.php'; ?>
