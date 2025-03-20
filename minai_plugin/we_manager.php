<?php
header('Content-Type: application/json');

$configFilepath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;
$rootEnginePath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
$pluginPath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "ext" . DIRECTORY_SEPARATOR . "minai_plugin";

if (!file_exists($configFilepath . "conf.php")) {
    @copy($configFilepath . "conf.sample.php", $configFilepath . "conf.php");   // Defaults
    if (!file_exists($rootEnginePath . "data" . DIRECTORY_SEPARATOR . "mysqlitedb.db")) {
        require($rootEnginePath . "ui" . DIRECTORY_SEPARATOR . "cmd" . DIRECTORY_SEPARATOR . "install-db.php");
    }
    die(header("Location: conf_wizard.php"));
}

require_once($rootEnginePath . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($rootEnginePath . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS["DBDRIVER"]}.class.php");
require_once("logger.php");

$db = new sql();
$GLOBALS['db'] = $db;

// Now include other files that need database access
require_once($pluginPath . DIRECTORY_SEPARATOR . "db_utils.php");
require_once($pluginPath . DIRECTORY_SEPARATOR . "util.php");

// Ensure database schema is up to date
ensureAllDatabaseSchemas();

$response = ['status' => 'success'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    minai_log("info", 'post action: ' . $action);
    
    switch ($action) {
        case 'add':
            $baseFormId = $db->escape($_POST['baseFormId']);
            $modName = $db->escape($_POST['modName']);
            $name = $db->escape($_POST['name']);
            $description = $db->escape($_POST['description']);

            $db->execQuery("INSERT INTO equipment_description (baseformid, modname, name, description) 
                           VALUES ('{$baseFormId}', '{$modName}', '{$name}', '{$description}')");
            break;

        case 'edit':
            $baseFormId = $db->escape($_POST['baseFormId']);
            $modName = $db->escape($_POST['modName']);
            $description = $db->escape($_POST['description']);

            $db->execQuery("UPDATE equipment_description 
                           SET description = '{$description}' 
                           WHERE baseformid = '{$baseFormId}' 
                           AND modname = '{$modName}'");
            break;

        case 'delete':
            $baseFormId = $db->escape($_POST['baseFormId']);
            $modName = $db->escape($_POST['modName']);

            $db->execQuery("DELETE FROM equipment_description 
                           WHERE baseformid = '{$baseFormId}' 
                           AND modname = '{$modName}'");
            break;

        case 'toggle_visibility':
            $baseFormId = $db->escape($_POST['baseFormId']);
            $modName = $db->escape($_POST['modName']);
            $isHidden = $_POST['isHidden'] === '1' ? true : false;
            $newHiddenValue = !$isHidden;

            $db->execQuery("UPDATE equipment_description 
                           SET is_hidden = " . ($newHiddenValue ? 'TRUE' : 'FALSE') . " 
                           WHERE baseformid = '{$baseFormId}' 
                           AND modname = '{$modName}'");

            $response['is_hidden'] = $newHiddenValue ? 1 : 0;
            break;
    }

    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'load') {
    // Load data with optional filters
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'baseFormId';

    $query = "SELECT * FROM equipment_description WHERE 1=1";

    $baseFormId = $db->escape($_GET['baseFormId'] ?? '');
    $modName = $db->escape($_GET['modName'] ?? '');
    $name = $db->escape($_GET['name'] ?? '');
    $description = $db->escape($_GET['description'] ?? '');

    if (!empty($baseFormId)) {
        $query .= " AND baseformid ILIKE '%{$baseFormId}%'";
    }

    if (!empty($modName)) {
        $query .= " AND modname ILIKE '%{$modName}%'";
    }

    if (!empty($name)) {
        $query .= " AND name ILIKE '%{$name}%'";
    }

    if (!empty($description)) {
        $query .= " AND description ILIKE '%{$description}%'";
    }

    $query .= " ORDER BY $sort";

    $result = $db->fetchAll($query);

    $data = [];
    foreach ($result as $row) {
        // PostgreSQL returns lowercase column names
        $data[] = [
            'baseFormId' => $row['baseformid'],
            'modName' => $row['modname'],
            'name' => $row['name'],
            'description' => $row['description'],
            'isHidden' => isset($row['is_hidden']) ? ($row['is_hidden'] == 't' || $row['is_hidden'] == '1' || $row['is_hidden'] === true) : false
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}