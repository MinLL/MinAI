// Constants
const ITEMS_PER_PAGE = 10;
const DEBOUNCE_DELAY = 300;

// State Management
const State = {
    items: [],
    currentPage: 1,
    totalPages: 1,
    categories: [],
    itemTypes: [],
    filters: {
        category: '',
        type: '',
        availability: '',
        search: ''
    },
    
    updateFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
        this.currentPage = 1;
    }
};

// API Service
const API = {
    baseUrl: 'api/items_api.php',
    
    async getItems(params) {
        const response = await fetch(`${this.baseUrl}?${params.toString()}`);
        if (!response.ok) throw new Error('Failed to load items');
        return response.json();
    },
    
    async getCategories() {
        const response = await fetch(`${this.baseUrl}?action=categories`);
        if (!response.ok) throw new Error('Failed to load categories');
        return response.json();
    },
    
    async getItemTypes() {
        const response = await fetch(`${this.baseUrl}?action=types`);
        if (!response.ok) throw new Error('Failed to load item types');
        return response.json();
    },
    
    async updateItem(id, data) {
        const response = await fetch(`${this.baseUrl}?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!response.ok) throw new Error('Failed to update item');
        return response.json();
    },
    
    async deleteItem(id) {
        const response = await fetch(`${this.baseUrl}?id=${id}`, {
            method: 'DELETE'
        });
        if (!response.ok) throw new Error('Failed to delete item');
        return response.json();
    },
    
    async importItems(formData) {
        const response = await fetch(`${this.baseUrl}?action=import`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error('Failed to import items');
        return response.json();
    },
    
    async resetDatabase() {
        const response = await fetch(`${this.baseUrl}?action=reset`);
        if (!response.ok) throw new Error('Failed to reset database');
        return response.json();
    }
};

// Utility Functions
const Utils = {
    formatDate(dateString) {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit', 
            minute: '2-digit'
        });
    },
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    getFormData(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            data[key] = key.endsWith('_available') || key.endsWith('_hidden') 
                ? formData.has(key) 
                : value;
        }
        
        return data;
    }
};

// UI Management
const UI = {
    elements: {
        messageElement: document.getElementById('message'),
        itemsTableBody: document.getElementById('items-table-body'),
        categoryFilter: document.getElementById('category-filter'),
        typeFilter: document.getElementById('type-filter'),
        availabilityFilter: document.getElementById('availability-filter'),
        sortBySelect: document.getElementById('sort-by'),
        sortOrderSelect: document.getElementById('sort-order'),
        searchInput: document.getElementById('search-input'),
        searchButton: document.getElementById('search-button'),
        resetButton: document.getElementById('reset-button'),
        prevPageButton: document.getElementById('prev-page'),
        nextPageButton: document.getElementById('next-page'),
        pageInfoElement: document.getElementById('page-info'),
        addItemForm: document.getElementById('add-item-form'),
        importForm: document.getElementById('import-form'),
        exportButton: document.getElementById('export-button'),
        exportCategorySelect: document.getElementById('export-category'),
        exportTypeSelect: document.getElementById('export-type'),
        tabs: document.querySelectorAll('.tab'),
        tabContents: document.querySelectorAll('.tab-content')
    },

    showMessage(message, type) {
        this.elements.messageElement.textContent = message;
        this.elements.messageElement.className = `message ${type}`;
        this.elements.messageElement.style.display = 'block';
    },

    hideMessage() {
        this.elements.messageElement.style.display = 'none';
    },

    updatePagination() {
        this.elements.pageInfoElement.textContent = `Page ${State.currentPage} of ${State.totalPages || 1}`;
        this.elements.prevPageButton.disabled = State.currentPage === 1;
        this.elements.nextPageButton.disabled = State.currentPage >= State.totalPages;
    },

    createModal(content) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = content;
        document.body.appendChild(modal);
        modal.style.display = 'block';
        return modal;
    }
};

// Event Handlers
const EventHandlers = {
    onSearch: Utils.debounce(function() {
        State.currentPage = 1;
        filterAndDisplayItems();
    }, DEBOUNCE_DELAY),
    
    onFilterChange: function() {
        State.currentPage = 1;
        loadItems();
    },
    
    onTabClick: function(event) {
        const tabId = event.target.getAttribute('data-tab');
        UI.elements.tabs.forEach(t => t.classList.remove('active'));
        UI.elements.tabContents.forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
};

// Error Handler
function handleApiError(error, customMessage) {
    console.error(customMessage, error);
    UI.showMessage(`${customMessage}: ${error.message}`, 'error');
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set up tab switching
    UI.elements.tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            UI.elements.tabs.forEach(t => t.classList.remove('active'));
            UI.elements.tabContents.forEach(c => c.classList.remove('active'));
            
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
    UI.elements.searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            State.currentPage = 1;
            filterAndDisplayItems();
        }, 300); // Wait 300ms after user stops typing
    });
    
    // Search button
    UI.elements.searchButton.addEventListener('click', function() {
        State.currentPage = 1;
        filterAndDisplayItems();
    });
    
    // Reset button
    UI.elements.resetButton.addEventListener('click', function() {
        UI.elements.searchInput.value = '';
        UI.elements.categoryFilter.value = '';
        UI.elements.typeFilter.value = '';
        UI.elements.availabilityFilter.value = '';
        UI.elements.sortBySelect.value = 'name';
        UI.elements.sortOrderSelect.value = 'ASC';
        State.currentPage = 1;
        loadItems();
    });
    
    // Filters and sorting
    UI.elements.categoryFilter.addEventListener('change', function() {
        State.currentPage = 1;
        loadItems();
    });
    
    UI.elements.typeFilter.addEventListener('change', function() {
        State.currentPage = 1;
        loadItems();
    });
    
    UI.elements.availabilityFilter.addEventListener('change', function() {
        State.currentPage = 1;
        loadItems();
    });
    
    UI.elements.sortBySelect.addEventListener('change', function() {
        State.currentPage = 1;
        loadItems();
    });
    
    UI.elements.sortOrderSelect.addEventListener('change', function() {
        State.currentPage = 1;
        loadItems();
    });
    
    // Pagination
    UI.elements.prevPageButton.addEventListener('click', function() {
        if (State.currentPage > 1) {
            State.currentPage--;
            filterAndDisplayItems();
        }
    });
    
    UI.elements.nextPageButton.addEventListener('click', function() {
        if (State.currentPage < State.totalPages) {
            State.currentPage++;
            filterAndDisplayItems();
        }
    });
    
    // Add item form
    UI.elements.addItemForm.addEventListener('submit', function(e) {
        e.preventDefault();
        addItem();
    });
    
    // Import form
    UI.elements.importForm.addEventListener('submit', function(e) {
        e.preventDefault();
        importItems();
    });
    
    // Export button
    UI.elements.exportButton.addEventListener('click', function() {
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

// Main functionality
async function loadItems() {
    try {
        UI.showMessage('Loading items...', 'info');
        
        // Build query parameters
        const params = new URLSearchParams();
        
        if (UI.elements.categoryFilter.value) {
            params.append('category', UI.elements.categoryFilter.value);
        }
        
        if (UI.elements.typeFilter.value) {
            params.append('item_type', UI.elements.typeFilter.value);
        }
        
        if (UI.elements.availabilityFilter.value) {
            params.append('is_available', UI.elements.availabilityFilter.value);
        }
        
        params.append('sort_by', UI.elements.sortBySelect.value);
        params.append('sort_order', UI.elements.sortOrderSelect.value);
        params.append('_cache', new Date().getTime());
        
        const data = await API.getItems(params);
        State.items = data;
        console.log("Loaded items data:", State.items);
        
        if (State.items.length > 0) {
            console.log("Sample item is_hidden values:");
            for (let i = 0; i < Math.min(3, State.items.length); i++) {
                console.log(`Item ${State.items[i].id}: is_hidden =`, State.items[i].is_hidden,
                            `(${typeof State.items[i].is_hidden})`);
            }
        }
        
        filterAndDisplayItems();
        UI.hideMessage();
    } catch (error) {
        handleApiError(error, 'Error loading items');
    }
}

function filterAndDisplayItems() {
    let filteredItems = [...State.items];
    
    // Apply search filter if there's a search term
    const searchTerm = UI.elements.searchInput.value.trim().toLowerCase();
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
    
    // Update pagination
    State.totalPages = Math.ceil(filteredItems.length / ITEMS_PER_PAGE);
    State.currentPage = Math.min(State.currentPage, State.totalPages);
    if (State.currentPage < 1) State.currentPage = 1;
    
    // Calculate pagination indices
    const startIndex = (State.currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, filteredItems.length);
    
    // Update UI pagination
    UI.updatePagination();
    
    // Clear the table
    UI.elements.itemsTableBody.innerHTML = '';
    
    // Display filtered items for current page
    for (let i = startIndex; i < endIndex; i++) {
        const item = filteredItems[i];
        displayItem(item);
    }
}

function displayItem(item) {
    const row = document.createElement('tr');
    
    const isHidden = item.is_hidden === true || 
                     item.is_hidden === 't' || 
                     item.is_hidden === 1 || 
                     item.is_hidden === '1' || 
                     item.is_hidden === 'true';
    
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
        <td>${Utils.formatDate(item.last_seen)}</td>
        <td>${Utils.formatDate(item.created_at)}</td>
        <td class="button-cell">
            <div class="button-container">
                <button class="visibility-button icon-button" data-id="${item.id}" data-hidden="${isHidden ? '1' : '0'}" 
                        title="${isHidden ? 'Make visible' : 'Hide item'}">
                    <i class="fas ${isHidden ? 'fa-eye-slash' : 'fa-eye'}"></i>
                </button>
                <button class="edit-button icon-button" data-id="${item.id}" title="Edit item">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-button icon-button delete" data-id="${item.id}" title="Delete item">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    `;
    
    UI.elements.itemsTableBody.appendChild(row);

    // Add event listeners to the buttons
    const visibilityButton = row.querySelector('.visibility-button');
    const editButton = row.querySelector('.edit-button');
    const deleteButton = row.querySelector('.delete-button');

    visibilityButton.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const currentState = this.getAttribute('data-hidden');
        toggleItemHidden(id, currentState);
    });

    editButton.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        editItem(id);
    });

    deleteButton.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        deleteItem(id);
    });
}

