<?php
// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");
require_once("../logger.php");

$db = new sql();

// Add this helper function at the top of the file after the requires
function extractJson($text) {
    // Extract everything between first { and last }
    if (preg_match('/\{[\s\S]*\}/s', $text, $matches)) {
        return $matches[0];
    }
    return $text;
}

// Handle GET request to fetch table data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $table = 'minai_x_personalities'; // Set default table
    $data = $db->fetchAll("SELECT * FROM $table");
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Handle POST request for all operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = 'minai_x_personalities';
    
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
                
                $rawData = $_POST['data'];
                $jsonData = extractJson($rawData);
                $data = json_decode($jsonData, true);
                
                if ($data === null) {
                    throw new Exception('Invalid JSON data provided');
                }
                
                $id = $_POST['id'];
                
                // Check if entry exists
                $exists = $db->fetchAll("SELECT id FROM $table WHERE id = '" . $db->escape($id) . "'");
                
                if (empty($exists)) {
                    // Insert new entry
                    try {
                        $db->insert($table, [
                            'id' => $id,
                            'x_personality' => json_encode($data['x_personality'])
                        ]);
                        // Verify the insert worked by checking if the record exists
                        $verify = $db->fetchAll("SELECT id FROM $table WHERE id = '" . $db->escape($id) . "'");
                        if (!empty($verify)) {
                            echo json_encode([
                                'status' => 'success',
                                'message' => 'New personality created successfully',
                                'id' => $id
                            ]);
                        } else {
                            throw new Exception('Failed to verify new personality creation');
                        }
                    } catch (Exception $e) {
                        minai_log("info", "Insert error: " . $e->getMessage());
                        throw new Exception('Failed to create new personality: ' . $e->getMessage());
                    }
                } else {
                    // Update existing entry
                    try {
                        $db->update($table, 
                            "x_personality = '" . $db->escape(json_encode($data['x_personality'])) . "'",
                            "id = '" . $db->escape($id) . "'"
                        );
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Personality updated successfully',
                            'id' => $id
                        ]);
                    } catch (Exception $e) {
                        minai_log("info", "Update error: " . $e->getMessage());
                        throw new Exception('Failed to update personality: ' . $e->getMessage());
                    }
                }
                break;
                
            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Missing id for delete');
                }
                $id = $_POST['id'];
                try {
                    $db->delete($table, "id = '" . $db->escape($id) . "'");
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Personality deleted successfully',
                        'id' => $id
                    ]);
                } catch (Exception $e) {
                    minai_log("info", "Delete error: " . $e->getMessage());
                    throw new Exception('Failed to delete personality: ' . $e->getMessage());
                }
                break;

            case 'import':
                if (!isset($_POST['data'])) {
                    throw new Exception('No data provided for import');
                }
                $importData = json_decode($_POST['data'], true);
                $imported = 0;
                $skipped = 0;
                
                foreach ($importData as $entry) {
                    // Check if ID already exists
                    $exists = $db->fetchAll("SELECT id FROM $table WHERE id = '" . $db->escape($entry['id']) . "'");
                    if (empty($exists)) {
                        try {
                            $db->insert($table, [
                                "id" => $entry['id'],
                                "x_personality" => $entry['x_personality']
                            ]);
                            $imported++;
                        } catch (Exception $e) {
                            minai_log("info", "Import error for ID {$entry['id']}: " . $e->getMessage());
                            $skipped++;
                        }
                    } else {
                        $skipped++;
                    }
                }
                
                echo json_encode([
                    'status' => 'success',
                    'message' => "Import completed: $imported added, $skipped skipped",
                    'imported' => $imported,
                    'skipped' => $skipped
                ]);
                break;

            default:
                throw new Exception('Invalid action specified');
        }
    } catch (Exception $e) {
        minai_log("info", "Personalities API error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'details' => 'Check server logs for more information'
        ]);
    }
    exit;
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
    minai_log("info", "Deleting $id from $table");
    // Delete the entry
    $result = $db->delete($table, "id = '$id'");
    echo json_encode(['status' => 'success']);
}
