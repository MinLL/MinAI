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
    CreateActionsTableIfNotExists();
    $MUST_DIE=false;
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["events_to_ignore"]) && in_array($GLOBALS["gameRequest"][0], $GLOBALS["events_to_ignore"])) {
        error_log("minai: Event {$GLOBALS["gameRequest"][0]} in ignore list, blocking.");
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "storecontext") {
        $db = $GLOBALS['db'];
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $modName = $vars[0];
        $eventKey = $vars[1];
        $eventValue = $vars[2];
        $ttl = intval($vars[3]);
        error_log("minai: Storing custom context: {$modName}, {$eventKey}, {$eventValue}, {$ttl}");
        $db->delete("custom_context", "modName='".$db->escape($modName)."' AND eventKey='".$db->escape($eventKey)."'");
        $db->insert(
            'custom_context',
            array(
                'modName' => $db->escape($modName),
                'eventKey' => $db->escape($eventKey),
                'eventValue' => $db->escape($eventValue),
                'expiresAt' => time() + $ttl,
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
        file_put_contents("my_logs.txt", "\n\ncustom integrations: updateThreadsDB!!!\n\n", FILE_APPEND);
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
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "radiant") {
        if (time() > GetLastInput() + $GLOBALS["input_delay_for_radiance"]) {
            // $GLOBALS["HERIKA_NAME"] is npc1
            $GLOBALS["HERIKA_TARGET"] = explode(":", $GLOBALS["gameRequest"][3])[3];
            error_log("minai: Starting radiant dialogue between {$GLOBALS["HERIKA_NAME"]} and {$GLOBALS["HERIKA_TARGET"]}");
            $GLOBALS["PROMPTS"]["radiant"]= [
                "cue"=>[
                    "write dialogue for {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  "
                ], 
                "player_request"=>[    
                    "The Narrator: {$GLOBALS["HERIKA_NAME"]} starts a dialogue with {$GLOBALS["HERIKA_TARGET"]} about a random topic",
                ]
            ];
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
