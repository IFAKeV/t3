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
                <span>Offen seit: <?php echo $ticket['AgeDays']; ?> Tage</span>
                <span>Letzte Aktualisierung: <?php echo $last_update_at; ?></span>
            </div>
            <span class="ticket-id">Ticket-ID: <?php echo $ticket['TicketID']; ?></span>
        </div>
        <?php $prev_id = $ticket['TicketID'] - 1; $next_id = $ticket['TicketID'] + 1; ?>
        <div class="ticket-nav">
            <a href="index.php?action=view_ticket&amp;id=<?php echo $prev_id; ?>" class="ticket-nav-link">&lt;</a>
            <form method="GET" action="index.php" class="ticket-nav-form">
                <input type="hidden" name="action" value="view_ticket">
                <input type="number" name="id" value="<?php echo $ticket['TicketID']; ?>" class="ticket-nav-input">
            </form>
            <a href="index.php?action=view_ticket&amp;id=<?php echo $next_id; ?>" class="ticket-nav-link">&gt;</a>
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
                            <img src="<?php echo $base_url; ?>/static/uploads/<?php echo $a['StoragePath']; ?>" alt="<?php echo htmlspecialchars($a['FileName']); ?>">
                        <?php elseif ($ext == 'pdf'): ?>
                            📄
                        <?php elseif (in_array($ext, ['doc','docx'])): ?>
                            📝
                        <?php elseif ($ext == 'md'): ?>
                            <a href="index.php?action=view_markdown&amp;file=<?php echo urlencode($a['StoragePath']); ?>" class="md-attachment">📘</a>
                        <?php else: ?>
                            📎
                        <?php endif; ?>
                    </div>
                    <div class="attachment-info">
                        <a href="<?php echo $base_url; ?>/static/uploads/<?php echo $a['StoragePath']; ?>" target="_blank" class="attachment-name"><?php echo htmlspecialchars($a['FileName']); ?></a>
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
                <div class="bubble-content"><?php echo linkify_urls($ticket['Description']); ?></div>
            </div>

            <h3>Verlauf</h3>
            <div class="updates-list">
                <?php foreach ($updates as $u): ?>
                <div class="update-bubble <?php if ($u['IsSolution']) echo 'solution'; ?>">
                    <div class="bubble-content"><?php echo linkify_urls($u['UpdateText']); ?></div>
                    <div class="bubble-meta">
                        <span class="bubble-author"><?php echo htmlspecialchars($u['UpdatedByName']); ?></span>
                        <span class="bubble-time"><?php echo $u['FormattedUpdatedAt']; ?></span>
                        <?php if ($u['IsSolution']): ?><span class="solution-badge">Lösung</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($ticket['StatusName'] != 'Gelöst'): ?>
            <?php
                $new_status_id = null;
                $solved_status_id = null;
                $solved_status_label = null;
                foreach ($statuses as $status_option) {
                    if ($status_option['StatusName'] === 'Neu') {
                        $new_status_id = (int) $status_option['StatusID'];
                    }
                    if ($status_option['StatusName'] === 'Gelöst') {
                        $solved_status_id = (int) $status_option['StatusID'];
                        $solved_status_label = $status_option['StatusName'];
                    }
                }
            ?>
            <div class="update-form">
                <form method="POST" action="index.php?action=update_ticket&id=<?php echo $ticket['TicketID']; ?>" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status_id">Status ändern:</label>
                            <select
                                id="status_id"
                                name="status_id"
                                data-status-new="<?php echo $new_status_id !== null ? htmlspecialchars((string) $new_status_id) : ''; ?>"
                                data-status-solved="<?php echo $solved_status_id !== null ? htmlspecialchars((string) $solved_status_id) : ''; ?>"
                                data-status-solved-label="<?php echo $solved_status_label !== null ? htmlspecialchars($solved_status_label) : ''; ?>"
                                data-current-status="<?php echo htmlspecialchars((string) $ticket['StatusID']); ?>"
                                <?php if ($ticket['StatusName'] == 'Neu') echo 'required'; ?>>
                                <?php if ($ticket['StatusName'] != 'Neu'): ?>
                                <option value="">-- Unverändert --</option>
                                <?php endif; ?>
                                <?php foreach ($statuses as $s): if ($s['StatusName'] != 'Neu' && $s['StatusName'] != 'Gelöst'): ?>
                                <option value="<?php echo $s['StatusID']; ?>" <?php if (($ticket['StatusName'] == 'Neu' && $s['StatusName'] == 'In Arbeit') || ($s['StatusID'] == $ticket['StatusID'])) echo 'selected'; ?>><?php echo htmlspecialchars($s['StatusName']); ?></option>
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
                        <div class="form-group">
                            <label for="assign_agent">Agent zuweisen:</label>
                            <?php $current_assignee_id = $assignees[0]['AgentID'] ?? null; ?>
                            <select id="assign_agent" name="assign_agent">
                                <option value="">-- Niemanden zuweisen --</option>
                                <?php foreach ($agents as $ag):
                                    if ($ag['AgentID'] == $current_assignee_id) { continue; }
                                    $label = ($ag['AgentID'] == $agent['AgentID']) ? 'Mich selbst' : $ag['AgentName'];
                                    $label .= ' (' . $ag['TeamName'] . ')';
                                ?>
                                <option value="<?php echo $ag['AgentID']; ?>"><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
    if (!isSolution || !statusSelect) {
        return;
    }

    const parseId = function(value) {
        const parsed = parseInt(value, 10);
        return Number.isNaN(parsed) ? null : parsed;
    };

    const newStatusId = parseId(statusSelect.dataset.statusNew);
    const solvedStatusId = parseId(statusSelect.dataset.statusSolved);
    const solvedStatusLabel = statusSelect.dataset.statusSolvedLabel || '';
    const currentStatus = parseId(statusSelect.dataset.currentStatus);

    const nonSelectableIds = [];
    if (solvedStatusId !== null) {
        nonSelectableIds.push(solvedStatusId);
    }
    if (newStatusId !== null && currentStatus !== null && currentStatus !== newStatusId) {
        nonSelectableIds.push(newStatusId);
    }

    const solvedIdString = solvedStatusId !== null ? String(solvedStatusId) : null;
    let solvedOption = null;

    Array.from(statusSelect.options).forEach(function(opt) {
        const optionValue = parseId(opt.value);
        if (optionValue !== null && nonSelectableIds.includes(optionValue)) {
            if (solvedIdString !== null && optionValue === solvedStatusId) {
                solvedOption = opt;
            }
            opt.remove();
        }
    });

    if (!solvedOption && solvedIdString !== null && solvedStatusLabel) {
        solvedOption = document.createElement('option');
        solvedOption.value = solvedIdString;
        solvedOption.textContent = solvedStatusLabel;
    }

    let previousStatusValue = statusSelect.value;

    isSolution.addEventListener('change', function() {
        if (isSolution.checked) {
            previousStatusValue = statusSelect.value;
            if (solvedOption && solvedIdString !== null && !statusSelect.querySelector('option[value="' + solvedIdString + '"]')) {
                statusSelect.appendChild(solvedOption);
            }
            if (solvedIdString !== null) {
                statusSelect.value = solvedIdString;
            }
            statusSelect.disabled = true;
        } else {
            statusSelect.disabled = false;
            if (solvedOption && statusSelect.contains(solvedOption)) {
                solvedOption.remove();
            }
            if (previousStatusValue && statusSelect.querySelector('option[value="' + previousStatusValue + '"]')) {
                statusSelect.value = previousStatusValue;
            } else {
                const fallbackOption = statusSelect.querySelector('option[value=""]') || statusSelect.querySelector('option');
                statusSelect.value = fallbackOption ? fallbackOption.value : '';
            }
        }
    });
});
</script>
<?php include 'templates/footer.php'; ?>
