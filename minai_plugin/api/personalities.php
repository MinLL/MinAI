<?php
// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");

$db = new sql();

// Handle GET request to fetch table data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $table = 'minai_x_personalities'; // Set default table
    $data = $db->fetchAll("SELECT * FROM $table");
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Handle POST request for all operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = 'minai_x_personalities'; // Set default table
    
    if (!isset($_POST['action'])) {
        echo json_encode(['status' => 'error', 'message' => 'No action specified']);
        exit;
    }

    try {
        switch ($_POST['action']) {
            case 'update':
                if (!isset($_POST['data']) || !isset($_POST['id'])) {
                    throw new Exception('Missing data or id for update');
                }
                $data = json_decode($_POST['data'], true);
                $id = $_POST['id'];
                $jsonData = json_encode($data['x_personality']);
                
                $result = $db->update($table, 
                    "x_personality = '" . $db->escape($jsonData) . "'", 
                    "id = '" . $db->escape($id) . "'"
                );
                break;
                
            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Missing id for delete');
                }
                $id = $_POST['id'];
                $result = $db->delete($table, "id = '" . $db->escape($id) . "'");
                break;
        }
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Handle PUT request to update existing data
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $table = $_PUT['table'];
    $data = json_decode($_PUT['data'], true);
    $id = $_PUT['id'];  // Use the ID from the request

    // Update the x_personality JSONB field
    $jsonData = json_encode([
        "orientation" => $data['orientation'],
        "sexFantasies" => $data['sexFantasies'],
        "sexualBehavior" => $data['sexualBehavior'],
        "relationshipStyle" => $data['relationshipStyle'],
        "speakStyleDuringSex" => $data['speakStyleDuringSex'],
        "sexPersonalityTraits" => $data['sexPersonalityTraits'],
        "preferredSexPositions" => $data['preferredSexPositions']
    ]);

    // Update the record where id matches
    $result = $db->update($table, "x_personality = '$jsonData'", "id = '$id'");

    echo json_encode(['status' => 'success']);

}

// Handle DELETE request to delete existing data
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $table = $_DELETE['table'];
    $id = $_DELETE['id'];
    error_log("Deleting $id from $table");
    // Delete the entry
    $result = $db->delete($table, "id = '$id'");
    echo json_encode(['status' => 'success']);
}
?>
