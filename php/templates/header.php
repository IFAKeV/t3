<?php
if (!isset($title)) { $title = 'IFAK Ticketsystem'; }
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$php_base = $base_url;
$css_version = filemtime(__DIR__ . '/../static/css/style.css');
if (!isset($week1_dates)) $week1_dates = [];
if (!isset($week2_dates)) $week2_dates = [];
if (!isset($availability_overview)) $availability_overview = [];
if (!isset($status_map)) $status_map = get_availability_status_map();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/static/css/style.css?v=<?php echo $css_version; ?>">
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="<?php echo $base_url; ?>/static/img/ifak-ticket-logo.svg" alt="IFAK Logo" width="200"></a>
    </div>
    <?php if (!empty($agents_overview)): ?>
    <div class="agent-overview">
        <?php foreach ($agents_overview as $ov): ?>
            <div class="agent-box">
                <div class="week-boxes">
                    <?php foreach ($week1_dates as $d): ?>
                        <?php $info = $availability_overview[$ov['AgentID']]['week1'][$d] ?? null; ?>
                        <?php $color = $info ? $info['ColorCode'] : '#000000'; ?>
                        <span class="day-box" style="background-color: <?php echo htmlspecialchars($color); ?>"></span>
                    <?php endforeach; ?>
                </div>
                <a href="index.php?agent=<?php echo $ov['AgentID']; ?>" class="agent-link">
                    <?php echo htmlspecialchars($ov['AgentName']); ?>
                    (<?php echo $ov['OpenTickets']; ?>)
                </a>
                <div class="week-boxes">
                    <?php foreach ($week2_dates as $d): ?>
                        <?php $info = $availability_overview[$ov['AgentID']]['week2'][$d] ?? null; ?>
                        <?php $color = $info ? $info['ColorCode'] : '#000000'; ?>
                        <span class="day-box" style="background-color: <?php echo htmlspecialchars($color); ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="index.php?action=availabilities">Verfügbarkeiten</a></li>
            <li><a href="index.php?action=new_ticket" class="button">+ Neues Ticket</a></li>
            <?php if (isset($agent)): ?>
            <li class="user-info">
                <span class="team-badge" style="background-color: <?php echo htmlspecialchars($agent['TeamColor']); ?>">
                    <?php echo htmlspecialchars($agent['TeamName']); ?>
                </span>
                <span><?php echo htmlspecialchars($agent['AgentName']); ?></span>
                <a href="index.php?action=logout" class="logout">Abmelden</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
<?php if (!empty($flash)) { echo '<div class="flash-message">' . htmlspecialchars($flash) . '</div>'; }
?>
