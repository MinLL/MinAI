<?php
// Set headers first
header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
$GLOBALS["db"] = new sql();
require_once("$pluginPath/logger.php");
require_once("../util.php");
require_once("../items.php");
require_once("$pluginPath/db_utils.php");
// Ensure required tables exist
CreateItemsTableIfNotExists();

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
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
        break;
}

/**
 * Handle GET requests
 */
function handleGetRequest() {
    // Check if we're getting a specific item or a list
    if (isset($_GET['id'])) {
        // Get a specific item
        $item = GetItemById($_GET['id']);
        
        if ($item) {
            sendResponse(200, $item);
        } else {
            sendResponse(404, ['error' => 'Item not found']);
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'categories') {
        // Get distinct categories and count of items in each
        $db = $GLOBALS['db'];
        try {
            $query = "SELECT category, COUNT(*) as count FROM minai_items WHERE category IS NOT NULL GROUP BY category ORDER BY category";
            $categories = $db->fetchAll($query);
            sendResponse(200, $categories);
        } catch (Exception $e) {
            sendResponse(500, ['error' => 'Failed to retrieve categories']);
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'types') {
        // Get distinct item types
        $db = $GLOBALS['db'];
        try {
            $query = "SELECT DISTINCT item_type FROM minai_items WHERE item_type IS NOT NULL ORDER BY item_type";
            $types = $db->fetchAll($query);
            sendResponse(200, $types);
        } catch (Exception $e) {
            sendResponse(500, ['error' => 'Failed to retrieve item types']);
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'reset') {
        // Reset the items database
        resetItemsDatabase();
    } else {
        // Get a list of items with optional filters
        $filters = [];
        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
        $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
        
        // Apply filters if provided
        if (isset($_GET['item_id'])) $filters['item_id'] = $_GET['item_id'];
        if (isset($_GET['file_name'])) $filters['file_name'] = $_GET['file_name'];
        if (isset($_GET['name'])) $filters['name'] = $_GET['name'];
        if (isset($_GET['description'])) $filters['description'] = $_GET['description'];
        if (isset($_GET['category'])) $filters['category'] = $_GET['category'];
        if (isset($_GET['is_available'])) $filters['is_available'] = $_GET['is_available'] === 'true';
        if (isset($_GET['item_type'])) $filters['item_type'] = $_GET['item_type'];
        if (isset($_GET['mod_index'])) $filters['mod_index'] = $_GET['mod_index'];
        
        $items = GetItems($filters, $sort_by, $sort_order);
        sendResponse(200, $items);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest() {
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if we're importing items
    if (isset($_GET['action']) && $_GET['action'] === 'import') {
        if (isset($_FILES['import_file'])) {
            $file_path = $_FILES['import_file']['tmp_name'];
            try {
                // Read JSON file
                $json_data = file_get_contents($file_path);
                $items = json_decode($json_data, true);
                
                if (!$items || !is_array($items)) {
                    sendResponse(400, ['error' => 'Invalid JSON format']);
                    return;
                }
                
                $count = 0;
                foreach ($items as $item) {
                    if (isset($item['item_id']) && isset($item['file_name']) && isset($item['name'])) {
                        $description = isset($item['description']) ? $item['description'] : '';
                        $is_available = isset($item['is_available']) ? $item['is_available'] : true;
                        $category = isset($item['category']) ? $item['category'] : null;
                        $item_type = isset($item['item_type']) ? $item['item_type'] : 'Item';
                        $mod_index = isset($item['mod_index']) ? $item['mod_index'] : null;
                        
                        if (AddItem($item['item_id'], $item['file_name'], $item['name'], $description, $is_available, $category)) {
                            // Update additional fields after adding
                            $db = $GLOBALS['db'];
                            $result = $db->fetchAll("SELECT id FROM minai_items WHERE item_id = '{$db->escape($item['item_id'])}' AND file_name = '{$db->escape($item['file_name'])}'");
                            if (count($result) > 0) {
                                $id = $result[0]['id'];
                                $data = [
                                    'item_type' => $item_type,
                                    'mod_index' => $mod_index
                                ];
                                UpdateItem($id, $data);
                            }
                            $count++;
                        }
                    }
                }
                
                sendResponse(200, ['message' => "{$count} items imported successfully"]);
            } catch (Exception $e) {
                sendResponse(500, ['error' => 'Error importing items: ' . $e->getMessage()]);
            }
        } else {
            sendResponse(400, ['error' => 'No file uploaded']);
        }
    } else {
        // Add a new item
        if (isset($data['item_id']) && isset($data['file_name']) && isset($data['name'])) {
            $description = isset($data['description']) ? $data['description'] : '';
            $is_available = isset($data['is_available']) ? $data['is_available'] : true;
            $category = isset($data['category']) ? $data['category'] : null;
            
            if (AddItem($data['item_id'], $data['file_name'], $data['name'], $description, $is_available, $category)) {
                // Get the ID of the newly added item
                $db = $GLOBALS['db'];
                $result = $db->fetchAll("SELECT id FROM minai_items WHERE item_id = '{$db->escape($data['item_id'])}' AND file_name = '{$db->escape($data['file_name'])}'");
                
                if (count($result) > 0) {
                    $id = $result[0]['id'];
                    
                    // Update additional fields if provided
                    $updateData = [];
                    if (isset($data['item_type'])) $updateData['item_type'] = $data['item_type'];
                    if (isset($data['mod_index'])) $updateData['mod_index'] = $data['mod_index'];
                    
                    if (!empty($updateData)) {
                        UpdateItem($id, $updateData);
                    }
                }
                
                sendResponse(201, ['message' => 'Item added successfully']);
            } else {
                sendResponse(400, ['error' => 'Failed to add item']);
            }
        } else {
            sendResponse(400, ['error' => 'Missing required fields']);
        }
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest() {
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if we have an ID
    if (isset($_GET['id'])) {
        // Update the item
        if (UpdateItem($_GET['id'], $data)) {
            sendResponse(200, ['message' => 'Item updated successfully']);
        } else {
            sendResponse(400, ['error' => 'Failed to update item']);
        }
    } else {
        sendResponse(400, ['error' => 'Missing item ID']);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest() {
    // Check if we have an ID
    if (isset($_GET['id'])) {
        // Delete the item
        if (DeleteItem($_GET['id'])) {
            sendResponse(200, ['message' => 'Item deleted successfully']);
        } else {
            sendResponse(400, ['error' => 'Failed to delete item']);
        }
    } else {
        sendResponse(400, ['error' => 'Missing item ID']);
    }
}

/**
 * Reset items database by deleting all items
 */
function resetItemsDatabase() {
    $db = $GLOBALS['db'];
    
    try {
        // Delete all items
        $db->execQuery("DELETE FROM minai_items");
        
        sendResponse(200, ['message' => 'Items database has been reset successfully']);
    } catch (Exception $e) {
        sendResponse(500, ['error' => 'Failed to reset database: ' . $e->getMessage()]);
    }
}

/**
 * Send a JSON response
 * 
 * @param int $status_code HTTP status code
 * @param mixed $data Data to send
 */
function sendResponse($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
} 