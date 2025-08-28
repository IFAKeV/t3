<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models.php';

function send_push_notification($agent_ids, $title, $body) {
    global $PUSH_VAPID_PUBLIC_KEY, $PUSH_VAPID_PRIVATE_KEY, $PUSH_VAPID_SUBJECT;

    if (!class_exists('Minishlink\\WebPush\\WebPush')) {
        return;
    }

    $auth = [
        'VAPID' => [
            'subject' => $PUSH_VAPID_SUBJECT,
            'publicKey' => $PUSH_VAPID_PUBLIC_KEY,
            'privateKey' => $PUSH_VAPID_PRIVATE_KEY,
        ],
    ];

    $webPush = new Minishlink\WebPush\WebPush($auth);

    $query = 'SELECT AgentID, Endpoint, P256dh, Auth FROM AgentPushSubscriptions';
    $params = [];
    if (!empty($agent_ids)) {
        $placeholders = implode(',', array_fill(0, count($agent_ids), '?'));
        $query .= " WHERE AgentID IN ($placeholders)";
        $params = $agent_ids;
    }
    try {
        $subs = query_db($query, $params);
    } catch (Exception $e) {
        return;
    }
    if (!$subs) return;

    $payload = json_encode(['title' => $title, 'body' => $body]);

    foreach ($subs as $sub) {
        $subscription = Minishlink\WebPush\Subscription::create([
            'endpoint' => $sub['Endpoint'],
            'publicKey' => $sub['P256dh'],
            'authToken' => $sub['Auth'],
        ]);
        $webPush->queueNotification($subscription, $payload);
    }

    $webPush->flush();
}

if (php_sapi_name() !== 'cli' && isset($_REQUEST['title']) && isset($_REQUEST['body'])) {
    $agent = isset($_REQUEST['agent']) ? intval($_REQUEST['agent']) : null;
    send_push_notification($agent ? [$agent] : [], $_REQUEST['title'], $_REQUEST['body']);
}