async function addItem() {
    try {
        const data = Utils.getFormData(UI.elements.addItemForm);
        
        // Make API request
        const response = await API.updateItem('new', data);
        UI.showMessage(response.message, 'success');
        UI.elements.addItemForm.reset();
        document.getElementById('item-type').value = 'Item';
        
        // Reload data
        await Promise.all([
            loadItems(),
            loadCategories(),
            loadItemTypes()
        ]);
    } catch (error) {
        handleApiError(error, 'Failed to add item');
    }
}

async function editItem(id) {
    const item = State.items.find(item => item.id == id);
    
    if (!item) {
        UI.showMessage('Item not found', 'error');
        return;
    }
    
    const modalContent = `
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
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="edit-is-hidden" name="is_hidden" ${item.is_hidden ? 'checked' : ''}>
                    <label for="edit-is-hidden">Hidden</label>
                </div>
                
                <button type="submit">Update Item</button>
                <button type="button" class="cancel">Cancel</button>
            </form>
        </div>
    `;
    
    const modal = UI.createModal(modalContent);
    
    // Close button functionality
    modal.querySelector('.close').addEventListener('click', () => document.body.removeChild(modal));
    modal.querySelector('.cancel').addEventListener('click', () => document.body.removeChild(modal));
    
    // Form submission
    modal.querySelector('#edit-item-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            const data = Utils.getFormData(this);
            const response = await API.updateItem(id, data);
            UI.showMessage(response.message, 'success');
            document.body.removeChild(modal);
            
            // Reload data
            await Promise.all([
                loadItems(),
                loadCategories(),
                loadItemTypes()
            ]);
        } catch (error) {
            handleApiError(error, 'Failed to update item');
        }
    });
}

