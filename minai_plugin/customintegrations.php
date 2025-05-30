<?php
// We need access to gameRequest here, but it's not global.
// Impl copied from main.php

require_once("util.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."updateThreadsDB.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."dungeonmaster.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."items.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."utils/narrator_utils.php");

function ProcessIntegrations() {
    if (isset($GLOBALS["gameRequest"])) {
        minai_log("info", "Processing request for {$GLOBALS["HERIKA_NAME"]}: " . json_encode($GLOBALS["gameRequest"]));

        // Deduplication check - only proceed if this isn't a duplicate request
        if (isset($GLOBALS["gameRequest"][0]) && isset($GLOBALS["gameRequest"][3])) {
            $eventType = $GLOBALS["gameRequest"][0];
            $eventData = $GLOBALS["gameRequest"][3];
            
            // Create a hash of the request for efficient storage and comparison
            $requestHash = md5($eventType . '|' . $eventData);
            
            // Check if we've seen this exact request recently
            $lastRequestData = GetActorValue($GLOBALS["PLAYER_NAME"], "LastRequestHash", true);
            $lastRequestTime = intval(GetActorValue($GLOBALS["PLAYER_NAME"], "LastRequestTime", true));
            $currentTime = time();
            
            // If same request and within 5 seconds, consider it a duplicate
            if ($lastRequestData === $requestHash && ($currentTime - $lastRequestTime) < 5) {
                minai_log("info", "Duplicate request detected for {$eventType}, blocking.");
                die('X-CUSTOM-CLOSE');
            }
            
            // Store this request for future deduplication
            SetActorValue($GLOBALS["PLAYER_NAME"], "LastRequestHash", $requestHash);
            SetActorValue($GLOBALS["PLAYER_NAME"], "LastRequestTime", $currentTime);
        }
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
        $db->upsertRowOnConflict(
            'custom_context',
            array(
                'modName' => $modName,
                'eventKey' => $eventKey,
                'eventValue' => $eventValue,
                'expiresAt' => time() + $ttl,
                'npcName' => $npcName,
                'ttl' => $ttl
            ),
            'modname, eventkey'
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
                //"write dialogue for {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  " //'write' prefix lead to double answers, TEMPLATE_DIALOG already has a "Write ..." => the result is "write dialogue ... Write next line"
                "{$GLOBALS["TEMPLATE_DIALOG"]} "
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
        $db->upsertRowOnConflict(
            'conf_opts',
            array(
                'id' => $id,
                'value' => time()
            ),
            'id'
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

    // Handle dungeon master events
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_dungeon_master") {
        // Get the request data
        $requestData = $GLOBALS["gameRequest"][3] ?? '';
        
        // Process the dungeon master event
        ProcessDungeonMasterEvent($requestData);
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
            
            // minai_log("info", "Stored tattoo data for " . $actorName);
        } else {
            minai_log("error", "Invalid tattoo data format");
        }
        
        $MUST_DIE=true;
    }

    // Add handling for minai_storeitem and minai_storeitem_batch
    if (isset($GLOBALS["gameRequest"]) && ($GLOBALS["gameRequest"][0] == "minai_storeitem" || $GLOBALS["gameRequest"][0] == "minai_storeitem_batch")) {
        $start_time = microtime(true);
        minai_log("info", "Processing " . $GLOBALS["gameRequest"][0] . " event");
        
        $data = $GLOBALS["gameRequest"][3];
        $success = true;

        if ($GLOBALS["gameRequest"][0] == "minai_storeitem_batch") {
            // Add batch size limit
            $MAX_BATCH_SIZE = 1024 * 1024; // 1MB
            $MAX_ITEMS_PER_BATCH = 1000;
            
            $dataSize = strlen($data);
            if ($dataSize > $MAX_BATCH_SIZE) {
                minai_log("error", "Batch size ({$dataSize} bytes) exceeds maximum allowed size ({$MAX_BATCH_SIZE} bytes)");
                die('X-CUSTOM-CLOSE');
            }
            
            // First, check if this is an inventory batch
            $parts = explode("@", $data, 3);
            if (count($parts) != 3) {
                minai_log("error", "Invalid batch format");
                die('X-CUSTOM-CLOSE');
            }
            
            $actorName = $parts[0];
            $batchStatus = $parts[1];
            $data = $parts[2];
            
            // Process batched items
            $items = explode("~", $data);
            $itemCount = count($items);
            
            if ($itemCount > $MAX_ITEMS_PER_BATCH) {
                minai_log("error", "Batch item count ({$itemCount}) exceeds maximum allowed items ({$MAX_ITEMS_PER_BATCH})");
                die('X-CUSTOM-CLOSE');
            }
            
            minai_log("info", "Processing batch: {$itemCount} items, {$dataSize} bytes");
            
            $processedItemsCount = 0;
            $skippedItemsCount = 0;
            $invalidItemsCount = 0;
            $inventoryItems = array();
            
            $batch_start = microtime(true);
            
            // For inventory tracking, build a formatted string in the old format
            if ($actorName) {
                $inventoryItems = array();
                
                // Different handling based on batch status
                if ($batchStatus == "initial") {
                    // Clear temporary inventory storage for initial batch
                    SetActorValue($actorName, "Inventory2", "");
                    // minai_log("info", "Initial batch - clearing temporary inventory");
                } 
                else {
                    // For non-initial batches (partial or final), get existing temporary inventory
                    $existingInventory = GetActorValue($actorName, "Inventory2", false, true);
                    
                    // Parse existing inventory if any
                    if (!empty($existingInventory)) {
                        $existingItems = explode("~", $existingInventory);
                        foreach ($existingItems as $item) {
                            $itemParts = explode("&", $item);
                            if (count($itemParts) == 2) {
                                $formId = $itemParts[0];
                                $count = intval($itemParts[1]);
                                $inventoryItems[$formId] = $count;
                            }
                        }
                        
                        $logMessage = ($batchStatus == "final") 
                            ? "Merged with existing temporary inventory for final batch: " 
                            : "Merged with existing temporary inventory: ";
                        // minai_log("info", $logMessage . count($inventoryItems) . " items");
                    } else {
                        // minai_log("debug", "No existing temporary inventory found for {$actorName}");
                    }
                }
            }
            
            // Debug the actual items received
            //minai_log("info", "Received " . count($items) . " items in batch");
            
            // Special handling for empty final batch
            if ($batchStatus == "final" && empty(trim($data))) {
              //  minai_log("info", "Received empty final batch - will finalize inventory");
                
                // Even if this batch is empty, we still need to process the finalization
                // if there's any data in the temporary inventory
                $existingInventory = GetActorValue($actorName, "Inventory2", false, true);
                if (!empty($existingInventory)) {
                    // Parse the existing inventory to create the final inventory items array
                    $existingItems = explode("~", $existingInventory);
                    foreach ($existingItems as $item) {
                        $itemParts = explode("&", $item);
                        if (count($itemParts) == 2) {
                            $formId = $itemParts[0];
                            $count = intval($itemParts[1]);
                            $inventoryItems[$formId] = $count;
                        }
                    }
                    
                    // minai_log("info", "Empty final batch with existing temporary inventory: " . count($inventoryItems) . " items");
                } else {
                    // Empty final batch with no temporary inventory means clear the main inventory
                    SetActorValue($actorName, "Inventory", "");
                    // minai_log("info", "Empty final batch with no temporary inventory - cleared main inventory");
                }
            }
            
            // Process items in the batch if there are any
            if (!empty(trim($data))) {
                foreach ($items as $index => $item) {
                    if (empty(trim($item))) {
                        $skippedItemsCount++;
                        // minai_log("debug", "Skipping empty item at index {$index}");
                        continue; // Skip empty items
                    }
                    
                    $itemData = explode("@", $item);
                    if (count($itemData) >= 6) {  // Ensure we have all required fields
                        $formId = $itemData[0];
                        $modName = $itemData[1];
                        $name = $itemData[2];
                        $formTypeId = $itemData[3];
                        $modIndex = $itemData[4];
                        $count = intval($itemData[5]);
                        
                        // Skip items with empty names or mod names
                        if (empty($name) || empty($modName)) {
                            // minai_log("debug", "Skipping item with empty name or mod at index {$index}: " . substr($item, 0, 30));
                            $skippedItemsCount++;
                            continue;
                        }
                        
                        
                        // minai_log("debug", "Processing item [{$index}]: {$name} (ID: {$formId}, Mod: {$modName}, Count: {$count})");
                        
                        // If this is inventory data, track the item and count
                        if ($actorName && $count > 0) {
                            $inventoryItems[$formId] = $count;
                        }
                        
                        // Translate form type ID to category
                        $category = translateFormTypeToCategory($formTypeId);
                        
                        if (!AddItemWithModIndex($formId, $modName, $name, "", $modIndex, true, $category)) {
                            $success = false;
                            minai_log("error", "Failed to store item data for " . $formId . " from " . $modName);
                        } else {
                            $processedItemsCount++;
                        }
                    } else {
                        $invalidItemsCount++;
                        minai_log("error", "Invalid item data format at index {$index}, fields count: " . count($itemData) . ", data: " . substr($item, 0, 50));
                    }
                }
            }
            
            $batch_end = microtime(true);
            $batch_duration = $batch_end - $batch_start;
            
            // Log performance metrics
            minai_log("info", "Batch processing metrics:");
            minai_log("info", "- Total items: {$itemCount}");
            minai_log("info", "- Processed: {$processedItemsCount}");
            minai_log("info", "- Skipped: {$skippedItemsCount}");
            minai_log("info", "- Invalid: {$invalidItemsCount}");
            minai_log("info", "- Processing time: {$batch_duration} seconds");
            minai_log("info", "- Average time per item: " . ($batch_duration / max(1, $processedItemsCount)) . " seconds");
            
            if ($actorName && !empty($inventoryItems)) {
                $inv_start = microtime(true);
                
                // Convert to the format expected by the database
                $inventoryStr = implode("~", array_map(
                    function($formId, $count) { 
                        return $formId . "&" . $count; 
                    },
                    array_keys($inventoryItems),
                    $inventoryItems
                ));
                
                // Store to appropriate location based on batch status
                if ($batchStatus == "final") {
                    SetActorValue($actorName, "Inventory", $inventoryStr);
                    SetActorValue($actorName, "Inventory2", "");
                } else {
                    SetActorValue($actorName, "Inventory2", $inventoryStr);
                }
                
                $inv_duration = microtime(true) - $inv_start;
                minai_log("info", "Inventory storage time: {$inv_duration} seconds");
            }
        } else {
            // Process single item
            $itemData = explode("@", $data);
            if (count($itemData) >= 5) {  // Updated to require mod_index
                $formId = $itemData[0];
                $modName = $itemData[1];
                $name = $itemData[2];
                $formTypeId = $itemData[3];
                $modIndex = $itemData[4];
                $description = isset($itemData[5]) ? $itemData[5] : "";
                
                // Translate form type ID to category
                $category = translateFormTypeToCategory($formTypeId);
                
                $success = AddItemWithModIndex($formId, $modName, $name, $description, $modIndex, true, $category);
            } else {
                $success = false;
                minai_log("error", "Invalid item data format, missing required fields");
            }
        }
        
        $total_duration = microtime(true) - $start_time;
        minai_log("info", "Total batch processing time: {$total_duration} seconds");
        
        if (!$success) {
            minai_log("error", "One or more items failed to store");
        }
        
        $MUST_DIE=true;
    }

    // Add handling for minai_clearinventory
    // There's a race condition inbetween the inventory being cleared and new inventory being stored
    // I don't think it's a huge deal though, so leaving it for now.
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_clearinventory") {
        minai_log("info", "Processing minai_clearinventory event");
        
        $actorName = $GLOBALS["gameRequest"][3];
        if ($actorName) {
            // Clear both the main and temporary inventory
            SetActorValue($actorName, "Inventory", "");
            SetActorValue($actorName, "Inventory2", "");
            minai_log("info", "Cleared inventory for actor: " . $actorName);
        } else {
            minai_log("error", "No actor name provided for inventory clear");
        }
        
        $MUST_DIE=true;
    }

    // Add handling for minai_combatenddefeat
    if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_combatenddefeat") {
        // Store the defeat timestamp
        $db = $GLOBALS['db'];
        $id = "_minai_PLAYER//lastDefeat";
        $db->upsertRowOnConflict(
            'conf_opts',
            array(
                'id' => $id,
                'value' => time()
            ),
            'id'
        );
        minai_log("info", "Player was defeated in combat, blocking Attack command for 300 seconds");
        $MUST_DIE=true;
    }

    if ($MUST_DIE) {
        minai_log("info", "Done procesing custom request");
        die('X-CUSTOM-CLOSE');
    }
}

