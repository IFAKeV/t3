<?php $title = 'Ticket #' . $ticket['TicketID']; include 'templates/header.php'; ?>
<h2>Ticket #<?php echo $ticket['TicketID']; ?> - <?php echo htmlspecialchars($ticket['Title']); ?></h2>
<p>Status: <?php echo htmlspecialchars($ticket['StatusName']); ?> | Priorit\xC3\xA4t: <?php echo htmlspecialchars($ticket['PriorityName']); ?></p>
<p><?php echo nl2br(htmlspecialchars($ticket['Description'])); ?></p>
<p>Kontakt: <?php echo htmlspecialchars($ticket['ContactName']); ?></p>
<?php if ($attachments): ?>
<ul>
<?php foreach ($attachments as $a): ?>
<li><a href="uploads/<?php echo $a['StoragePath']; ?>" target="_blank"><?php echo htmlspecialchars($a['FileName']); ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php include 'templates/footer.php'; ?>
