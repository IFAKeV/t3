<?php
$title = 'Dashboard - IFAK Ticketsystem';
include 'templates/header.php';
if (!isset($current_team_filter)) $current_team_filter = 'mine';
if (!isset($current_status_filter)) $current_status_filter = 'open';
if (!isset($search_term)) $search_term = '';
if (!isset($current_agent_filter)) $current_agent_filter = '';
?>
<div class="dashboard">
    <div class="dashboard-header">
        <h1>Ticket-Übersicht</h1>
        <div class="dashboard-filters">
            <div class="filter-group">
                <label>Team:</label>
                <select id="team-filter" onchange="applyFilters()">
                    <option value="mine" <?php if ($current_team_filter == 'mine') echo 'selected'; ?>>Meine Tickets</option>
                    <option value="my_team" <?php if ($current_team_filter == 'my_team') echo 'selected'; ?>>Mein Team (<?php echo htmlspecialchars($agent['TeamName']); ?>)</option>
                    <option value="all" <?php if ($current_team_filter == 'all') echo 'selected'; ?>>Alle Teams</option>
                    <?php foreach ($teams as $t): ?>
                    <option value="<?php echo $t['TeamID']; ?>" <?php if ($current_team_filter == $t['TeamID']) echo 'selected'; ?>><?php echo htmlspecialchars($t['TeamName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <label>Suche:</label>
                <div class="search-bar">
                    <input type="text" id="search-input" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Titel oder ID">
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
                <tr class="ticket-row" data-href="index.php?action=view_ticket&id=<?php echo $ticket['TicketID']; ?>" onclick="window.location='index.php?action=view_ticket&id=<?php echo $ticket['TicketID']; ?>'">
                    <td><?php echo $ticket['TicketID']; ?></td>
                    <td><span class="team-badge" style="background-color: <?php echo htmlspecialchars($ticket['TeamColor']); ?>;"><?php echo htmlspecialchars($ticket['TeamName']); ?></span></td>
                    <td><span class="status-badge" style="background-color: <?php echo htmlspecialchars($ticket['StatusColor']); ?>;"><?php echo htmlspecialchars($ticket['StatusName']); ?></span></td>
                    <td><span class="priority-badge" style="background-color: <?php echo htmlspecialchars($ticket['PriorityColor']); ?>;"><?php echo htmlspecialchars($ticket['PriorityName']); ?></span></td>
                    <td><?php echo htmlspecialchars($ticket['Title']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['ContactName']); ?></td>
                    <td>
                        <?php if (!empty($ticket['AssignedAgents'])): ?>
                            <?php echo htmlspecialchars($ticket['AssignedAgents']); ?>
                        <?php else: ?>
                            <span class="unassigned-badge">Offen: <?php echo $ticket['AgeDays']; ?>d</span>
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
    const team = document.getElementById('team-filter').value;
    const status = document.getElementById('status-filter').value;
    const agent = document.getElementById('agent-filter').value;
    const search = document.getElementById('search-input').value;

    const url = new URL(window.location);
    url.searchParams.set('team', team);
    url.searchParams.set('status', status);
    if (agent) { url.searchParams.set('agent', agent); } else { url.searchParams.delete('agent'); }
    if (search) { url.searchParams.set('q', search); } else { url.searchParams.delete('q'); }
    window.location = url;
}
</script>
<?php include 'templates/footer.php'; ?>
