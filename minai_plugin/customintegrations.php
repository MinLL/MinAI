<?php
// We need access to gameRequest here, but it's not global.
// Impl copied from main.php

require_once(__DIR__.DIRECTORY_SEPARATOR."updateThreadsDB.php");

function CreateContextTableIfNotExists() {
  $db = $GLOBALS['db'];
  $db->execQuery(
    "CREATE TABLE IF NOT EXISTS custom_context (
      modName TEXT NOT NULL,
      eventKey TEXT NOT NULL,
      eventValue TEXT NOT NULL,
      ttl INT,
      expiresAt INT,
      PRIMARY KEY (modName, eventKey)
    )"
  );
  $db->execQuery(
    "ALTER TABLE custom_context
     ADD COLUMN IF NOT EXISTS npcName TEXT NOT NULL
  ");
}


function CreateActionsTableIfNotExists() {
  $db = $GLOBALS['db'];
  $db->execQuery(
    "CREATE TABLE IF NOT EXISTS custom_actions (
      actionName TEXT NOT NULL,
      actionPrompt TEXT NOT NULL,
      targetDescription TEXT NOT NULL,
      targetEnum TEXT NOT NULL,
      enabled INT,
      ttl INT,
      expiresAt INT,
      PRIMARY KEY (actionName, actionPrompt)
    )"
  );
}


function SetGameRequest() {
    if (strpos($_SERVER["QUERY_STRING"],"&")===false)
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5)));
    else
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5,strpos($_SERVER["QUERY_STRING"],"&")-4)));
    $GLOBALS["gameRequest"] = explode("|", $receivedData);
    error_log("minai: Received Data: {$receivedData}");
}


function ProcessIntegrations() {
    // Handle allowing third party mods to register things with the context system
    SetGameRequest();
    CreateContextTableIfNotExists();
    $MUST_DIE=false;
    if (isset($GLOBALS["use_defeat"]) && $GLOBALS["use_defeat"] && IsModEnabled("SexlabDefeat")) {
        $GLOBALS["events_to_ignore"][] = "combatend";
        $GLOBALS["events_to_ignore"][] = "combatendmighty";
    }
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["events_to_ignore"]) && in_array($GLOBALS["gameRequest"][0], $GLOBALS["events_to_ignore"])) {
        error_log("minai: Event {$GLOBALS["gameRequest"][0]} in ignore list, blocking.");
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_init") {
        // This is sent once by the SKSE plugin when the game is loaded. Do our initialization here.
        error_log("minai: Initializing");
        CreateThreadsTableIfNotExists();
        CreateActionsTableIfNotExists();
        CreateContextTableIfNotExists();
        importXPersonalities();
        importScenesDescriptions();
        $MUST_DIE=true;

    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "storecontext") {
        $db = $GLOBALS['db'];
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $modName = $vars[0];
        $eventKey = $vars[1];
        $eventValue = $vars[2];
        $npcName = $vars[3];
        $ttl = intval($vars[4]);
        error_log("minai: Storing custom context: {$modName}, {$eventKey}, {$eventValue}, {$ttl}");
        $db->delete("custom_context", "modName='".$db->escape($modName)."' AND eventKey='".$db->escape($eventKey)."'");
        $db->insert(
            'custom_context',
            array(
                'modName' => $db->escape($modName),
                'eventKey' => $db->escape($eventKey),
                'eventValue' => $db->escape($eventValue),
                'expiresAt' => time() + $ttl,
                'npcName' => $db->escape($npcName),
                'ttl' => $ttl // already converted to int, no need to escape
            )
        );
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "registeraction") {
        $db = $GLOBALS['db'];
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $actionName = $vars[0];
        $actionPrompt = $vars[1];
        $enabled = $vars[2];
        $ttl = intval($vars[3]);
        $targetDescription = $vars[4];
        $targetEnum = $vars[5];
        error_log("minai: Registering custom action: {$actionName}, {$actionPrompt}, {$enabled}, {$ttl}");
        $db->delete("custom_actions", "actionName='".$db->escape($actionName)."'");
        $db->insert(
            'custom_actions',
            array(
                'actionName' => $db->escape($actionName),
                'actionPrompt' => $db->escape($actionPrompt),
                'enabled' => $enabled,
                'expiresAt' => time() + $ttl,
                'ttl' => $ttl, // already converted to int, no need to escape
                'targetDescription' => $db->escape($targetDescription),
                'targetEnum' => $db->escape($targetEnum)
            )
        );
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "updateThreadsDB") {
        updateThreadsDB();
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) =="npc_talk") {
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $tmp = explode(":", $vars[0]);
        $speaker = $tmp[sizeof($tmp)-1];
        $target = $vars[1];
        $message = $vars[2];
        error_log("minai: Processing NPC request ({$speaker} => {$target}: {$message})");
        $GLOBALS["PROMPTS"]["npc_talk"]= [
            "cue"=>[
                "write dialogue for {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  "
            ], 
            "player_request"=>[
                "{$speaker}: {$message} (Talking to {$target})"
            ]
        ];
    }
    if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["radiant", "radiantsearchinghostile", "radiantsearchingfriend", "radiantcombathostile", "radiantcombatfriend"])) {
        if (time() > GetLastInput() + $GLOBALS["input_delay_for_radiance"]) {
            // $GLOBALS["HERIKA_NAME"] is npc1
            $GLOBALS["HERIKA_TARGET"] = explode(":", $GLOBALS["gameRequest"][3])[3];
            if ($GLOBALS["HERIKA_TARGET"] == $GLOBALS["HERIKA_NAME"])
                $GLOBALS["HERIKA_TARGET"] = $GLOBALS["PLAYER_NAME"];
            error_log("minai: Starting {$GLOBALS["gameRequest"][0]} dialogue between {$GLOBALS["HERIKA_NAME"]} and {$GLOBALS["HERIKA_TARGET"]}");
            StoreRadiantActors($GLOBALS["HERIKA_TARGET"], $GLOBALS["HERIKA_NAME"]);
        }
        else {
            // Avoid race condition where we send input, the server starts to process the request, and then
            // a radiant request comes in 
            error_log("minai: Not starting radiance: Input was too recent");
            $MUST_DIE=true;
        }
    }
    if (in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s","rechat","bored", "radiant"])) {
        if (!in_array($GLOBALS["gameRequest"][0], ["radiant", "rechat"]))
            ClearRadiantActors();
        error_log("minai: Setting lastInput time.");
        $db = $GLOBALS['db'];
        $id = "_minai_RADIANT//lastInput";
        $db->delete("conf_opts", "id='{$id}'");
        $db->insert(
            'conf_opts',
            array(
                'id' => $id,
                'value' => time()
            )
        );
    }
    if (isset($GLOBALS["gameRequest"]) && str_starts_with(strtolower($GLOBALS["gameRequest"][0]), "sextalk")) {
        $type = $GLOBALS["gameRequest"][0];
        $scene = getScene($GLOBALS["HERIKA_NAME"]);
        $sceneDesc = $scene["description"];
        if(!$sceneDesc) {
            if($scene["fallback"]) {
                $sceneDesc = $scene["fallback"];
            } else {
                $sceneDesc = "{$scene["actors"]} are having sex.";
            }
            
        }

        $prompt = "";

        switch($type) {
            case "sextalk_scenechange": {
                $prompt = "The Narrator: ";
                
                if(!$scene["prev_scene_id"]) {
                    $prompt .= "{$scene["actors"]} started sex scene.";
                } else {
                    $prompt .= "{$scene["actors"]} changed position.";
                }
                $prompt .= " $sceneDesc";
                break;
            }
            case "sextalk_speedincrease":
                $prompt = "The Narrator: {$scene["actors"]} sex pace just became faster.";
                break;
            case "sextalk_speeddecrease":
                $prompt = "The Narrator: {$scene["actors"]} sex pace just became slower.";
                break;
            case "sextalk_climax":
                $prompt = "The Narrator: {$GLOBALS["HERIKA_NAME"]} reaches orgasm.";
                break;
            case "sextalk_climaxchastity":
                $prompt = "The Narrator: {$GLOBALS["HERIKA_NAME"]} is frustrated they can't reach orgasm becasue of chastity belt.";
                break;
            case "sextalk_end":
                $prompt = "The Narrator: Participants finished their sex.";
                break;
        }

        $GLOBALS["gameRequest"][3] = $prompt;
    }
    if ($MUST_DIE) {
        error_log("minai: Done procesing custom request");
        die('X-CUSTOM-CLOSE');
    }
}

