// Global variables
let currentPage = 1;
let itemsPerPage = 10;
let totalPages = 1;
let currentItems = [];
let categories = [];
let itemTypes = [];

// DOM elements
const messageElement = document.getElementById('message');
const itemsTableBody = document.getElementById('items-table-body');
const categoryFilter = document.getElementById('category-filter');
const typeFilter = document.getElementById('type-filter');
const availabilityFilter = document.getElementById('availability-filter');
const sortBySelect = document.getElementById('sort-by');
const sortOrderSelect = document.getElementById('sort-order');
const searchInput = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const resetButton = document.getElementById('reset-button');
const prevPageButton = document.getElementById('prev-page');
const nextPageButton = document.getElementById('next-page');
const pageInfoElement = document.getElementById('page-info');
const addItemForm = document.getElementById('add-item-form');
const importForm = document.getElementById('import-form');
const exportButton = document.getElementById('export-button');
const exportCategorySelect = document.getElementById('export-category');
const exportTypeSelect = document.getElementById('export-type');
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set up tab switching
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to current tab and content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Load items
    loadItems();
    
    // Load categories and item types
    loadCategories();
    loadItemTypes();
    
    // Set up event listeners
    setupEventListeners();
});

// Set up event listeners
function setupEventListeners() {
    // Search input (with debounce)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            filterAndDisplayItems();
        }, 300); // Wait 300ms after user stops typing
    });
    
    // Search button
    searchButton.addEventListener('click', function() {
        currentPage = 1;
        filterAndDisplayItems();
    });
    
    // Reset button
    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = '';
        typeFilter.value = '';
        availabilityFilter.value = '';
        sortBySelect.value = 'name';
        sortOrderSelect.value = 'ASC';
        currentPage = 1;
        loadItems();
    });
    
    // Filters and sorting
    categoryFilter.addEventListener('change', function() {
        currentPage = 1;
        loadItems();
    });
    
    typeFilter.addEventListener('change', function() {
        currentPage = 1;
        loadItems();
    });
    
    availabilityFilter.addEventListener('change', function() {
        currentPage = 1;
        loadItems();
    });
    
    sortBySelect.addEventListener('change', function() {
        currentPage = 1;
        loadItems();
    });
    
    sortOrderSelect.addEventListener('change', function() {
        currentPage = 1;
        loadItems();
    });
    
    // Pagination
    prevPageButton.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            filterAndDisplayItems();
        }
    });
    
    nextPageButton.addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            filterAndDisplayItems();
        }
    });
    
    // Add item form
    addItemForm.addEventListener('submit', function(e) {
        e.preventDefault();
        addItem();
    });
    
    // Import form
    importForm.addEventListener('submit', function(e) {
        e.preventDefault();
        importItems();
    });
    
    // Export button
    exportButton.addEventListener('click', function() {
        exportItems();
    });
    
    // Reset database button
    const resetDbButton = document.getElementById('reset-db-button');
    if (resetDbButton) {
        resetDbButton.addEventListener('click', function() {
            resetDatabase();
        });
    }
}

