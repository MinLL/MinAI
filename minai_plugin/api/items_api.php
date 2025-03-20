<?php
// Set headers first
header('Content-Type: application/json');

// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");

// Initialize database first
$db = new sql();
$GLOBALS["db"] = $db;

// Now include other files that need database access
require_once("../logger.php");
require_once("../db_utils.php");
require_once("../util.php");
require_once("../items.php");

// Initialize database schema
InitiateDBTables();

// Handle request based on method
$method = $_SERVER['REQUEST_METHOD'];
try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        case 'PUT':
            handlePutRequest();
            break;
        case 'DELETE':
            handleDeleteRequest();
            break;
        default:
            sendResponse(405, ['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    minai_log("error", "API Error: " . $e->getMessage());
    sendResponse(500, ['error' => 'Internal server error']);
}

/**
 * Handle GET requests for item operations
 */
function handleGetRequest() {
    if (isset($_GET['id'])) {
        handleGetSingleItem();
    } elseif (isset($_GET['action'])) {
        handleGetAction();
    } else {
        handleGetItemsList();
    }
}

/**
 * Handle retrieval of a single item
 */
function handleGetSingleItem() {
    $item = GetItemById($_GET['id']);
    if ($item) {
        sendResponse(200, $item);
    } else {
        sendResponse(404, ['error' => 'Item not found']);
    }
}

/**
 * Handle special GET actions (categories, types, reset)
 */
function handleGetAction() {
    $db = $GLOBALS['db'];
    
    switch ($_GET['action']) {
        case 'categories':
            try {
                $categories = $db->fetchAll("SELECT category, COUNT(*) as count 
                                           FROM minai_items 
                                           WHERE category IS NOT NULL 
                                           GROUP BY category 
                                           ORDER BY category");
                sendResponse(200, $categories);
            } catch (Exception $e) {
                minai_log("error", "Failed to retrieve categories: " . $e->getMessage());
                sendResponse(500, ['error' => 'Failed to retrieve categories']);
            }
            break;

        case 'types':
            try {
                $types = $db->fetchAll("SELECT DISTINCT item_type 
                                      FROM minai_items 
                                      WHERE item_type IS NOT NULL 
                                      ORDER BY item_type");
                sendResponse(200, $types);
            } catch (Exception $e) {
                minai_log("error", "Failed to retrieve item types: " . $e->getMessage());
                sendResponse(500, ['error' => 'Failed to retrieve item types']);
            }
            break;

        case 'toggle_visibility':
            try {
                if (!isset($_GET['id'])) {
                    sendResponse(400, ['error' => 'Missing item ID']);
                }
                
                $id = $db->escape($_GET['id']);
                $currentState = $db->fetchAll("SELECT is_available FROM minai_items WHERE id = '{$id}'");
                
                if (empty($currentState)) {
                    sendResponse(404, ['error' => 'Item not found']);
                }
                
                $newState = !($currentState[0]['is_available'] == 't' || $currentState[0]['is_available'] == '1' || $currentState[0]['is_available'] === true);
                
                $db->execQuery("UPDATE minai_items SET is_available = " . ($newState ? 'TRUE' : 'FALSE') . " WHERE id = '{$id}'");
                
                sendResponse(200, ['status' => 'success', 'is_available' => $newState]);
            } catch (Exception $e) {
                minai_log("error", "Failed to toggle visibility: " . $e->getMessage());
                sendResponse(500, ['error' => 'Failed to toggle visibility']);
            }
            break;

        case 'reset':
            resetItemsDatabase();
            break;

        default:
            sendResponse(400, ['error' => 'Invalid action']);
    }
}

/**
 * Handle retrieval of filtered items list
 */
function handleGetItemsList() {
    $filters = [
        'item_id' => $_GET['item_id'] ?? null,
        'file_name' => $_GET['file_name'] ?? null,
        'name' => $_GET['name'] ?? null,
        'description' => $_GET['description'] ?? null,
        'category' => $_GET['category'] ?? null,
        'item_type' => $_GET['item_type'] ?? null,
        'mod_index' => $_GET['mod_index'] ?? null
    ];

    // Remove null values
    $filters = array_filter($filters);

    if (isset($_GET['is_available'])) {
        $filters['is_available'] = $_GET['is_available'] === 'true';
    }

    $sort_by = $_GET['sort_by'] ?? 'name';
    $sort_order = $_GET['sort_order'] ?? 'ASC';

    $items = GetItems($filters, $sort_by, $sort_order);
    sendResponse(200, $items);
}

/**
 * Handle POST requests for item operations
 */
function handlePostRequest() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($_GET['action']) && $_GET['action'] === 'import') {
        handleImportItems();
    } else {
        handleAddItem($data);
    }
}

/**
 * Handle import of multiple items from a file
 */
function handleImportItems() {
    if (!isset($_FILES['import_file'])) {
        sendResponse(400, ['error' => 'No file uploaded']);
    }

    try {
        $items = json_decode(file_get_contents($_FILES['import_file']['tmp_name']), true);
        if (!$items || !is_array($items)) {
            sendResponse(400, ['error' => 'Invalid JSON format']);
        }

        $count = importItems($items);
        sendResponse(200, ['message' => "{$count} items imported successfully"]);
    } catch (Exception $e) {
        minai_log("error", "Import error: " . $e->getMessage());
        sendResponse(500, ['error' => 'Error importing items: ' . $e->getMessage()]);
    }
}

/**
 * Import multiple items from array
 */
function importItems($items) {
    $count = 0;
    $db = $GLOBALS['db'];

    foreach ($items as $item) {
        if (!validateItemData($item)) {
            continue;
        }

        if (AddItem(
            $item['item_id'],
            $item['file_name'],
            $item['name'],
            $item['description'] ?? '',
            $item['is_available'] ?? true,
            $item['category'] ?? null
        )) {
            $result = $db->fetchAll("SELECT id FROM minai_items 
                                   WHERE item_id = '{$db->escape($item['item_id'])}' 
                                   AND file_name = '{$db->escape($item['file_name'])}'");
            
            if (!empty($result)) {
                $updateData = [
                    'item_type' => $item['item_type'] ?? 'Item',
                    'mod_index' => $item['mod_index'] ?? null
                ];
                UpdateItem($result[0]['id'], $updateData);
                $count++;
            }
        }
    }
    return $count;
}

/**
 * Handle adding a single item
 */
function handleAddItem($data) {
    if (!validateItemData($data)) {
        sendResponse(400, ['error' => 'Missing required fields']);
    }

    if (AddItem(
        $data['item_id'],
        $data['file_name'],
        $data['name'],
        $data['description'] ?? '',
        $data['is_available'] ?? true,
        $data['category'] ?? null
    )) {
        $db = $GLOBALS['db'];
        $result = $db->fetchAll("SELECT id FROM minai_items 
                               WHERE item_id = '{$db->escape($data['item_id'])}' 
                               AND file_name = '{$db->escape($data['file_name'])}'");

        if (!empty($result)) {
            $updateData = [];
            if (isset($data['item_type'])) $updateData['item_type'] = $data['item_type'];
            if (isset($data['mod_index'])) $updateData['mod_index'] = $data['mod_index'];

            if (!empty($updateData)) {
                UpdateItem($result[0]['id'], $updateData);
            }
        }

        sendResponse(201, ['message' => 'Item added successfully']);
    } else {
        sendResponse(400, ['error' => 'Failed to add item']);
    }
}

/**
 * Validate required item data fields
 */
function validateItemData($data) {
    return isset($data['item_id']) && isset($data['file_name']) && isset($data['name']);
}

/**
 * Handle PUT requests for item updates
 */
function handlePutRequest() {
    if (!isset($_GET['id'])) {
        sendResponse(400, ['success' => false, 'error' => 'Missing item ID']);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'];

    minai_log("debug", "PUT request data for item {$id}: " . print_r($data, true));

    if (isset($data['is_hidden'])) {
        minai_log("debug", "Toggling visibility for item {$id} to " . ($data['is_hidden'] ? 'HIDDEN' : 'VISIBLE'));
    }

    if (UpdateItem($id, $data)) {
        $updatedItem = GetItemById($id);
        
        if ($updatedItem) {
            minai_log("debug", "Item updated successfully. New state: " . print_r($updatedItem, true));
            sendResponse(200, [
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $updatedItem
            ]);
        } else {
            sendResponse(200, [
                'success' => true,
                'message' => 'Item updated successfully, but could not retrieve updated data'
            ]);
        }
    } else {
        minai_log("error", "Failed to update item {$id}");
        sendResponse(400, ['success' => false, 'error' => 'Failed to update item']);
    }
}

/**
 * Handle DELETE requests for item deletion
 */
function handleDeleteRequest() {
    if (!isset($_GET['id'])) {
        sendResponse(400, ['error' => 'Missing item ID']);
    }

    if (DeleteItem($_GET['id'])) {
        sendResponse(200, ['message' => 'Item deleted successfully']);
    } else {
        sendResponse(400, ['error' => 'Failed to delete item']);
    }
}

/**
 * Reset the items database
 */
function resetItemsDatabase() {
    try {
        $GLOBALS['db']->execQuery("DELETE FROM minai_items");
        sendResponse(200, ['message' => 'Items database has been reset successfully']);
    } catch (Exception $e) {
        minai_log("error", "Database reset error: " . $e->getMessage());
        sendResponse(500, ['error' => 'Failed to reset database: ' . $e->getMessage()]);
    }
}

/**
 * Send a JSON response and exit
 * 
 * @param int $status_code HTTP status code
 * @param mixed $data Response data
 */
function sendResponse($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
} 