<?php
require_once("config.php");

if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $GLOBALS["responseTemplate"] = [
        "character"=>$GLOBALS["PLAYER_NAME"] . "'s subconscious",
        "listener"=>"{$GLOBALS['PLAYER_NAME']} is thinking to herself",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
    ];
}