// Load items from the API
function loadItems() {
    // Show loading message
    showMessage('Loading items...', 'info');
    
    // Build query parameters
    const params = new URLSearchParams();
    
    // Add category filter if selected
    if (categoryFilter.value) {
        params.append('category', categoryFilter.value);
    }
    
    // Add type filter if selected
    if (typeFilter.value) {
        params.append('item_type', typeFilter.value);
    }
    
    // Add availability filter if selected
    if (availabilityFilter.value) {
        params.append('is_available', availabilityFilter.value);
    }
    
    // Add sorting
    params.append('sort_by', sortBySelect.value);
    params.append('sort_order', sortOrderSelect.value);
    
    // Make API request
    fetch(`api/items_api.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load items');
            }
            return response.json();
        })
        .then(data => {
            currentItems = data;
            filterAndDisplayItems();
            hideMessage();
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Filter and display items based on search term
function filterAndDisplayItems() {
    let filteredItems = [...currentItems];
    
    // Apply search filter if there's a search term
    const searchTerm = searchInput.value.trim().toLowerCase();
    if (searchTerm) {
        filteredItems = filteredItems.filter(item => {
            return (
                (item.name && item.name.toLowerCase().includes(searchTerm)) ||
                (item.item_id && item.item_id.toLowerCase().includes(searchTerm)) ||
                (item.file_name && item.file_name.toLowerCase().includes(searchTerm)) ||
                (item.description && item.description.toLowerCase().includes(searchTerm)) ||
                (item.category && item.category.toLowerCase().includes(searchTerm)) ||
                (item.item_type && item.item_type.toLowerCase().includes(searchTerm)) ||
                (item.mod_index && item.mod_index.toLowerCase().includes(searchTerm))
            );
        });
    }
    
    // Update pagination for filtered results
    totalPages = Math.ceil(filteredItems.length / itemsPerPage);
    currentPage = Math.min(currentPage, totalPages);
    if (currentPage < 1) currentPage = 1;
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredItems.length);
    
    // Update page info
    pageInfoElement.textContent = `Page ${currentPage} of ${totalPages || 1}`;
    
    // Enable/disable pagination buttons
    prevPageButton.disabled = currentPage === 1;
    nextPageButton.disabled = currentPage >= totalPages;
    
    // Clear the table
    itemsTableBody.innerHTML = '';
    
    // Display filtered items for current page
    for (let i = startIndex; i < endIndex; i++) {
        const item = filteredItems[i];
        
        // Create table row
        const row = document.createElement('tr');
        
        // Add cells
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.item_id}</td>
            <td>${item.file_name}</td>
            <td>${item.name}</td>
            <td>${item.description || ''}</td>
            <td>${item.item_type || 'Item'}</td>
            <td>${item.category || ''}</td>
            <td>${item.mod_index || ''}</td>
            <td>${item.is_available ? 'Yes' : 'No'}</td>
            <td>${formatDate(item.last_seen)}</td>
            <td>${formatDate(item.created_at)}</td>
            <td>
                <button class="edit-button" data-id="${item.id}">Edit</button>
                <button class="delete-button delete" data-id="${item.id}">Delete</button>
            </td>
        `;
        
        // Add row to table
        itemsTableBody.appendChild(row);
    }
    
    // Add event listeners to edit and delete buttons
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            editItem(id);
        });
    });
    
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            deleteItem(id);
        });
    });
}

