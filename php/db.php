<?php
require_once __DIR__ . '/config.php';

function get_db($type = 'ticket') {
    global $DATABASE;
    static $connections = [];
    if (!isset($connections[$type])) {
        $db = new SQLite3($DATABASE[$type . '_db']);
        $db->enableExceptions(true);
        $db->busyTimeout(5000);
        $connections[$type] = $db;
    }
    return $connections[$type];
}

function bind_params($stmt, $params) {
    foreach (array_values($params) as $idx => $val) {
        $type = SQLITE3_TEXT;
        if (is_int($val) || ctype_digit((string)$val)) {
            $type = SQLITE3_INTEGER;
        } elseif (is_float($val)) {
            $type = SQLITE3_FLOAT;
        } elseif (is_null($val)) {
            $type = SQLITE3_NULL;
        }
        $stmt->bindValue($idx + 1, $val, $type);
    }
}

function query_db($query, $params = [], $one = false, $type = 'ticket') {
    $db = get_db($type);
    $stmt = $db->prepare($query);
    bind_params($stmt, $params);
    $result = $stmt->execute();
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    $stmt->close();
    if ($one) {
        return $rows[0] ?? null;
    }
    return $rows;
}

function insert_db($table, $fields, $values, $type = 'ticket') {
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $query = 'INSERT INTO ' . $table . ' (' . implode(',', $fields) . ') VALUES(' . $placeholders . ')';
    $db = get_db($type);
    $stmt = $db->prepare($query);
    bind_params($stmt, $values);
    $stmt->execute();
    $id = $db->lastInsertRowID();
    $stmt->close();
    return $id;
}

function update_db($table, $id_field, $id_value, $fields, $values, $type = 'ticket') {
    $set_clause = implode(',', array_map(fn($f) => "$f = ?", $fields));
    $values[] = $id_value;
    $query = 'UPDATE ' . $table . ' SET ' . $set_clause . ' WHERE ' . $id_field . ' = ?';
    $db = get_db($type);
    $stmt = $db->prepare($query);
    bind_params($stmt, $values);
    $stmt->execute();
    $stmt->close();
}
?>
