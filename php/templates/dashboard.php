<?php
$title = 'Dashboard - IFAK Ticketsystem';
include 'templates/header.php';
if (!isset($current_status_filter)) $current_status_filter = 'open';
if (!isset($search_term)) $search_term = '';
if (!isset($person_term)) $person_term = '';
if (!isset($facility_term)) $facility_term = '';
if (!isset($current_agent_filter)) $current_agent_filter = '';
if (!isset($new_ticket_count)) $new_ticket_count = 0;
if (!isset($open_ticket_count)) $open_ticket_count = 0;
?>
<div class="dashboard">
    <div class="dashboard-header">
        <h1>
            <?php if (!empty($search_term)): ?>
                Tickets mit Suchbegriff "<?php echo htmlspecialchars($search_term); ?>" (<?php echo $new_ticket_count; ?>|<?php echo $open_ticket_count; ?>)
            <?php else: ?>
                Ticket-Übersicht (<?php echo $new_ticket_count; ?>|<?php echo $open_ticket_count; ?>)
            <?php endif; ?>
        </h1>
        <div class="dashboard-filters">
            <div class="filter-group">
                <label>Status:</label>
                <select id="status-filter" onchange="applyFilters()">
                    <option value="open" <?php if ($current_status_filter == 'open') echo 'selected'; ?>>Alle offenen</option>
                    <option value="all" <?php if ($current_status_filter == 'all') echo 'selected'; ?>>Alle</option>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s['StatusName']; ?>" <?php if ($current_status_filter == $s['StatusName']) echo 'selected'; ?>><?php echo htmlspecialchars($s['StatusName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Agent:</label>
                <select id="agent-filter" onchange="applyFilters()">
                    <option value="" <?php if (!$current_agent_filter) echo 'selected'; ?>>Alle</option>
                    <?php foreach ($agents as $ag): ?>
                    <option value="<?php echo $ag['AgentID']; ?>" <?php if ($current_agent_filter == $ag['AgentID']) echo 'selected'; ?>><?php echo htmlspecialchars($ag['AgentName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group search-group">
                <label>Titel, ID, Person oder Einrichtung:</label>
                <div class="search-bar">
                    <input type="text" id="search-input" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Titel, ID, Person oder Einrichtung">
                    <input type="text" id="person-input" value="<?php echo htmlspecialchars($person_term); ?>" placeholder="Person">
                    <input type="text" id="facility-input" value="<?php echo htmlspecialchars($facility_term); ?>" placeholder="Einrichtung">
                    <button onclick="applyFilters()">Suchen</button>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Team</th>
                    <th>Status</th>
                    <th>Priorität</th>
                    <th>Titel</th>
                    <th>Kontakt</th>
                    <th>Zugewiesen an</th>
                    <th>Erstellt am</th>
                    <th>Alter (Tage)</th>
                    <th>Erstellt von</th>
                    <th>Quelle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr class="ticket-row<?php if (!empty($ticket['Stale']) && $ticket['StatusName'] != 'Gelöst') echo ' old-ticket'; if (!empty($ticket['Delayed'])) echo ' delayed-ticket'; ?>" data-href="index.php?action=view_ticket&id=<?php echo $ticket['TicketID']; ?>" onclick="window.location='index.php?action=view_ticket&id=<?php echo $ticket['TicketID']; ?>'">
                    <td><?php echo $ticket['TicketID']; ?></td>
                    <td><span class="team-badge" style="background-color: <?php echo htmlspecialchars($ticket['TeamColor']); ?>;"><?php echo htmlspecialchars($ticket['TeamName']); ?></span></td>
                    <td><span class="status-badge" style="background-color: <?php echo htmlspecialchars($ticket['StatusColor']); ?>;"><?php echo htmlspecialchars($ticket['StatusName']); ?></span></td>
                    <td><span class="priority-badge" style="background-color: <?php echo htmlspecialchars($ticket['PriorityColor']); ?>;"><?php echo htmlspecialchars($ticket['PriorityName']); ?></span></td>
                    <td><?php echo htmlspecialchars($ticket['Title']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['ContactName']); ?></td>
                    <td>
                        <?php if (!empty($ticket['AssignedAgents'])): ?>
                            <?php echo htmlspecialchars($ticket['AssignedAgents']); ?>
                        <?php elseif ($ticket['StatusName'] === 'Gelöst'): ?>
                            &ndash;
                        <?php else: ?>
                            <span class="unassigned-badge<?php if (!empty($ticket['Delayed'])) echo ' overdue'; ?>">Offen: <?php echo $ticket['AgeDays'] > 0 ? $ticket['AgeDays'] . 'd' : $ticket['AgeHours'] . 'h'; ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $ticket['CreatedAt']; ?></td>
                    <td><?php echo $ticket['AgeDays']; ?></td>
                    <td><?php echo htmlspecialchars($ticket['CreatedByName']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['Source']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const agent = document.getElementById('agent-filter').value;
    const search = document.getElementById('search-input').value;
    const person = document.getElementById('person-input').value;
    const facility = document.getElementById('facility-input').value;

    const url = new URL(window.location);
    url.searchParams.delete('team');
    url.searchParams.set('status', status);
    if (agent) { url.searchParams.set('agent', agent); } else { url.searchParams.delete('agent'); }
    if (search) { url.searchParams.set('q', search); } else { url.searchParams.delete('q'); }
    if (person) { url.searchParams.set('person', person); } else { url.searchParams.delete('person'); }
    if (facility) { url.searchParams.set('facility', facility); } else { url.searchParams.delete('facility'); }
    window.location = url;
}
['search-input','person-input','facility-input'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    }
});
</script>
<?php include 'templates/footer.php'; ?>
