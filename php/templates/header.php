<?php
if (!isset($title)) { $title = 'IFAK Ticketsystem'; }
$base_url = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$php_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$css_version = filemtime(__DIR__ . '/../../static/css/style.css');
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
        <a href="index.php"><img src="<?php echo $base_url; ?>/static/img/ifak-ticket-logo.svg" alt="IFAK Logo" width="300"></a>
    </div>
    <?php if (!empty($agents_overview)): ?>
    <div class="agent-overview">
        <?php foreach ($agents_overview as $ov): ?>
            <a href="index.php?agent=<?php echo $ov['AgentID']; ?>" class="agent-link">
                <?php echo htmlspecialchars($ov['AgentName']); ?>
                (<?php echo $ov['OpenTickets']; ?>)
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
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
