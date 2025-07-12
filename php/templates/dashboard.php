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
                    <th>Titel</th>
                    <th>Status</th>
                    <th>Priorität</th>
                    <th>Team</th>
                    <th>Erstellt am</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="index.php?action=view_ticket&id=<?php echo $ticket['TicketID']; ?>">#<?php echo $ticket['TicketID']; ?></a></td>
                    <td><?php echo htmlspecialchars($ticket['Title']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['StatusName']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['PriorityName']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['TeamName']); ?></td>
                    <td><?php echo $ticket['CreatedAt']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'templates/footer.php'; ?>