function GetThirdpartyContext() {
    $db = $GLOBALS['db'];
    $ret = "";
    $currentTime = time();
    // $db->delete("custom_context", "expiresAt < {$currentTime}");
    $rows = $db->fetchAll(
      "SELECT * FROM custom_context WHERE expiresAt > {$currentTime}"
    );
    foreach ($rows as $row) {
        error_log("minai: Inserting third-party context: {$row["eventvalue"]}");
        if (strtolower($GLOBALS["HERIKA_NAME"]) == strtolower($row['npcname']) || strtolower($row['npcname']) == "everyone")
            $ret .= $row["eventvalue"] . "\n";
    }
    return $ret;
}


function RegisterThirdPartyActions() {
    $db = $GLOBALS['db'];
    $currentTime = time();
    // $db->delete("custom_context", "expiresAt < {$currentTime}");
    $rows = $db->fetchAll(
      "SELECT * FROM custom_actions WHERE expiresAt > {$currentTime}"
    );
    foreach ($rows as $row) {
        if ($row["enabled"] == 1) {
            $actionName = $row["actionname"];
            $cmdName = "ExtCmd{$actionName}";
            $actionPrompt = $row["actionprompt"];
            $targetDesc = $row["targetdescription"];
            $targetEnum = explode(",", $row["targetenum"]);
            error_log("minai: Inserting third-party action: {$actionName} ({$actionPrompt})");
            $GLOBALS["F_NAMES"][$cmdName]=$actionName;
            $GLOBALS["F_TRANSLATIONS"][$cmdName]=$actionPrompt;
            $GLOBALS["FUNCTIONS"][] = [
                "name" => $GLOBALS["F_NAMES"][$cmdName],
                "description" => $GLOBALS["F_TRANSLATIONS"][$cmdName],
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "target" => [
                            "type" => "string",
                            "description" => $targetDesc,
                            "enum" => $targetEnum
                        ]
                    ],
                    "required" => ["target"],
                ],
            ];
            $GLOBALS["FUNCRET"][$cmdName]=$GLOBALS["GenericFuncRet"];
            RegisterAction($cmdName);
        }
    }
}
?>
