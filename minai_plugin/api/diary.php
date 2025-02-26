<?php
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Authorization");
require_once("../logger.php");

$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
$GLOBALS["db"] = new sql();

try {
    // Get all diary entries
    $query = "SELECT * FROM diarylog ORDER BY localts DESC";
    $entries = $GLOBALS["db"]->fetchAll($query);

    echo json_encode([
        'status' => 'success',
        'entries' => $entries,
        'playerName' => $GLOBALS["PLAYER_NAME"]
    ]);

} catch (Exception $e) {
    minai_log("error", "Diary API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 