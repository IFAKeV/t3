<?php
require_once __DIR__ . '/config.php';

function get_db($type = 'ticket') {
    global $DATABASE;
    static $connections = [];
    if (!isset($connections[$type])) {
        $dsn = 'sqlite:' . $DATABASE[$type . '_db'];
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connections[$type] = $pdo;
    }
    return $connections[$type];
}

function query_db($query, $params = [], $one = false, $type = 'ticket') {
    $db = get_db($type);
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    if ($one) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insert_db($table, $fields, $values, $type = 'ticket') {
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $query = 'INSERT INTO ' . $table . ' (' . implode(',', $fields) . ') VALUES (' . $placeholders . ')';
    $db = get_db($type);
    $stmt = $db->prepare($query);
    $stmt->execute($values);
    return $db->lastInsertId();
}

function update_db($table, $id_field, $id_value, $fields, $values, $type = 'ticket') {
    $set_clause = implode(',', array_map(function($f){return "$f = ?";}, $fields));
    $values[] = $id_value;
    $query = 'UPDATE ' . $table . ' SET ' . $set_clause . ' WHERE ' . $id_field . ' = ?';
    $db = get_db($type);
    $stmt = $db->prepare($query);
    $stmt->execute($values);
}
?>