async function deleteItem(id) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }
    
    try {
        const response = await API.deleteItem(id);
        UI.showMessage(response.message, 'success');
        
        // Reload data
        await Promise.all([
            loadItems(),
            loadCategories(),
            loadItemTypes()
        ]);
    } catch (error) {
        handleApiError(error, 'Failed to delete item');
    }
}

async function importItems() {
    const fileInput = document.getElementById('import-file');
    
    if (!fileInput.files.length) {
        UI.showMessage('Please select a file to import', 'error');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('import_file', fileInput.files[0]);
        
        const response = await API.importItems(formData);
        UI.showMessage(response.message, 'success');
        fileInput.value = '';
        
        // Reload data
        await Promise.all([
            loadItems(),
            loadCategories(),
            loadItemTypes()
        ]);
    } catch (error) {
        handleApiError(error, 'Failed to import items');
    }
}

async function exportItems() {
    try {
        const params = new URLSearchParams();
        
        if (UI.elements.exportCategorySelect.value) {
            params.append('category', UI.elements.exportCategorySelect.value);
        }
        
        if (UI.elements.exportTypeSelect.value) {
            params.append('item_type', UI.elements.exportTypeSelect.value);
        }
        
        const data = await API.getItems(params);
        
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
    } catch (error) {
        handleApiError(error, 'Failed to export items');
    }
}

