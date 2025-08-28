<?php
$title = 'Verfügbarkeiten - IFAK Ticketsystem';
include 'templates/header.php';
if (!isset($calendar_start)) {
    $calendar_start = new DateTime('first day of this month', new DateTimeZone('Europe/Berlin'));
    $calendar_end = (clone $calendar_start)->modify('+2 month')->modify('last day of this month');
}
$month_names = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
?>
<div class="availability-header">
    <h1>Verfügbarkeiten</h1>
    <div class="availability-legend">
        <?php foreach ($status_map as $st): ?>
        <div class="legend-item">
            <span class="legend-box" style="background-color: <?php echo htmlspecialchars($st['ColorCode']); ?>"></span>
            <span><?php echo htmlspecialchars($st['StatusName']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<p>Klick auf einen Tag, um die eigene Verfügbarkeit zu ändern.</p>
<div class="agent-columns">
<?php foreach ($agents as $ag): ?>
    <?php $data = $avail_data[$ag['AgentID']] ?? []; ?>
    <div class="agent-column">
        <h2><?php echo htmlspecialchars($ag['AgentName']); ?></h2>
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
                        <?php $week_start = (clone $month_start)->modify('monday this week'); ?>
                        <?php while ($week_start <= $month_end): ?>
                            <tr>
                            <?php for ($i=0; $i<7; $i++): ?>
                                <?php $date = $week_start->format('Y-m-d');
                                      $day_num = (int)$week_start->format('j');
                                      $in_month = $week_start->format('m') === $month_start->format('m');
                                      $dow = (int)$week_start->format('N');
                                      $status = $data[$date]['StatusID'] ?? null;
                                      $color = $status ? ($status_map[$status]['ColorCode']) : '#000000';
                                      $code = $status ? ($status_map[$status]['ShortCode']) : ''; ?>
                                <td class="<?php echo $dow>=6 ? 'weekend' : ''; ?> availability-day" data-date="<?php echo $date; ?>" data-status="<?php echo $status ?: 0; ?>" data-agent="<?php echo $ag['AgentID']; ?>" style="background-color: <?php echo $in_month ? htmlspecialchars($color) : '#eee'; ?>" title="<?php echo htmlspecialchars($code); ?>">
                                    <?php echo $in_month ? $day_num : '&nbsp;'; ?>
                                </td>
                                <?php $week_start->modify('+1 day'); ?>
                            <?php endfor; ?>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php endfor; ?>
        </div>
</div>
<?php endforeach; ?>
</div>
<script>
const statusMap = <?php echo json_encode($status_map); ?>;
const traineeId = <?php echo json_encode($TRAINEE_AGENT_ID); ?>;
const currentAgent = <?php echo json_encode($agent['AgentID']); ?>;
const partTimeIds = <?php echo json_encode($PART_TIME_AGENT_IDS); ?>;
const codeMap = {};
Object.values(statusMap).forEach(function(st){
    codeMap[st.ShortCode] = st.StatusID;
});
document.querySelectorAll('.availability-day').forEach(function(td){
    if(td.classList.contains('weekend')) return;
    if(parseInt(td.dataset.agent) !== currentAgent) return;
    td.addEventListener('click', function(){
        let current = parseInt(td.dataset.status);
        const agentId = parseInt(td.dataset.agent);
        let next;
        if(!current){
            // Erste Auswahl: Verfügbar
            next = codeMap['V'];
        } else if(current === codeMap['V']){
            if(partTimeIds.includes(agentId) && codeMap['F']){
                // Von Verfügbar zu Frei (nur Teilzeit)
                next = codeMap['F'];
            } else if(agentId === traineeId && codeMap['S']){
                // Von Verfügbar zu Schule (nur Azubi)
                next = codeMap['S'];
            } else {
                // Von Verfügbar zu Urlaub
                next = codeMap['U'];
            }
        } else if(partTimeIds.includes(agentId) && codeMap['F'] && current === codeMap['F']){
            // Von Frei zu Urlaub
            next = codeMap['U'];
        } else if(agentId === traineeId && codeMap['S'] && current === codeMap['S']){
            // Von Schule zu Urlaub
            next = codeMap['U'];
        } else if(current === codeMap['U']){
            // Von Urlaub zu Krank
            next = codeMap['K'];
        } else {
            // Zurück zu Verfügbar
            next = codeMap['V'];
        }
        td.dataset.status = next;
        td.style.backgroundColor = statusMap[next].ColorCode;
        fetch('index.php?action=set_availability', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'date=' + encodeURIComponent(td.dataset.date) + '&status_id=' + next
        });
    });
});
</script>
<?php include 'templates/footer.php'; ?>