/**
 * Translates Skyrim form type ID to a category name for items
 * 
 * @param int $formTypeId The numeric form type ID from Skyrim
 * @return string The corresponding category name
 */
function translateFormTypeToCategory($formTypeId) {
    // Convert string to int if needed
    $formTypeId = intval($formTypeId);
    
    // Form type to category mapping based on Skyrim's form types
    switch ($formTypeId) {
        case 26: return "Armor";
        case 27: return "Book";
        case 30: return "Ingredient";
        case 32: return "Misc";
        case 33: return "Apparatus";
        case 41: return "Weapon";
        case 42: return "Ammo";
        case 45: return "Key";
        case 46: return "Potion";
        case 48: return "Note";
        case 49: return "Constructible";
        case 52: return "SoulGem";
        case 23: return "Scroll";
        case 24: return "Activator";
        case 28: return "Container";
        case 29: return "Door";
        case 31: return "Light";
        case 34: return "Static";
        case 39: return "Flora";
        case 40: return "Furniture";
        case 43: return "NPC";
        default: return "Other";
    }
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
            
            directRegisterAction(
                $cmdName, 
                $actionName, 
                $actionPrompt,
                true
            );
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
        
        // Upsert the new data
        $db->upsertRowOnConflict(
            'actor_tattoos',
            array(
                'actor_name' => $actorName,
                'tattoo_data' => $tattooData,
                'updated_at' => time()
            ),
            'actor_name'
        );
        
        // minai_log("info", "Successfully stored tattoo data for " . $actorName . ": " . substr($tattooData, 0, 100) . "...");
        
        // Now process each tattoo to ensure it exists in the tattoo_description table
        $tattoos = explode("~", $tattooData);
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        // minai_log("info", "Processing " . count($tattoos) . " tattoo entries");
        
        foreach ($tattoos as $index => $tattoo) {
            if (empty(trim($tattoo))) {
                // minai_log("info", "Skipping empty tattoo entry at index " . $index);
                $skippedCount++;
                continue; // Skip empty entries
            }
            
            $fields = explode("&", $tattoo);
            if (count($fields) < 2) {
                // minai_log("info", "Skipping tattoo with insufficient fields at index " . $index . ": " . $tattoo);
                $skippedCount++;
                continue; // Need at least section and name
            }
            
            $section = trim($fields[0]);
            $name = trim($fields[1]);
            
            // Skip if section or name is empty
            if (empty($section) || empty($name)) {
                // minai_log("info", "Skipping tattoo with empty section or name at index " . $index . ": " . $tattoo);
                $skippedCount++;
                continue;
            }
            
            try {
                // Check if this tattoo already exists in the description table
                $exists = $db->fetchOne(
                    "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
                );
                
                // minai_log("info", "Tattoo " . $section . "/" . $name . " exists check result: " . ($exists ? "Yes" : "No"));
                
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
                    
                    // minai_log("info", "Inserting new tattoo: " . $section . "/" . $name . " with description: " . $defaultDescription . " and hidden_by: " . $defaultHiddenBy);
                    
                    $result = $db->insert(
                        'tattoo_description',
                        array(
                            'section' => $db->escape($section),
                            'name' => $db->escape($name),
                            'description' => $defaultDescription,
                            'hidden_by' => $defaultHiddenBy
                        )
                    );
                    
                    // minai_log("info", "Insert result: " . ($result ? "Success" : "Failed"));
                    $processedCount++;
                }
            } catch (Exception $e) {
                minai_log("error", "Error processing tattoo " . $section . "/" . $name . ": " . $e->getMessage());
                $errorCount++;
            }
        }
        
        // minai_log("info", "Tattoo processing complete. Processed: " . $processedCount . ", Skipped: " . $skippedCount . ", Errors: " . $errorCount);
        
        // Verify the data was stored correctly
        $storedData = $db->fetchOne(
            "SELECT tattoo_data FROM actor_tattoos WHERE actor_name='" . $db->escape($actorName) . "'"
        );
        
        if ($storedData) {
            if (is_array($storedData)) {
                $storedData = $storedData['tattoo_data'] ?? '';
            }
            // minai_log("info", "Verification: Stored data length: " . strlen($storedData) . ", Original data length: " . strlen($tattooData));
        } else {
            minai_log("error", "Verification failed: No data found for actor " . $actorName);
        }
        
        return true;
    } catch (Exception $e) {
        minai_log("error", "Error storing tattoo data: " . $e->getMessage());
        return false;
    }
}