// Load categories from the API
function loadCategories() {
    fetch('api/items_api.php?action=categories')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load categories');
            }
            return response.json();
        })
        .then(data => {
            categories = data;
            
            // Clear existing options (except the first one)
            while (categoryFilter.options.length > 1) {
                categoryFilter.remove(1);
            }
            
            while (exportCategorySelect.options.length > 1) {
                exportCategorySelect.remove(1);
            }
            
            // Add categories to filters
            categories.forEach(category => {
                const option1 = document.createElement('option');
                option1.value = category.category;
                option1.textContent = `${category.category} (${category.count})`;
                categoryFilter.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = category.category;
                option2.textContent = `${category.category} (${category.count})`;
                exportCategorySelect.appendChild(option2);
            });
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Load item types from the API
function loadItemTypes() {
    fetch('api/items_api.php?action=types')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load item types');
            }
            return response.json();
        })
        .then(data => {
            itemTypes = data;
            
            // Clear existing options (except the first one)
            while (typeFilter.options.length > 1) {
                typeFilter.remove(1);
            }
            
            while (exportTypeSelect.options.length > 1) {
                exportTypeSelect.remove(1);
            }
            
            // Add item types to filters
            itemTypes.forEach(type => {
                const option1 = document.createElement('option');
                option1.value = type.item_type;
                option1.textContent = type.item_type;
                typeFilter.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = type.item_type;
                option2.textContent = type.item_type;
                exportTypeSelect.appendChild(option2);
            });
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Add a new item
function addItem() {
    // Get form data
    const formData = new FormData(addItemForm);
    const data = {};
    
    // Convert FormData to object
    for (const [key, value] of formData.entries()) {
        if (key === 'is_available') {
            data[key] = true;
        } else {
            data[key] = value;
        }
    }
    
    // If is_available checkbox is not checked, set it to false
    if (!formData.has('is_available')) {
        data.is_available = false;
    }
    
    // Make API request
    fetch('api/items_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to add item');
            }
            return response.json();
        })
        .then(data => {
            showMessage(data.message, 'success');
            addItemForm.reset();
            // Set default value for item_type back to "Item"
            document.getElementById('item-type').value = 'Item';
            loadItems();
            loadCategories();
            loadItemTypes();
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Edit an item
function editItem(id) {
    // Find the item
    const item = currentItems.find(item => item.id == id);
    
    if (!item) {
        showMessage('Item not found', 'error');
        return;
    }
    
    // Create a modal dialog for editing
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Item</h2>
            <form id="edit-item-form">
                <div class="form-group">
                    <label for="edit-item-id">Item ID (format: 0x??012345)</label>
                    <input type="text" id="edit-item-id" name="item_id" value="${item.item_id}" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-file-name">File Name</label>
                    <input type="text" id="edit-file-name" name="file_name" value="${item.file_name}" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-name">Item Name</label>
                    <input type="text" id="edit-item-name" name="name" value="${item.name}" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-description">Description</label>
                    <textarea id="edit-item-description" name="description" rows="4">${item.description || ''}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-type">Item Type</label>
                    <input type="text" id="edit-item-type" name="item_type" value="${item.item_type || 'Item'}">
                </div>
                
                <div class="form-group">
                    <label for="edit-item-category">Category</label>
                    <input type="text" id="edit-item-category" name="category" value="${item.category || ''}">
                </div>
                
                <div class="form-group">
                    <label for="edit-mod-index">Mod Index</label>
                    <input type="text" id="edit-mod-index" name="mod_index" value="${item.mod_index || ''}">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="edit-is-available" name="is_available" ${item.is_available ? 'checked' : ''}>
                    <label for="edit-is-available">Available for use</label>
                </div>
                
                <button type="submit">Update Item</button>
                <button type="button" class="cancel">Cancel</button>
            </form>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show the modal
    modal.style.display = 'block';
    
    // Close button functionality
    modal.querySelector('.close').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    // Cancel button functionality
    modal.querySelector('.cancel').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    // Form submission
    modal.querySelector('#edit-item-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        const data = {};
        
        // Convert FormData to object
        for (const [key, value] of formData.entries()) {
            if (key === 'is_available') {
                data[key] = true;
            } else {
                data[key] = value;
            }
        }
        
        // If is_available checkbox is not checked, set it to false
        if (!formData.has('is_available')) {
            data.is_available = false;
        }
        
        // Make API request
        fetch(`api/items_api.php?id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update item');
                }
                return response.json();
            })
            .then(data => {
                showMessage(data.message, 'success');
                document.body.removeChild(modal);
                loadItems();
                loadCategories();
                loadItemTypes();
            })
            .catch(error => {
                showMessage(error.message, 'error');
            });
    });
}

// Delete an item
function deleteItem(id) {
    // Confirm deletion
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }
    
    // Make API request
    fetch(`api/items_api.php?id=${id}`, {
        method: 'DELETE'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete item');
            }
            return response.json();
        })
        .then(data => {
            showMessage(data.message, 'success');
            loadItems();
            loadCategories();
            loadItemTypes();
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Import items
function importItems() {
    // Get file input
    const fileInput = document.getElementById('import-file');
    
    if (!fileInput.files.length) {
        showMessage('Please select a file to import', 'error');
        return;
    }
    
    // Create FormData
    const formData = new FormData();
    formData.append('import_file', fileInput.files[0]);
    
    // Make API request
    fetch('api/items_api.php?action=import', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to import items');
            }
            return response.json();
        })
        .then(data => {
            showMessage(data.message, 'success');
            loadItems();
            loadCategories();
            loadItemTypes();
            fileInput.value = '';
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Export items
function exportItems() {
    // Build query parameters
    const params = new URLSearchParams();
    
    // Add category filter if selected
    if (exportCategorySelect.value) {
        params.append('category', exportCategorySelect.value);
    }
    
    // Add type filter if selected
    if (exportTypeSelect.value) {
        params.append('item_type', exportTypeSelect.value);
    }
    
    // Make API request
    fetch(`api/items_api.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to export items');
            }
            return response.json();
        })
        .then(data => {
            // Create a JSON file
            const json = JSON.stringify(data, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            // Create a download link
            const a = document.createElement('a');
            a.href = url;
            a.download = 'minai_items.json';
            a.click();
            
            // Clean up
            URL.revokeObjectURL(url);
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
}

// Format date to a more readable format
function formatDate(dateString) {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid Date';
    
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

// Show a message
function showMessage(message, type) {
    messageElement.textContent = message;
    messageElement.className = `message ${type}`;
    messageElement.style.display = 'block';
}

// Hide the message
function hideMessage() {
    messageElement.style.display = 'none';
}

// Add CSS for the modal
const style = document.createElement('style');
style.textContent = `
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
    }
    
    .modal-content {
        background-color: var(--background-light);
        margin: 10% auto;
        padding: 20px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        width: 80%;
        max-width: 600px;
        position: relative;
    }
    
    .close {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
`;
document.head.appendChild(style);

// Reset database function
function resetDatabase() {
    // Confirm reset
    if (!confirm('WARNING: This will delete ALL items from the database. This action CANNOT be undone. Are you absolutely sure?')) {
        return;
    }
    
    // Make API request
    fetch(`api/items_api.php?action=reset`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to reset database');
            }
            return response.json();
        })
        .then(data => {
            showMessage(data.message, 'success');
            // Reload items and filters
            loadItems();
            loadCategories();
            loadItemTypes();
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
} 