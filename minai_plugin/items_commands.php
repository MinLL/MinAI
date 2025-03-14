<?php

require_once("config.php");
require_once("util.php");
require_once("items.php");


// Global cache for form ID lookups
$GLOBALS['formIdCache'] = [];

// Helper function to get actor inventory
function GetActorInventoryItems($actorName) {
    $inventory = [];
    $inventoryStr = GetActorValue($actorName, "Inventory");
    
    if (empty($inventoryStr)) {
        minai_log("debug", "No inventory data found for actor: " . $actorName);
        return $inventory;
    }
    
    minai_log("debug", "Parsing inventory for " . $actorName . ": " . $inventoryStr);
    
    // Parse the inventory string (format: formId&count~formId&count~...)
    $items = explode("~", $inventoryStr);
    foreach ($items as $item) {
        if (empty($item)) {
            continue;
        }
        
        $parts = explode("&", $item);
        if (count($parts) == 2) {
            $formId = $parts[0];
            $count = (int)$parts[1];
            
            if (empty($formId) || $count <= 0) {
                minai_log("debug", "Skipping invalid item data: " . $item);
                continue;
            }
            
            // Get item name from database
            $itemInfo = GetItemInfoFromFormId($formId);
            if ($itemInfo) {
                $inventory[] = [
                    'formId' => $formId,
                    'name' => $itemInfo['name'],
                    'count' => $count
                ];
                minai_log("debug", "Added item to inventory: " . $itemInfo['name'] . " x" . $count);
            } else {
                minai_log("warn", "Item not found in database for formId: " . $formId);
            }
        } else {
            minai_log("warn", "Invalid item format in inventory data: " . $item);
        }
    }
    
    minai_log("info", "Retrieved " . count($inventory) . " items for actor: " . $actorName);
    return $inventory;
}

// Helper function to get item info from formId
function GetItemInfoFromFormId($formId) {
    $db = $GLOBALS['db'];
    
    try {
        // Check cache first
        $originalFormId = $formId;
        if (isset($GLOBALS['formIdCache'][$originalFormId])) {
            return $GLOBALS['formIdCache'][$originalFormId];
        }
        
        // Format conversion from game format to database format
        // Game format: 0000000F (8 characters)
        // Database format: 0x00000F (ignoring first 2 chars after 0x)
        
        // Remove any 0x prefix if it exists (for robustness)
        if (substr($formId, 0, 2) === '0x') {
            $formId = substr($formId, 2);
        }
        
        // Ignore the first two characters of the game's form ID
        if (strlen($formId) >= 8) {
            $formId = substr($formId, 2);
        }
        
        // Add 0x prefix for database lookup
        $formId = '0x' . $formId;
        
        $formId = $db->escape($formId);
        $query = "SELECT * FROM minai_items WHERE UPPER(item_id) = UPPER('{$formId}')";
        $result = $db->fetchAll($query);
        
        $itemInfo = count($result) > 0 ? $result[0] : null;
        
        // Store in cache (null results are also cached to avoid repeated lookups)
        $GLOBALS['formIdCache'][$originalFormId] = $itemInfo;
        
        return $itemInfo;
    } catch (Exception $e) {
        minai_log("error", "Error in GetItemInfoFromFormId: " . $e->getMessage());
        return null;
    }
}

