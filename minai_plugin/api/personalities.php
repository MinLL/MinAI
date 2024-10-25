<?php
// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");

$db = new Sql();

// Handle GET request to fetch table data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
        $data = $db->fetchAll("SELECT * FROM $table");
        echo json_encode($data);
    }
}

// Handle POST request to insert new data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $data = json_decode($_POST['data'], true);
    $jsonData = json_encode([
        "orientation" => $data['orientation'],
        "sexFantasies" => $data['sexFantasies'],
        "sexualBehavior" => $data['sexualBehavior'],
        "relationshipStyle" => $data['relationshipStyle'],
        "speakStyleDuringSex" => $data['speakStyleDuringSex'],
        "sexPersonalityTraits" => $data['sexPersonalityTraits'],
        "preferredSexPositions" => $data['preferredSexPositions']
    ]);

    $result = $db->insert($table, [
        "id"  => $data['id'],
        "x_personality" => $db->escape($jsonData)
    ]);
    echo json_encode(['status' => 'success']);
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
