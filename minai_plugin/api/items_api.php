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
CreateScenariosTableIfNotExists();
CreateItemRelevanceTableIfNotExists();

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
        // Get categories
        $categories = GetItemCategories();
        sendResponse(200, $categories);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'relevance' && isset($_GET['situation'])) {
        // Get relevant items for a situation
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $items = GetRelevantItems($_GET['situation'], $limit);
        sendResponse(200, $items);
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
            $count = ImportItemsFromJson($file_path);
            sendResponse(200, ['message' => "{$count} items imported successfully"]);
        } else {
            sendResponse(400, ['error' => 'No file uploaded']);
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'process_ingame') {
        // Process in-game items
        if (isset($data['items']) && is_array($data['items'])) {
            $count = ProcessInGameItems($data['items']);
            sendResponse(200, ['message' => "{$count} in-game items processed successfully"]);
        } else {
            sendResponse(400, ['error' => 'Invalid items data']);
        }
    } else {
        // Add a new item
        if (isset($data['item_id']) && isset($data['file_name']) && isset($data['name'])) {
            $description = isset($data['description']) ? $data['description'] : '';
            $is_available = isset($data['is_available']) ? $data['is_available'] : true;
            $category = isset($data['category']) ? $data['category'] : null;
            
            if (AddItem($data['item_id'], $data['file_name'], $data['name'], $description, $is_available, $category)) {
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