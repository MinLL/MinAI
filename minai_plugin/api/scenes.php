<?php
// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");

$db = new sql();

// Handle GET request to fetch table data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $table = 'minai_scenes_descriptions'; // Set default table
    $data = $db->fetchAll("SELECT * FROM $table");
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Handle POST request to insert new data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = 'minai_scenes_descriptions';
    
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
                
                $setClause = "sexlab_id = '{$db->escape($data['sexlab_id'])}', description = '{$db->escape($data['description'])}'";
                $result = $db->update($table, $setClause, "ostim_id = '" . $db->escape($id) . "'");
                break;
                
            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Missing id for delete');
                }
                $id = $_POST['id'];
                $result = $db->delete($table, "ostim_id = '" . $db->escape($id) . "'");
                break;

            case 'import':
                if (!isset($_POST['data'])) {
                    throw new Exception('No data provided for import');
                }
                $importData = json_decode($_POST['data'], true);
                
                foreach ($importData as $entry) {
                    // Check if ID already exists
                    $exists = $db->fetchAll("SELECT ostim_id FROM $table WHERE ostim_id = '" . $db->escape($entry['ostim_id']) . "'");
                    if (empty($exists)) {
                        $result = $db->insert($table, [
                            "ostim_id" => $db->escape($entry['ostim_id']),
                            "sexlab_id" => $db->escape($entry['sexlab_id']),
                            "description" => $db->escape($entry['description'])
                        ]);
                    }
                }
                break;

            case 'add':
                if (!isset($_POST['data'])) {
                    throw new Exception('No data provided for add');
                }
                $data = json_decode($_POST['data'], true);
                
                // Validate required fields
                if (empty($data['ostim_id']) || empty($data['sexlab_id']) || empty($data['description'])) {
                    throw new Exception('Missing required fields');
                }
                
                // Check if ID already exists
                $exists = $db->fetchAll("SELECT ostim_id FROM $table WHERE ostim_id = '" . $db->escape($data['ostim_id']) . "'");
                if (!empty($exists)) {
                    throw new Exception('Scene with this OStim ID already exists');
                }
                
                // Insert new record
                $columns = [
                    'ostim_id' => $db->escape($data['ostim_id']),
                    'sexlab_id' => $db->escape($data['sexlab_id']),
                    'description' => $db->escape($data['description'])
                ];
                
                try {
                    $db->insert($table, $columns);
                    // Check if the record was actually inserted
                    $check = $db->fetchAll("SELECT ostim_id FROM $table WHERE ostim_id = '" . $db->escape($data['ostim_id']) . "'");
                    if (empty($check)) {
                        $error = pg_last_error();
                        throw new Exception('Database error: ' . ($error ? $error : 'Failed to verify scene insertion'));
                    }
                } catch (Exception $e) {
                    throw new Exception('Failed to insert scene: ' . $e->getMessage());
                }
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
    error_log("updating for $id");
    // Update the scene description
    $setClause = "sexlab_id = '{$db->escape($data['sexlab_id'])}', description = '{$db->escape($data['description'])}'";
    $result = $db->update($table, $setClause, "ostim_id = '$id'");

    echo json_encode(['status' => 'success']);
}

// Handle DELETE request to delete existing data
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $table = $_DELETE['table'];
    $id = $_DELETE['id'];

    // Delete the entry
    $result = $db->delete($table, "ostim_id = '$id'");

    echo json_encode(['status' => 'success']);
}
?>
