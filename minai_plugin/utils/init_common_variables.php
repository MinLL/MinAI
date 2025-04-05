<?php
/*
 * This file initializes and caches commonly used variables to reduce redundant function calls
 * across the MinAI codebase. Load this file early in the process to ensure these values are
 * available to other components.
 */
require_once("/var/www/html/HerikaServer/ext/minai_plugin/util.php");

// Cache target actor
$GLOBALS["target"] = GetTargetActor();
$GLOBALS["target_gender"] = IsFemale($GLOBALS["target"]) ? "female" : "male";
$GLOBALS["target_pronouns"] = GetActorPronouns($GLOBALS["target"]);

// Cache player info
$GLOBALS["player_pronouns"] = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
$GLOBALS["player_gender"] = IsFemale($GLOBALS["PLAYER_NAME"]) ? "female" : "male";

// Cache Herika info
$GLOBALS["herika_pronouns"] = GetActorPronouns($GLOBALS["HERIKA_NAME"]);
$GLOBALS["herika_gender"] = IsFemale($GLOBALS["HERIKA_NAME"]) ? "female" : "male";

// Cache nearby actors
$GLOBALS["nearby"] = explode(",", GetActorValue("PLAYER", "nearbyActors"));

// Cache NSFW settings
if (IsChildActor($GLOBALS['HERIKA_NAME']) || IsChildActor($GLOBALS["target"])) {
    $GLOBALS["disable_nsfw"] = true;
}