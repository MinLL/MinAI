<?php
header('Content-Type: application/json');

$configFilepath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;
$rootEnginePath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

if (!file_exists($configFilepath . "conf.php")) {
  @copy($configFilepath . "conf.sample.php", $configFilepath . "conf.php");   // Defaults
  if (!file_exists($rootEnginePath . "data" . DIRECTORY_SEPARATOR . "mysqlitedb.db")) {
    require($rootEnginePath . "ui" . DIRECTORY_SEPARATOR . "cmd" . DIRECTORY_SEPARATOR . "install-db.php");
  }
  die(header("Location: conf_wizard.php"));
}

require_once($rootEnginePath . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($rootEnginePath . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS["DBDRIVER"]}.class.php");
$db = new sql();
$GLOBALS['db'] = $db;

require_once("logger.php");
require_once("db_utils.php");
require_once("util.php");
require_once("contextbuilders/wornequipment_context.php");
CreateEquipmentDescriptionTableIfNotExist();
$response = ['status' => 'success'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    minai_log("info", 'post action: ' . $action);
    if ($action === 'add') {
        // Add new row
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);
        $name = $db->escape($_POST['name']);
        $description = $db->escape($_POST['description']);
        // If description is "no description" (case insensitive), use empty string
        if (strtolower($description) === "no description") {
            $description = "";
        }
        $is_restraint = isset($_POST['is_restraint']) ? intval($_POST['is_restraint']) : 0;
        $body_part = $db->escape($_POST['body_part'] ?? '');
        $hidden_by = $db->escape($_POST['hidden_by'] ?? '');
        $is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 0;

        $insertQuery = "INSERT INTO equipment_description (baseFormId, modName, name, description, is_restraint, body_part, hidden_by, is_enabled) 
                        VALUES ('{$baseFormId}', '{$modName}', '{$name}', '{$description}', {$is_restraint}, '{$body_part}', '{$hidden_by}', {$is_enabled})";

        $db->execQuery($insertQuery);

    } elseif ($action === 'edit') {
        // Edit existing row
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);
        $name = $db->escape($_POST['name']);
        $description = $db->escape($_POST['description']);
        // If description is "no description" (case insensitive), use empty string
        if (strtolower($description) === "no description") {
            $description = "";
        }
        $is_restraint = isset($_POST['is_restraint']) ? intval($_POST['is_restraint']) : 0;
        $body_part = $db->escape($_POST['body_part'] ?? '');
        $hidden_by = $db->escape($_POST['hidden_by'] ?? '');
        $is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 0;

        // Update existing record
        $updateQuery = "UPDATE equipment_description 
                        SET name = '{$name}',
                            description = '{$description}',
                            is_restraint = {$is_restraint},
                            body_part = '{$body_part}',
                            hidden_by = '{$hidden_by}',
                            is_enabled = {$is_enabled}
                        WHERE baseformid = '{$baseFormId}' AND modname = '{$modName}'";
        
        minai_log("info", 'update query: ' . $updateQuery);
        $db->execQuery($updateQuery);

    } elseif ($action === 'toggle_enabled') {
        // Toggle enabled status
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);
        $is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 0;
        error_log("Toggling enabled status for {$baseFormId} {$modName}");
        $updateQuery = "UPDATE equipment_description SET is_enabled = {$is_enabled} WHERE baseformid = '{$baseFormId}' AND modname = '{$modName}'";
        error_log("Update query: {$updateQuery}");
        $db->execQuery($updateQuery);
        
    } elseif ($action === 'delete') {
        // Delete existing row
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);

        $deleteQuery = "DELETE FROM equipment_description WHERE baseFormId = '{$baseFormId}' AND modName = '{$modName}'";
        $db->execQuery($deleteQuery);
    }
    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'load') {
    // Load data with optional filters
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'baseFormId';

    $query = "SELECT * FROM equipment_description WHERE 1=1";

    $baseFormId = $db->escape($_GET['baseFormId'] ?? '');
    $modName = $db->escape($_GET['modName'] ?? '');
    $name = $db->escape($_GET['name'] ?? '');
    $description = $db->escape($_GET['description'] ?? '');
    $body_part = $db->escape($_GET['body_part'] ?? '');
    $is_restraint = isset($_GET['is_restraint']) && $_GET['is_restraint'] ? intval($_GET['is_restraint']) : 0;
    $show_disabled = isset($_GET['show_disabled']) && $_GET['show_disabled'] ? true : false;
    
    // Filter for worn equipment
    $filter_worn = isset($_GET['filter_worn']) && $_GET['filter_worn'] ? true : false;
    $actor_name = isset($_GET['actor_name']) && !empty($_GET['actor_name']) ? $_GET['actor_name'] : '';
    
    // Create an array for IDs of worn equipment
    $worn_equipment_ids = [];
    
    // If filtering by worn equipment, get the equipment data
    if ($filter_worn && !empty($actor_name)) {
        $equipment_data = ProcessEquipment($actor_name);
        
        // Combine visible and hidden items
        $all_worn_items = array_merge($equipment_data['visibleItems'], $equipment_data['hiddenItems']);
        
        // Create a list of worn equipment IDs for filtering
        foreach ($all_worn_items as $item) {
            $worn_equipment_ids[] = strtolower($item['baseFormId'] . '|' . $item['modName']);
        }
    }

    if (!empty($baseFormId)) {
        $query .= " AND baseFormId ILIKE '%{$baseFormId}%'";
    }

    if (!empty($modName)) {
        $query .= " AND modName ILIKE '%{$modName}%'";
    }

    if (!empty($name)) {
        $query .= " AND name ILIKE '%{$name}%'";
    }

    if (!empty($description)) {
        $query .= " AND description ILIKE '%{$description}%'";
    }
    
    if (!empty($body_part)) {
        $query .= " AND body_part ILIKE '%{$body_part}%'";
    }
    
    if (isset($_GET['is_restraint']) && $_GET['is_restraint']) {
        $query .= " AND is_restraint = 1";
    }
    
    // Only show enabled items unless specifically asked to show disabled
    if (!$show_disabled) {
        $query .= " AND is_enabled = 1";
    }

    $query .= " ORDER BY $sort";

    $result = $db->fetchAll($query);

    $data = [];
    foreach ($result as $row) {
        // If filtering by worn equipment, check if this item is currently worn
        if ($filter_worn) {
            $item_id = strtolower($row['baseformid'] . '|' . $row['modname']);
            if (!in_array($item_id, $worn_equipment_ids)) {
                continue; // Skip items that aren't worn
            }
        }
        
        // result column name is all lower case for some reason
        $data[] = [
            'baseFormId' => $row['baseformid'],
            'modName' => $row['modname'],
            'name' => $row['name'],
            'description' => $row['description'],
            'is_restraint' => $row['is_restraint'] ?? 0,
            'body_part' => $row['body_part'] ?? '',
            'hidden_by' => $row['hidden_by'] ?? '',
            'is_enabled' => $row['is_enabled'] ?? 1
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
}
