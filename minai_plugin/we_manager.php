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
require_once("logger.php");

$db = new sql();
$GLOBALS['db'] = $db;

// Check if is_hidden column exists, add it if not
function ensureIsHiddenColumnExists($db) {
    // PostgreSQL syntax
    $checkColumnQuery = "SELECT column_name FROM information_schema.columns 
                         WHERE table_name = 'equipment_description' 
                         AND column_name = 'is_hidden'";
    
    $result = $db->fetchAll($checkColumnQuery);
    
    if (empty($result)) {
        minai_log("info", "Adding is_hidden column to equipment_description table");
        // PostgreSQL syntax for adding column if not exists
        $addColumnQuery = "ALTER TABLE equipment_description ADD COLUMN IF NOT EXISTS is_hidden BOOLEAN DEFAULT FALSE";
        $db->execQuery($addColumnQuery);
        return true;
    }
    
    return false;
}

// Ensure column exists
ensureIsHiddenColumnExists($db);

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

        $insertQuery = "INSERT INTO equipment_description (baseFormId, modName, name, description) VALUES ('{$baseFormId}', '{$modName}', '{$name}', '{$description}')";

        $db->execQuery($insertQuery);

    } elseif ($action === 'edit') {
        // Edit existing row
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);
        //$name = $db->escape($_POST['name']);
        $description = $db->escape($_POST['description']);

        // Update existing record
        $updateQuery = "UPDATE equipment_description SET description = '{$description}' WHERE baseFormId = '{$baseFormId}' AND modName = '{$modName}'";
        minai_log("info", 'update query: ' . $updateQuery);
        $db->execQuery($updateQuery);

    } elseif ($action === 'delete') {
        // Delete existing row
        $baseFormId = $db->escape($_POST['baseFormId']);
        $modName = $db->escape($_POST['modName']);

        $deleteQuery = "DELETE FROM equipment_description WHERE baseFormId = '{$baseFormId}' AND modName = '{$modName}'";
        $db->execQuery($deleteQuery);
        
    } elseif ($action === 'toggle_visibility') {
		// Toggle visibility
		$baseFormId = $db->escape($_POST['baseFormId']);
		$modName = $db->escape($_POST['modName']);
		$isHidden = $_POST['isHidden'] === '1' ? true : false;
		
		// Flip the is_hidden value
		$newHiddenValue = !$isHidden;
		
		// For PostgreSQL, use the same case for columns as returned in your fetchAll
		$updateQuery = "UPDATE equipment_description 
						SET is_hidden = " . ($newHiddenValue ? 'TRUE' : 'FALSE') . " 
						WHERE baseformid = '{$baseFormId}' 
						AND modname = '{$modName}'";
		
		minai_log("info", 'toggle visibility query: ' . $updateQuery);
		
		// Execute the query
		$db->execQuery($updateQuery);
		
		// Return the new state in the response
		$response['is_hidden'] = $newHiddenValue ? 1 : 0;
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
            'is_hidden' => isset($row['is_hidden']) ? $row['is_hidden'] : false
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}