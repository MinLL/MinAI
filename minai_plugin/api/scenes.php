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

    $result = $db->insert($table, $data);
    
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
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
