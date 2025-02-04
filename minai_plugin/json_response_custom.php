<?php
require_once("config.php");

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $GLOBALS["responseTemplate"] = [
        "character"=>$GLOBALS["PLAYER_NAME"],
        "listener"=>"{$GLOBALS['PLAYER_NAME']} is singing to those around her",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
    ];
}
elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $GLOBALS["responseTemplate"] = [
        "character"=>IsExplicitScene() ? $GLOBALS["PLAYER_NAME"] . "'s body" : $GLOBALS["PLAYER_NAME"] . "'s subconscious",
        "listener"=>IsExplicitScene() ? 
            "{$GLOBALS['PLAYER_NAME']} experiences intense sensations" : 
            "{$GLOBALS['PLAYER_NAME']} is thinking to herself",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
    ];
}
