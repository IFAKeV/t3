<?php
$title = 'Meine Verfügbarkeit - IFAK Ticketsystem';
include 'templates/header.php';
$month_names = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
// Nur der Auszubildende darf den Status "Schule" nutzen
if ($agent['AgentID'] !== $TRAINEE_AGENT_ID) {
    $statuses = array_filter($statuses, fn($s) => $s['StatusID'] != 4);
}
?>
<h1>Meine Verfügbarkeit</h1>
<form method="POST" action="index.php?action=edit_availability">
<div class="calendar-months">
<?php for ($m=0; $m<3; $m++): ?>
    <?php $month_start = (clone $calendar_start)->modify("+$m month");
          $month_end = (clone $month_start)->modify('last day of this month'); ?>
    <div class="calendar-month">
        <div class="month-name">
            <?php echo $month_names[(int)$month_start->format('n')-1] . ' ' . $month_start->format('Y'); ?>
        </div>
        <table>
            <tr>
            <?php for ($d=1; $d <= (int)$month_end->format('j'); $d++): ?>
                <?php $date = $month_start->format('Y-m-') . sprintf('%02d',$d);
                      $current = $availability[$date]['StatusID'] ?? 1; ?>
                <td>
                    <select name="status[<?php echo $date; ?>]">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $s['StatusID']; ?>" <?php if ($current == $s['StatusID']) echo 'selected'; ?>><?php echo htmlspecialchars($s['ShortCode']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            <?php endfor; ?>
            </tr>
        </table>
    </div>
<?php endfor; ?>
</div>
<input type="submit" value="Speichern">
</form>
<?php include 'templates/footer.php'; ?>
