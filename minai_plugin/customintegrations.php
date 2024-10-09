<?php
// We need access to gameRequest here, but it's not global.
// Impl copied from main.php

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
                    "required" => [],
                ],
            ];
            $GLOBALS["FUNCRET"][$cmdName]=$GLOBALS["GenericFuncRet"];
            RegisterAction($cmdName);
        }
    }
}
?>