async function resetDatabase() {
    if (!confirm('WARNING: This will delete ALL items from the database. This action CANNOT be undone. Are you absolutely sure?')) {
        return;
    }
    
    try {
        const response = await API.resetDatabase();
        UI.showMessage(response.message, 'success');
        
        // Reload data
        await Promise.all([
            loadItems(),
            loadCategories(),
            loadItemTypes()
        ]);
    } catch (error) {
        handleApiError(error, 'Failed to reset database');
    }
}

async function toggleItemHidden(id, currentState) {
    const isCurrentlyHidden = currentState === '1';
    const newHiddenState = !isCurrentlyHidden;
    
    console.log(`Toggling item ${id} - Current hidden state: ${currentState} (${isCurrentlyHidden})`);
    console.log(`Setting new hidden state to: ${newHiddenState}`);
    
    try {
        UI.showMessage('Updating visibility...', 'info');
        
        const response = await API.updateItem(id, { is_hidden: newHiddenState });
        console.log('API response:', response);
        
        if (response.status === 'success' || response.success) {
            UI.showMessage('Item visibility updated', 'success');
            
            // Update the button
            const button = document.querySelector(`.visibility-button[data-id="${id}"]`);
            if (button) {
                button.setAttribute('data-hidden', newHiddenState ? '1' : '0');
                
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = `fas ${newHiddenState ? 'fa-eye-slash' : 'fa-eye'}`;
                }
                
                button.setAttribute('title', newHiddenState ? 'Make visible' : 'Hide item');
            }
            
            // Update the in-memory data
            const itemToUpdate = State.items.find(item => item.id == id);
            if (itemToUpdate) {
                itemToUpdate.is_hidden = newHiddenState;
            }
            
            // Refresh the items list to ensure we have the latest data
            await loadItems();
        } else {
            UI.showMessage(response.error || response.message || 'Failed to update item', 'error');
        }
    } catch (error) {
        handleApiError(error, 'Error updating item visibility');
    }
}

// Load categories from the API
async function loadCategories() {
    try {
        const data = await API.getCategories();
        State.categories = data;
        
        // Clear existing options (except the first one)
        while (UI.elements.categoryFilter.options.length > 1) {
            UI.elements.categoryFilter.remove(1);
        }
        
        while (UI.elements.exportCategorySelect.options.length > 1) {
            UI.elements.exportCategorySelect.remove(1);
        }
        
        // Add categories to filters
        State.categories.forEach(category => {
            const option1 = document.createElement('option');
            option1.value = category.category;
            option1.textContent = `${category.category} (${category.count})`;
            UI.elements.categoryFilter.appendChild(option1);
            
            const option2 = document.createElement('option');
            option2.value = category.category;
            option2.textContent = `${category.category} (${category.count})`;
            UI.elements.exportCategorySelect.appendChild(option2);
        });
    } catch (error) {
        handleApiError(error, 'Failed to load categories');
    }
}

// Load item types from the API
async function loadItemTypes() {
    try {
        const data = await API.getItemTypes();
        State.itemTypes = data;
        
        // Clear existing options (except the first one)
        while (UI.elements.typeFilter.options.length > 1) {
            UI.elements.typeFilter.remove(1);
        }
        
        while (UI.elements.exportTypeSelect.options.length > 1) {
            UI.elements.exportTypeSelect.remove(1);
        }
        
        // Add item types to filters
        State.itemTypes.forEach(type => {
            const option1 = document.createElement('option');
            option1.value = type.item_type;
            option1.textContent = type.item_type;
            UI.elements.typeFilter.appendChild(option1);
            
            const option2 = document.createElement('option');
            option2.value = type.item_type;
            option2.textContent = type.item_type;
            UI.elements.exportTypeSelect.appendChild(option2);
        });
    } catch (error) {
        handleApiError(error, 'Failed to load item types');
    }
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
