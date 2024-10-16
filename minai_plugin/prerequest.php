<?php
require_once("config.php");
require_once("util.php");

Function SetRadiance($rechat_h, $rechat_p) {
    // error_log("minai: Setting Rechat Parameters (h={$rechat_h}, p={$rechat_p})");
    $GLOBALS["RECHAT_H"] = $rechat_h;
    $GLOBALS["RECHAT_P"] = $rechat_p;
}

Function GetNarratorConfigPath() {
    $path = dirname((__FILE__)) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
    $newConfFile=md5("The Narrator");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}
Function SetNarratorProfile() {
    if (!file_exists(GetNarratorConfigPath())) {
        error_log("minai: Initializing Narrator Profile");
        createProfile("The Narrator");
    }
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && $GLOBALS["use_narrator_profile"]) {
        error_log("minai: Overwriting profile with narrator profile.");
        require_once(GetNarratorConfigPath());
    }
}

if (IsNewRadiantConversation()) {
    error_log("minai: Initial radiant conversation, overriding rechat parameters");
    $GLOBALS["db"]->delete("conf_opts", "id='_minai_RADIANT//initial'");
    SetRadiance(2, 0); // Always rechat at least once
}
elseif (IsRadiant()) {
    SetRadiance($GLOBALS["radiance_rechat_h"], $GLOBALS["radiance_rechat_p"]);
}

SetNarratorProfile();

?>
