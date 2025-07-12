<?php $title = 'Dashboard - IFAK Ticketsystem'; include 'templates/header.php'; ?>
<div class="dashboard">
    <div class="dashboard-header">
        <h1>Ticket-Übersicht</h1>
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
<?php include 'templates/footer.php'; ?>
