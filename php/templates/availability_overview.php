<?php
$title = 'Verfügbarkeiten - IFAK Ticketsystem';
include 'templates/header.php';
if (!isset($calendar_start)) {
    $calendar_start = new DateTime('first day of this month', new DateTimeZone('Europe/Berlin'));
    $calendar_end = (clone $calendar_start)->modify('+2 month')->modify('last day of this month');
}
$month_names = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
?>
<h1>Verfügbarkeiten</h1>
<p><a href="index.php?action=edit_availability">Meine Verfügbarkeit bearbeiten</a></p>
<?php foreach ($agents as $ag): ?>
    <h2><?php echo htmlspecialchars($ag['AgentName']); ?></h2>
    <?php $data = $avail_data[$ag['AgentID']] ?? []; ?>
    <?php if (empty($data)): ?>
        <div class="availability-warning">Keine Verfügbarkeiten hinterlegt!</div>
    <?php endif; ?>
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
                        <?php $date = $month_start->format('Y-m-') . sprintf('%02d', $d); 
                              $color = $data[$date]['ColorCode'] ?? $status_map[1]['ColorCode'];
                              $code = $data[$date]['ShortCode'] ?? $status_map[1]['ShortCode']; ?>
                        <td style="background-color: <?php echo htmlspecialchars($color); ?>" title="<?php echo htmlspecialchars($code); ?>">
                            <?php echo $d; ?>
                        </td>
                    <?php endfor; ?>
                    </tr>
                </table>
            </div>
        <?php endfor; ?>
    </div>
<?php endforeach; ?>
<?php include 'templates/footer.php'; ?>
