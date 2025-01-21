function toggleNoItemsMessage(listContainer) {
    const items = listContainer.find('.item');
    const noItemsMsg = listContainer.find('#no-items-msg');
    const statusMessage = listContainer.find('.status-message');

    if (items.length === 0) {
        noItemsMsg.show(); // Show the "no items" message
        statusMessage.hide();
    } else {
        noItemsMsg.hide(); // Hide the "no items" message
        statusMessage.show();
    }
}

function toggleNewItemButton(listContainer) {
    const maxElements = parseInt(listContainer.attr('max-elements'), 10); // Get the max allowed elements
    const currentItemsCount = listContainer.find('.item').length; // Count current items
    const newItemButton = listContainer.find('#new-item-btn');

    if (currentItemsCount >= maxElements) {
        newItemButton.prop('disabled', true); // Disable the button
    } else {
        newItemButton.prop('disabled', false); // Enable the button
    }
}

/// Show typing status
function showTypingIndicator(listContainer) {
    const statusMessage = listContainer.find('.status-message');
    statusMessage.text('Typing...');
    statusMessage.attr('title', ''); // clear title while typing
}

// Hide typing status and update timestamp
function updateLastUpdated(listContainer, { timestamp, message }) {
    const statusMessage = listContainer.find('.status-message');
    statusMessage.text(message).attr('title', timestamp);
}

// Handle typing event (immediate feedback)
$(document).on('input', '.item-value', function () {
    const input = $(this);
    const listContainer = input.closest('.bulleted-list');

    // Show typing indicator immediately
    showTypingIndicator(listContainer);
});

// Add new item via AJAX
$(document).on('click', '.add-item', function () {
    const listContainer = $(this).closest('.bulleted-list');
    const elementId = listContainer.data('id');

    $.ajax({
        url: `${appBaseUrl}/scholar/create-bulleted-list-item`,
        method: 'POST',
        data: {
            template_id: $('#template_id').val(),
            element_id: elementId
        },
        success: function (response) {
            if (!response.id) {
                alert('Error creating new list item');
                return;
            }

            const index = listContainer.find('.item').length; // Set the index for the new item
            const newItem = `<div id="${response.id}_item" data-id="${response.id}" class="item row" data-index="${index}" style="margin-bottom: 5px;">
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" 
                            id="${response.id}_input"
                            class="form-control item-value search-box"
                            value=""/>
                        <span class="input-group-btn">
                            <button class="btn btn-danger remove-item" type="button">
                                <i class="glyphicon glyphicon-minus" title="Remove list item"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>`;

            listContainer.find('.container-items').append(newItem);
            toggleNoItemsMessage(listContainer);
            toggleNewItemButton(listContainer);
        },
        error: function () {
            alert('Error creating new list item');
        }
    });
});

// Handle value save with debounce
$(document).on('input', '.item-value', debounce(function () {
    const input = $(this);
    const value = input.val();
    const listContainer = input.closest('.bulleted-list');
    const item = input.closest('.item');
    const item_id = item.data('id');

    // Send the updated value to the server
    $.ajax({
        url: `${appBaseUrl}/scholar/update-bulleted-list-item`,
        method: 'POST',
        data: {
            item_id,
            value,
        },
        success: function (response) {
            updateLastUpdated(listContainer, response.last_updated); // Update timestamp after success
        },
        error: function () {
            alert('Error saving list item ' + item_id);
        }
    });
}, 500)); // Save after a delay of 500ms

// Remove item via AJAX
$(document).on('click', '.remove-item', function () {
    const item = $(this).closest('.item');
    const itemId = item.data('id');
    const listContainer = item.closest('.bulleted-list');

    if (confirm('Are you sure you want to remove this item?')) {
        $.ajax({
            url: `${appBaseUrl}/scholar/delete-bulleted-list-item`,
            method: 'POST',
            data: { item_id: itemId },
            success: function () {
                item.remove();
                toggleNoItemsMessage(listContainer);
                toggleNewItemButton(listContainer); 
            },
            error: function () {
                alert('Error removing item ' + itemId);
            }
        });
    }
});

// Initialize each bulleted list on page load
$(document).ready(function () {
    $('.bulleted-list').each(function () {
        const listContainer = $(this);

        // Initialize the current bulleted list
        toggleNoItemsMessage(listContainer);
        toggleNewItemButton(listContainer); 
    });
});
