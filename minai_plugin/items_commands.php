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
    
    // Parse the inventory string and collect all formIds and counts
    $inventoryData = [];
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
            
            // Process formId for database lookup
            $processedFormId = ProcessFormId($formId);
            
            // Store both the original and processed IDs along with the count
            $inventoryData[] = [
                'originalFormId' => $formId,
                'processedFormId' => $processedFormId,
                'count' => $count
            ];
        } else {
            minai_log("warn", "Invalid item format in inventory data: " . $item);
        }
    }
    
    if (empty($inventoryData)) {
        minai_log("debug", "No valid items found in inventory for: " . $actorName);
        return $inventory;
    }
    
    // Collect all processedFormIds
    $processedFormIds = [];
    foreach ($inventoryData as $data) {
        $processedFormIds[] = $data['processedFormId'];
    }
    
    // Check cache first
    $uncachedFormIds = [];
    $cachedItems = [];
    
    foreach ($inventoryData as $data) {
        $originalFormId = $data['originalFormId'];
        
        if (isset($GLOBALS['formIdCache'][$originalFormId])) {
            if ($GLOBALS['formIdCache'][$originalFormId]) {
                $cachedItems[$originalFormId] = [
                    'item' => $GLOBALS['formIdCache'][$originalFormId],
                    'count' => $data['count']
                ];
            }
        } else {
            $uncachedFormIds[] = $data['processedFormId'];
        }
    }
    
    // If we have uncached formIds, get them all at once
    if (!empty($uncachedFormIds)) {
        $db = $GLOBALS['db'];
        try {
            $placeholders = [];
            
            foreach ($uncachedFormIds as $id) {
                $placeholders[] = "UPPER(item_id) = UPPER('" . $db->escape($id) . "')";
            }
            
            $query = "SELECT * FROM minai_items WHERE (" . implode(" OR ", $placeholders) . ") AND is_available = TRUE";
            $result = $db->fetchAll($query);
            
            // Create a mapping of processedFormId to item info
            $fetchedItems = [];
            foreach ($result as $item) {
                foreach ($uncachedFormIds as $processedId) {
                    if (strcasecmp($item['item_id'], $processedId) === 0) {
                        $fetchedItems[$processedId] = $item;
                        break;
                    }
                }
            }
            
            // Update cache and add to inventory
            foreach ($inventoryData as $data) {
                $originalFormId = $data['originalFormId'];
                $processedFormId = $data['processedFormId'];
                
                // Skip if already cached
                if (isset($GLOBALS['formIdCache'][$originalFormId])) {
                    continue;
                }
                
                if (isset($fetchedItems[$processedFormId])) {
                    $item = $fetchedItems[$processedFormId];
                    $GLOBALS['formIdCache'][$originalFormId] = $item;
                    
                    $inventory[] = [
                        'formId' => $originalFormId,
                        'name' => $item['name'],
                        'count' => $data['count']
                    ];
                    
                    minai_log("debug", "Added item to inventory: " . $item['name'] . " x" . $data['count']);
                } else {
                    // Cache as null if not found
                    $GLOBALS['formIdCache'][$originalFormId] = null;
                }
            }
        } catch (Exception $e) {
            minai_log("error", "Error fetching items: " . $e->getMessage());
        }
    }
    
    // Add cached items to inventory
    foreach ($cachedItems as $originalFormId => $data) {
        $item = $data['item'];
        $count = $data['count'];
        
        $inventory[] = [
            'formId' => $originalFormId,
            'name' => $item['name'],
            'count' => $count
        ];
        
        minai_log("debug", "Added cached item to inventory: " . $item['name'] . " x" . $count);
    }
    
    minai_log("info", "Retrieved " . count($inventory) . " items for actor: " . $actorName);
    return $inventory;
}

// Helper function to process form ID for database lookup
function ProcessFormId($formId) {
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
    return '0x' . $formId;
}

// Helper function to get item info from formId
function GetItemInfoFromFormId($formId) {
    // Check cache first
    $originalFormId = $formId;
    if (isset($GLOBALS['formIdCache'][$originalFormId])) {
        return $GLOBALS['formIdCache'][$originalFormId];
    }
    
    // Process the formId for database lookup
    $processedFormId = ProcessFormId($formId);
    
    // Use GetItems function from items.php with filters for item_id and is_available
    // Now using exact matching thanks to our updates to GetItems
    $filters = [
        'item_id' => $processedFormId,
        'is_available' => true // Only return available items
    ];
    
    $items = GetItems($filters);
    $itemInfo = count($items) > 0 ? $items[0] : null;
    
    // Store in cache (null results are also cached to avoid repeated lookups)
    $GLOBALS['formIdCache'][$originalFormId] = $itemInfo;
    
    return $itemInfo;
}

// Helper function to build inventory list string
function BuildInventoryListString($items, $limit = null) {
    if (empty($items)) {
        return "no items";
    }
    
    // Use the global configuration setting if no explicit limit is provided
    if ($limit === null) {
        $limit = isset($GLOBALS['inventory_items_limit']) ? $GLOBALS['inventory_items_limit'] : 5;
    }
    
    // Sort by count descending
    usort($items, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // TODO: Future implementation - If use_item_relevancy_scoring is enabled, sort items by relevancy
    // instead of by count. This will require an LLM call to score items based on the current situation.
    // if (isset($GLOBALS['use_item_relevancy_scoring']) && $GLOBALS['use_item_relevancy_scoring']) {
    //     // Implementation for relevancy scoring goes here
    // }
    
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
    // Temporarily disable trade item command
    // RegisterAction("ExtCmdTradeItem");

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