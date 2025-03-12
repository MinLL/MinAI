<?php
// We need access to gameRequest here, but it's not global.
// Impl copied from main.php

require_once("util.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."updateThreadsDB.php");




function ProcessIntegrations() {
    if (isset($GLOBALS["gameRequest"])) {
        minai_log("info", "Processing request: " . json_encode($GLOBALS["gameRequest"]));
    }
    // Handle allowing third party mods to register things with the context system
    $MUST_DIE=false;
    if (isset($GLOBALS["use_defeat"]) && $GLOBALS["use_defeat"] && IsModEnabled("SexlabDefeat")) {
        $GLOBALS["events_to_ignore"][] = "combatend";
        $GLOBALS["events_to_ignore"][] = "combatendmighty";
    }
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["events_to_ignore"]) && in_array($GLOBALS["gameRequest"][0], $GLOBALS["events_to_ignore"])) {
        minai_log("info", "Event {$GLOBALS["gameRequest"][0]} in ignore list, blocking.");
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_init") {
        // This is sent once by the SKSE plugin when the game is loaded. Do our initialization here.
        minai_log("info", "Initializing");
        DropThreadsTableIfExists();
        InitiateDBTables();
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
        minai_log("info", "Storing custom context: {$modName}, {$eventKey}, {$eventValue}, {$ttl}");
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
        $npcName = $vars[6];
        minai_log("info", "Registering custom action: {$actionName}, {$actionPrompt}, {$enabled}, {$ttl}");
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
                'targetEnum' => $db->escape($targetEnum),
                'npcName' => $db->escape($npcName)
            )
        );
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "updatethreadsdb") {
        updateThreadsDB();
        $MUST_DIE=true;
    }
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) =="npc_talk") {
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $tmp = explode(":", $vars[0]);
        $speaker = $tmp[sizeof($tmp)-1];
        $target = $vars[1];
        $message = $vars[2];
        minai_log("info", "Processing NPC request ({$speaker} => {$target}: {$message})");
        $GLOBALS["PROMPTS"]["npc_talk"]= [
            "cue"=>[
                "write dialogue for {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  "
            ], 
            "player_request"=>[
                "{$speaker}: {$message} (Talking to {$target})"
            ]
        ];
    }
    if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["radiant", "radiantsearchinghostile", "radiantsearchingfriend", "radiantcombathostile", "radiantcombatfriend", "minai_force_rechat"])) {
        if (strtolower($GLOBALS["gameRequest"][0]) == "minai_force_rechat" || time() > GetLastInput() + $GLOBALS["input_delay_for_radiance"]) {
            // Block rechat/radiant during sex scenes
            if (IsSexActive()) {
                minai_log("info", "Blocking rechat/radiant during sex scene");
                $MUST_DIE = true;
            }
            else if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
                // Fail safe
                minai_log("info", "WARNING - Radiant dialogue started with narrator");
                $MUST_DIE = true;
            }
            else {
                // $GLOBALS["HERIKA_NAME"] is npc1
                // Fix parsing of target NPC name from radiant event
                $requestData = $GLOBALS["gameRequest"][3] ?? '';
                if (strpos($requestData, 'Context location:') !== false) {
                    // Parse target from format "(Context location: )Min:NPCName"
                    $parts = explode(':', $requestData);
                    $GLOBALS["HERIKA_TARGET"] = trim(end($parts));
                } else {
                    // Fallback to original parsing for other formats
                    $GLOBALS["HERIKA_TARGET"] = explode(":", $requestData)[3] ?? '';
                }

                if (empty(trim($GLOBALS["HERIKA_TARGET"]))) {
                    minai_log("info", "Blocking radiant/rechat - target is empty or invalid");
                    $MUST_DIE = true;
                }
                else if ($GLOBALS["HERIKA_TARGET"] == $GLOBALS["HERIKA_NAME"]) {
                    minai_log("info", "Blocking radiant/rechat - source and target are the same NPC");
                    $MUST_DIE = true;
                }
                else {
                    if ($GLOBALS["HERIKA_TARGET"] == $GLOBALS["HERIKA_NAME"])
                        $GLOBALS["HERIKA_TARGET"] = $GLOBALS["PLAYER_NAME"];
                    minai_log("info", "Starting {$GLOBALS["gameRequest"][0]} dialogue between {$GLOBALS["HERIKA_NAME"]} and {$GLOBALS["HERIKA_TARGET"]}");
                    StoreRadiantActors($GLOBALS["HERIKA_TARGET"], $GLOBALS["HERIKA_NAME"]);
                    $GLOBALS["target"] = $GLOBALS["HERIKA_TARGET"];
                }
            }
        }
        else {
            // Avoid race condition where we send input, the server starts to process the request, and then
            // a radiant request comes in 
            minai_log("info", "Not starting radiance: Input was too recent");
            $MUST_DIE=true;
        }
    }
    if (in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s","rechat","bored", "radiant", "minai_force_rechat"])) {
        if (!in_array($GLOBALS["gameRequest"][0], ["radiant", "rechat", "minai_force_rechat"]))
            ClearRadiantActors();
        // minai_log("info", "Setting lastInput time.");
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

    // Handle singing events
    /* if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_sing") {
        // Set up singing context
        $GLOBALS["ORIGINAL_HERIKA_NAME"] = $GLOBALS["HERIKA_NAME"];
        // Intended for use with the "Self Narrator" functionality
        $GLOBALS["HERIKA_NAME"] = "The Narrator";
        SetNarratorProfile();
        $GLOBALS["HERIKA_NAME"] = $GLOBALS["PLAYER_NAME"];
        $GLOBALS["PROMPTS"]["minai_sing"] = [
            "cue" => [
                "write a musical response as {$GLOBALS["PLAYER_NAME"]}. Be creative and match the mood of the scene."
            ],
            "player_request"=>[    
                "{$GLOBALS["PLAYER_NAME"]} begins singing a song: {$GLOBALS["gamerequest"][3]}.",
            ]
        ];
        
        // Add singing-specific personality traits
        $GLOBALS["HERIKA_PERS"] .= "\nWhen singing, you should be musical and poetic. Format your responses as song lyrics or poetry.\n";
        
        // Force response to be musical
        $GLOBALS["TEMPLATE_DIALOG"] = "Respond with song lyrics or a musical performance.";
        }*/

    // Handle narrator talk events
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_narrator_talk") {
        SetEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator", false);
        $GLOBALS["ORIGINAL_HERIKA_NAME"] = $GLOBALS["HERIKA_NAME"];
        $GLOBALS["HERIKA_NAME"] = "The Narrator";
        SetNarratorProfile();
        
        SetNarratorPrompts(isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"]);
    }

    if (isset($GLOBALS["gameRequest"]) && strpos($GLOBALS["gameRequest"][0], "minai_tntr_") === 0) {
        if (ShouldBlockTNTREvent($GLOBALS["gameRequest"][0])) {
            minai_log("info", "Blocking TNTR event: {$GLOBALS["gameRequest"][0]}");
            $MUST_DIE=true;
        }
    }

    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "storetattoodesc") {
        minai_log("info", "Processing storetattoodesc event");
        
        // Parse the tattoo data from the request
        $data = explode("@", $GLOBALS["gameRequest"][3]);
        if (count($data) >= 2) {
            $actorName = $data[0];
            $tattooData = $data[1];
            
            // Store the actor's tattoo data
            StoreTattooData($actorName, $tattooData);
            
            minai_log("info", "Stored tattoo data for " . $actorName);
        } else {
            minai_log("error", "Invalid tattoo data format");
        }
        
        $MUST_DIE=true;
    }

    // Add handling for minai_combatenddefeat
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_combatenddefeat") {
        // Store the defeat timestamp
        $db = $GLOBALS['db'];
        $id = "_minai_PLAYER//lastDefeat";
        $db->delete("conf_opts", "id='{$id}'");
        $db->insert(
            'conf_opts',
            array(
                'id' => $id,
                'value' => time()
            )
        );
        minai_log("info", "Player was defeated in combat, blocking Attack command for 300 seconds");
        $MUST_DIE=true;
    }

    if ($MUST_DIE) {
        minai_log("info", "Done procesing custom request");
        die('X-CUSTOM-CLOSE');
    }
}

