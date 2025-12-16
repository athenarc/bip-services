$(document).ready(() => {
    // Function to check and update the add row button state
    function updateAddRowButton(table) {
        const maxRows = table.data('max-rows');
        const rowCount = table.find('tbody tr').length;
        const addButton = table.closest('.dynamic-table-panel').find('.add-table-row');

        if (maxRows && rowCount >= maxRows) {
            addButton.prop('disabled', true);
        } else {
            addButton.prop('disabled', false);
        }
    }

    /// Update status meesage
    function updateStatusMessage(table, text) {
        table.closest('.dynamic-table-panel').find('.status-message').text(text);
    }

    function adjustTextareaHeight() {
        $('textarea.element-table-input').each(function () {
            this.style.height = `${this.scrollHeight }px`;
            this.style.overflowY = 'hidden';
            this.style.resize = 'none';
        }).on('input', function () {
            this.style.height = 'auto';
            this.style.height = `${this.scrollHeight }px`;
        });
    }

    function adjustRowHeight($row) {
        // Find the max height among all textareas in this row
        let maxHeight = 0;
        $row.find('textarea.element-table-input').each(function () {
            this.style.height = 'auto'; // Reset first to get proper scrollHeight
            const height = this.scrollHeight;
            if (height > maxHeight) {
                maxHeight = height;
            }
        });

        // Apply max height to all textareas in the row
        $row.find('textarea.element-table-input').each(function () {
            this.style.height = `${maxHeight }px`;
        });
    }

    function adjustTablesRowsHeights() {
        $('table.dynamic-table tr').each(function () {
            const $row = $(this);
            adjustRowHeight($row);
        });
    }

    // Function to save table data via AJAX
    function saveTableInstance($table) {
        const elementId = $table.data('element-id');
        const templateId = $('#template_id').val();
        const tableData = [];

        // Loop through each row in the table and collect data
        $table.find('tbody tr').each(function () {
            const rowData = [];
            $(this).find('.element-table-input').each(function () {
                rowData.push($(this).val());
            });
            tableData.push(rowData);
        });

        // AJAX request to save data
        $.ajax({
            url: `${appBaseUrl }/scholar/save-table-instance`,
            type: 'POST',
            data: {
                table_data: tableData,
                template_id: templateId,
                element_id: elementId,
            },
            success: function (response) {
                console.log(response);
                updateStatusMessage($table, response.last_updated_message);
            },
            error: function (xhr, status, error) {
                console.error('Error saving data:', error);
                updateStatusMessage($table, 'Error saving data');
            },
        });
    }


    // Handle typing event (immediate feedback)
    // Adjust heights dynamically when textarea input changes
    $(document).on('input', 'textarea.element-table-input', function () {
        const table = $(this).closest('.dynamic-table-panel').find('.dynamic-table');

        // Show typing indicator immediately
        updateStatusMessage(table, 'Typing...');

        // Find the row of the current textarea
        const $row = $(this).closest('tr');
        adjustRowHeight($row);
    });

    // Add new row
    $(document).on('click', '.add-table-row', function () {
        const table = $(this).closest('.dynamic-table-panel').find('.dynamic-table');
        const maxRows = table.data('max-rows');
        const rowCount = table.find('tbody tr').length;

        if (!maxRows || rowCount < maxRows) {
            let newRow = '<tr>';
            // Generate input fields based on column count
            table.find('thead th').each(index => {
                if (index < table.find('thead th').length - 1) {
                    newRow += `<td><textarea class="form-control search-box element-table-input"></textarea></td>`;
                }
            });

            newRow += '<td style="vertical-align: middle;"><button class="btn btn-danger remove-table-row"><i class="glyphicon glyphicon-minus"></i></button></td></tr>';
            table.find('tbody').append(newRow);

            updateAddRowButton(table);
            saveTableInstance(table);
        }
    });

    // Remove row
    $(document).on('click', '.remove-table-row', function () {
        if (confirm('Are you sure you want to remove this row?')) {
            const table = $(this).closest('.dynamic-table-panel').find('.dynamic-table');

            $(this).closest('tr').fadeOut(300, function () {
                $(this).remove(); // Ensure row is removed first

                updateAddRowButton(table); // Then update button state
                saveTableInstance(table);
            });
        }
    });

    // // Save table data via AJAX, with save button
    // $(document).on('click', '.save-element-table', function () {
    //     let table = $(this).closest('.dynamic-table-panel').find('.dynamic-table');
    //     saveTableInstance(table)
    // });

    // Save table data via AJAX (debounce)
    $(document).on('input', 'textarea.element-table-input', debounce(function () {
        const table = $(this).closest('.dynamic-table-panel').find('.dynamic-table');
        saveTableInstance(table);
    }, 500)); // Save after a delay of 500ms


    // Initial check for all tables
    $('.dynamic-table').each(function () {
        updateAddRowButton($(this));
    });


    adjustTablesRowsHeights();
});
