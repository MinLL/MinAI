<?php
$fast_commands = ["addnpc","updateprofile","diary","_quest","setconf","request","_speech","infoloc","infonpc","infonpc_close",
"infoaction","status_msg","delete_event","itemfound","_questdata","_uquest","location","_questreset","chat"];

// $fast_commands = [];
// Check for exact matches against fast commands
if (isset($GLOBALS["gameRequest"]) && in_array($GLOBALS["gameRequest"][0], $fast_commands)) {
    $GLOBALS["minai_skip_processing"] = true;
}
else {
    // error_log("Processing Non-Fast request: " . $GLOBALS["gameRequest"][0]);
}


// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}
require_once("util.php");
require_once("contextbuilders.php");
require_once("roleplaybuilder.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
// InitiateDBTables();

interceptRoleplayInput();

