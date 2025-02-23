<?php
require_once("config.php");

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
    $GLOBALS["responseTemplate"] = [
        "character"=>$GLOBALS["PLAYER_NAME"],
        "listener"=>"{$GLOBALS['PLAYER_NAME']} is singing to those around {$pronouns['object']}",
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
    $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
    $GLOBALS["responseTemplate"] = [
        "character"=>IsExplicitScene() ? $GLOBALS["PLAYER_NAME"] . "'s body" : $GLOBALS["PLAYER_NAME"] . "'s subconscious",
        "listener"=>IsExplicitScene() ? 
            "{$GLOBALS['PLAYER_NAME']} is reacting to physical sensations" : 
            "{$GLOBALS['PLAYER_NAME']} is thinking to {$pronouns['object']}self",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
    ];
}
