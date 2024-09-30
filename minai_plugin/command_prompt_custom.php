<?php
require_once("util.php");
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"]="Choose a coherent ACTION that is available to you in order to obey or physically interact with {$GLOBALS["PLAYER_NAME"]}. You can also use an ACTION to interact with the world, provide services, or indicate your arousal. ";

if ($GLOBALS["PROMPT_HEAD_OVERRIDE"] != "" && isset($GLOBALS["PROMPT_HEAD_OVERRIDE"]))  {
    $GLOBALS["PROMPT_HEAD"] = $GLOBALS["PROMPT_HEAD_OVERRIDE"];
}

?>
