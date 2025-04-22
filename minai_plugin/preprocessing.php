<?php
// Start metrics for this entry point
require_once("utils/metrics_util.php");

$fast_commands = ["addnpc","_quest","setconf","request","_speech","infoloc","infonpc","infonpc_close",
"infoaction","status_msg","delete_event","itemfound","_questdata","_uquest","location","_questreset"];

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

// minai_start_timer('CHIM');
// minai_start_timer('Pre-LLM', 'CHIM');
minai_start_timer('preprocessing_php', 'MinAI');

// Initialize common variables
require_once("utils/init_common_variables.php");

// Check for banned phrases in gameRequest[3]
$banned_phrases = ["Thank you for watching", "Thanks for watching", "Thank you very much for watching"];
if (isset($GLOBALS["gameRequest"][3])) {
    $message = strtolower($GLOBALS["gameRequest"][3]);
    foreach ($banned_phrases as $phrase) {
        if (stripos($message, strtolower($phrase)) !== false) {
            error_log("MinAI: Aborting request due to banned phrase: " . $phrase);
            die("Banned phrase detected: " . $phrase);
        }
    }
}

require_once("util.php");
require_once("contextbuilders.php");
require_once("roleplaybuilder.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
// InitiateDBTables();

// This is a hack to get around CHIM eating "diary" requests for the player in the DLL
if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary") {
    minai_log("info", "Diary request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "diary";
}
// Check to see if this is a profile update request
if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_updateprofile") {
    minai_log("info", "Profile update request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "updateprofile";
}

if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_updateprofile_player") {
    minai_log("info", "Profile update request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "updateprofile";
    SetNarratorProfile();
}

if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary_player") {
    minai_log("info", "Diary request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "diary";
    SetNarratorProfile();
}

interceptRoleplayInput();

minai_stop_timer('preprocessing_php');