function GetThirdpartyContext() {
    $db = $GLOBALS['db'];
    $ret = "";
    $currentTime = time();
    
	$npcName = $GLOBALS["db"]->escape($GLOBALS["HERIKA_NAME"]);
	$npcName = $GLOBALS["db"]->escape($npcName); // we need to escape twice to catch names with ' in then, like most Khajit names. Probably because the names are escaped before inserting.
	$npcNameLower = strtolower($npcName); // added the same name but in lowercase to be safe, since sometimes Skyrim returns NPC names in all lowercase and those get put into the DB.
	
	$inArray = array("everyone", $npcName, $npcNameLower);
	
	// Add the player name if its not an NPC to NPC conversation
	if (!IsRadiant()) {
		array_push($inArray, $GLOBALS["PLAYER_NAME"]);
	}
	
    $rows = $db->fetchAll(
		"SELECT * FROM custom_context WHERE expiresAt > {$currentTime} AND npcname IN ('" . implode("', '", $inArray) . "')"
	);
    foreach ($rows as $row) {
        minai_log("info", "Inserting third-party context: {$row["eventvalue"]}");
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
        if ($row["enabled"] == 1 && ((strtolower(strtolower($GLOBALS["HERIKA_NAME"])) == strtolower($row['npcname'])
            || (!IsRadiant() && strtolower($GLOBALS["PLAYER_NAME"])) == strtolower($row['npcname'])) 
            || strtolower($row['npcname']) == "everyone")) {
            $actionName = $row["actionname"];
            $cmdName = "ExtCmd{$actionName}";
            $actionPrompt = $row["actionprompt"];
            $targetDesc = $row["targetdescription"];
            $targetEnum = explode(",", $row["targetenum"]);
            minai_log("info", "Inserting third-party action: {$actionName} ({$actionPrompt})");
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

function ShouldBlockTNTREvent($eventName) {
    // Extract source and event from full event name (e.g. "minai_tntr_mimic_triggervoreinstant")
    $parts = explode('_', strtolower($eventName));
    if (count($parts) < 4) return false;
    
    $source = $parts[2];
    $event = $parts[3];
    
    if ($source == "mimic") {
        $blockedEvents = [
            "transvorestage02loop",
            "triggerdie", 
            "triggerattack",
            "triggermimicshake"
        ];
        return in_array($event, $blockedEvents);
    }
    
    if ($source == "deathworm") {
        $blockedEvents = [
            "trigger01"  // Block initial ground trembling event
        ];
        return in_array($event, $blockedEvents);
    }
    
    return false;
}

function StoreTattooData($actorName, $tattooData) {
    $db = $GLOBALS['db'];
    
    try {
        
        // Create the tattoo_description table if it doesn't exist
        $db->execQuery("CREATE TABLE IF NOT EXISTS tattoo_description (
            section TEXT NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            hidden_by TEXT,
            PRIMARY KEY (section, name)
        )");
        
        // Create the actor_tattoos table if it doesn't exist
        $db->execQuery("CREATE TABLE IF NOT EXISTS actor_tattoos (
            actor_name TEXT NOT NULL,
            tattoo_data TEXT NOT NULL,
            updated_at INTEGER NOT NULL,
            PRIMARY KEY (actor_name)
        )");
        
        // Delete any existing data for this actor
        $db->delete("actor_tattoos", "actor_name='" . $db->escape($actorName) . "'");
        
        // Insert the new data
        $db->insert(
            'actor_tattoos',
            array(
                'actor_name' => $db->escape($actorName),
                'tattoo_data' => $db->escape($tattooData),
                'updated_at' => time()
            )
        );
        
        minai_log("info", "Successfully stored tattoo data for " . $actorName . ": " . substr($tattooData, 0, 100) . "...");
        
        // Now process each tattoo to ensure it exists in the tattoo_description table
        $tattoos = explode("~", $tattooData);
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        minai_log("info", "Processing " . count($tattoos) . " tattoo entries");
        
        foreach ($tattoos as $index => $tattoo) {
            if (empty(trim($tattoo))) {
                minai_log("info", "Skipping empty tattoo entry at index " . $index);
                $skippedCount++;
                continue; // Skip empty entries
            }
            
            $fields = explode("&", $tattoo);
            if (count($fields) < 2) {
                minai_log("info", "Skipping tattoo with insufficient fields at index " . $index . ": " . $tattoo);
                $skippedCount++;
                continue; // Need at least section and name
            }
            
            $section = trim($fields[0]);
            $name = trim($fields[1]);
            
            // Skip if section or name is empty
            if (empty($section) || empty($name)) {
                minai_log("info", "Skipping tattoo with empty section or name at index " . $index . ": " . $tattoo);
                $skippedCount++;
                continue;
            }
            
            try {
                // Check if this tattoo already exists in the description table
                $exists = $db->fetchOne(
                    "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
                );
                
                minai_log("info", "Tattoo " . $section . "/" . $name . " exists check result: " . ($exists ? "Yes" : "No"));
                
                // If it doesn't exist, add it with default values
                if (!$exists) {
                    // Create a default description based on the name
                    $defaultDescription = "a " . $name . " tattoo";
                    
                    // Set sensible defaults based on area
                    $defaultHiddenBy = "";
                    if (count($fields) >= 3) {
                        $area = strtoupper(trim($fields[2]));
                        
                        // Face/head area tattoos
                        if (in_array($area, ['FACE', 'HEAD'])) {
                            $defaultHiddenBy = "helmet,zad_DeviousHood";
                        } 
                        // Neck area tattoos
                        else if ($area == 'NECK') {
                            $defaultHiddenBy = "helmet,zad_DeviousCollar,zad_DeviousHood";
                        }
                        // Hand/arm area tattoos
                        else if (in_array($area, ['HAND', 'ARM', 'WRIST'])) {
                            $defaultHiddenBy = "gloves,zad_DeviousGloves,zad_DeviousArmCuffs";
                        }
                        // Foot/leg area tattoos
                        else if (in_array($area, ['FOOT', 'ANKLE'])) {
                            $defaultHiddenBy = "boots,zad_DeviousAnkleShackles";
                        }
                        // Leg area tattoos
                        else if ($area == 'LEG') {
                            $defaultHiddenBy = "boots,wearing_bottom,zad_DeviousLegCuffs,zad_DeviousHobbleSkirt";
                        }
                        // Upper body tattoos
                        else if (in_array($area, ['BODY', 'BACK', 'CHEST'])) {
                            $defaultHiddenBy = "cuirass,wearing_top,zad_DeviousSuit,zad_DeviousPetSuit,zad_DeviousStraitJacket,SLA_ArmorHarness,SLA_ArmorSpendex,SLA_ArmorRubber";
                        }
                        // Breast area tattoos
                        else if ($area == 'BREAST') {
                            $defaultHiddenBy = "cuirass,wearing_top,zad_DeviousBra,SLA_Brabikini,zad_DeviousSuit,zad_DeviousPetSuit";
                        }
                        // Lower body tattoos
                        else if (in_array($area, ['PELVIS', 'BUTT'])) {
                            $defaultHiddenBy = "cuirass,wearing_bottom,zad_DeviousBelt,SLA_Thong,SLA_PantiesNormal,SLA_PantsNormal,SLA_PelvicCurtain,SLA_FullSkirt,SLA_MiniSkirt,SLA_MicroHotPants";
                        }
                        // Thigh area tattoos
                        else if ($area == 'THIGH') {
                            $defaultHiddenBy = "cuirass,wearing_bottom,SLA_PantsNormal,SLA_FullSkirt,SLA_MiniSkirt";
                        }
                        // Default for any other area
                        else {
                            $defaultHiddenBy = "cuirass";
                        }
                    }
                    
                    minai_log("info", "Inserting new tattoo: " . $section . "/" . $name . " with description: " . $defaultDescription . " and hidden_by: " . $defaultHiddenBy);
                    
                    $result = $db->insert(
                        'tattoo_description',
                        array(
                            'section' => $db->escape($section),
                            'name' => $db->escape($name),
                            'description' => $defaultDescription,
                            'hidden_by' => $defaultHiddenBy
                        )
                    );
                    
                    minai_log("info", "Insert result: " . ($result ? "Success" : "Failed"));
                    $processedCount++;
                }
            } catch (Exception $e) {
                minai_log("error", "Error processing tattoo " . $section . "/" . $name . ": " . $e->getMessage());
                $errorCount++;
            }
        }
        
        minai_log("info", "Tattoo processing complete. Processed: " . $processedCount . ", Skipped: " . $skippedCount . ", Errors: " . $errorCount);
        
        // Verify the data was stored correctly
        $storedData = $db->fetchOne(
            "SELECT tattoo_data FROM actor_tattoos WHERE actor_name='" . $db->escape($actorName) . "'"
        );
        
        if ($storedData) {
            if (is_array($storedData)) {
                $storedData = $storedData['tattoo_data'] ?? '';
            }
            minai_log("info", "Verification: Stored data length: " . strlen($storedData) . ", Original data length: " . strlen($tattooData));
        } else {
            minai_log("error", "Verification failed: No data found for actor " . $actorName);
        }
        
        return true;
    } catch (Exception $e) {
        minai_log("error", "Error storing tattoo data: " . $e->getMessage());
        return false;
    }
}