// Helper function to build inventory list string
function BuildInventoryListString($items, $limit = 5) {
    if (empty($items)) {
        return "no items";
    }
    
    // Sort by count descending
    usort($items, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // Limit items to display
    $displayItems = array_slice($items, 0, $limit);
    
    $itemStrings = [];
    foreach ($displayItems as $item) {
        $itemStrings[] = $item['name'] . " (" . $item['count'] . ")";
    }
    
    $result = implode(", ", $itemStrings);
    
    // Add "and more" if there are more items
    if (count($items) > $limit) {
        $result .= ", and " . (count($items) - $limit) . " more items";
    }
    
    return $result;
}

// Register the new item commands only if target is the player
if (IsPlayer(GetTargetActor())) {
    // Get target and player inventories for command descriptions
    $targetName = $GLOBALS["HERIKA_NAME"];
    $playerName = $GLOBALS["PLAYER_NAME"];
    $targetInventory = GetActorInventoryItems($targetName);
    $playerInventory = GetActorInventoryItems($playerName);
    
    $targetItemsStr = BuildInventoryListString($targetInventory);
    $playerItemsStr = BuildInventoryListString($playerInventory);
    
    RegisterAction("ExtCmdGiveItem");
    RegisterAction("ExtCmdTakeItem");
    RegisterAction("ExtCmdTradeItem");

    // Define GiveItem command
    $GLOBALS["F_NAMES"]["ExtCmdGiveItem"] = "GiveItem";
    $GLOBALS["F_TRANSLATIONS"]["ExtCmdGiveItem"] = "(Target MUST be specified as itemName:count) Give an item or items to {$GLOBALS["PLAYER_NAME"]} (for gifting, payment, quest items, rewards, trading). Available items: {$targetItemsStr}.";
    $GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdGiveItem"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdGiveItem"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "parameter" => [
                    "type" => "string",
                    "description" => "The item name and optionally the count in format 'ItemName:Count'. Use this for giving gifts, payments, quest items, or rewards. Examples: 'Gold:100' (payment), 'Iron Sword' (gift), 'Health Potion:5' (supplies), 'Septim:50' (payment)"
                ]
            ],
            "required" => ["parameter"],
        ],
    ];
    $GLOBALS["FUNCRET"]["ExtCmdGiveItem"] = $GLOBALS["GenericFuncRet"];

    // Define TakeItem command
    $GLOBALS["F_NAMES"]["ExtCmdTakeItem"] = "TakeItem";
    $GLOBALS["F_TRANSLATIONS"]["ExtCmdTakeItem"] = "(Target MUST be specified as itemName:count) Take/receive an item or items from {$GLOBALS["PLAYER_NAME"]} (for receiving payment, gifts, collecting items, quest requirements, trading). Available items: {$playerItemsStr}.";
    $GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdTakeItem"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdTakeItem"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "parameter" => [
                    "type" => "string",
                    "description" => "The item name and optionally the count in format 'ItemName:Count'. Use this for requesting payments, collecting quest items, or receiving goods. Examples: 'Gold:75' (collect payment), 'Iron Ore:10' (purchase resources), 'Health Potion' (request healing supply)"
                ]
            ],
            "required" => ["parameter"],
        ],
    ];
    $GLOBALS["FUNCRET"]["ExtCmdTakeItem"] = $GLOBALS["GenericFuncRet"];

    // Define TradeItem command
    $GLOBALS["F_NAMES"]["ExtCmdTradeItem"] = "TradeItem";
    $GLOBALS["F_TRANSLATIONS"]["ExtCmdTradeItem"] = "Trade items with {$GLOBALS["PLAYER_NAME"]} (for bartering, exchanging goods, selling/buying, fair trades). {$targetName} has: {$targetItemsStr}. {$playerName} has: {$playerItemsStr}";
    $GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdTradeItem"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdTradeItem"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "parameter" => [
                    "type" => "string",
                    "description" => "The items to trade in format 'GiveItem:TakeItem' or with counts 'GiveItem:GiveCount:TakeItem:TakeCount'. Use for balanced exchanges. Examples: 'Health Potion:2:Gold:50' (sell potions), 'Iron Sword:1:Septim:100' (sell weapon), 'Leather:5:Iron Ingot:3' (material exchange)"
                ]
            ],
            "required" => ["parameter"],
        ],
    ];
    $GLOBALS["FUNCRET"]["ExtCmdTradeItem"] = $GLOBALS["GenericFuncRet"];
} 