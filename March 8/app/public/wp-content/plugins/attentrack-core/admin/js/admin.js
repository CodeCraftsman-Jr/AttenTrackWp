jQuery(document).ready(function($) {
    // Initialize color pickers
    $('.color-picker').wpColorPicker();

    // Make phase blocks sortable
    $('#test-phases-container').sortable({
        items: '.phase-block',
        handle: '.phase-header',
        axis: 'y',
        update: function(event, ui) {
            updatePhaseIndexes();
        }
    });

    // Make stimuli blocks sortable
    $('.stimuli-container').sortable({
        items: '.stimulus-block',
        handle: '.stimulus-handle',
        axis: 'y',
        update: function(event, ui) {
            updateStimuliIndexes($(this));
        }
    });

    // Add new phase
    $('#add-phase').on('click', function() {
        const phaseIndex = $('.phase-block').length;
        const phaseTemplate = `
            <div class="phase-block" data-index="${phaseIndex}">
                <h3 class="phase-header">
                    Phase ${phaseIndex + 1}
                    <button type="button" class="remove-phase button-link">Remove</button>
                </h3>
                
                <div class="phase-content">
                    <p>
                        <label>Phase Title:</label>
                        <input type="text" name="phases[${phaseIndex}][title]" class="widefat">
                    </p>
                    
                    <p>
                        <label>Duration (seconds):</label>
                        <input type="number" name="phases[${phaseIndex}][duration]" min="1" max="3600" value="60">
                    </p>
                    
                    <div class="stimuli-container">
                        <h4>Stimuli</h4>
                        <button type="button" class="add-stimulus button">Add Stimulus</button>
                    </div>
                </div>
            </div>
        `;
        
        $(phaseTemplate).insertBefore(this);
        initializeSortable($('.phase-block').last().find('.stimuli-container'));
    });

    // Remove phase
    $(document).on('click', '.remove-phase', function() {
        $(this).closest('.phase-block').remove();
        updatePhaseIndexes();
    });

    // Add new stimulus
    $(document).on('click', '.add-stimulus', function() {
        const $stimuliContainer = $(this).closest('.stimuli-container');
        const phaseIndex = $(this).closest('.phase-block').data('index');
        const stimulusIndex = $stimuliContainer.find('.stimulus-block').length;
        
        const stimulusTemplate = `
            <div class="stimulus-block">
                <span class="stimulus-handle dashicons dashicons-menu"></span>
                <select name="phases[${phaseIndex}][stimuli][${stimulusIndex}][type]">
                    <option value="text">Text</option>
                    <option value="image">Image</option>
                    <option value="pattern">Pattern</option>
                </select>
                
                <input type="text" name="phases[${phaseIndex}][stimuli][${stimulusIndex}][content]" 
                       class="widefat stimulus-content" placeholder="Enter stimulus content">
                
                <button type="button" class="remove-stimulus button-link">Remove</button>
            </div>
        `;
        
        $(stimulusTemplate).insertBefore(this);
    });

    // Remove stimulus
    $(document).on('click', '.remove-stimulus', function() {
        $(this).closest('.stimulus-block').remove();
        updateStimuliIndexes($(this).closest('.stimuli-container'));
    });

    // Handle stimulus type change
    $(document).on('change', '.stimulus-block select', function() {
        const $contentInput = $(this).siblings('.stimulus-content');
        const type = $(this).val();
        
        switch(type) {
            case 'text':
                $contentInput.attr('placeholder', 'Enter text content');
                break;
            case 'image':
                $contentInput.attr('placeholder', 'Enter image URL or click to upload');
                // Could add media uploader functionality here
                break;
            case 'pattern':
                $contentInput.attr('placeholder', 'Enter pattern configuration');
                break;
        }
    });

    // Update phase indexes when order changes
    function updatePhaseIndexes() {
        $('.phase-block').each(function(index) {
            $(this).data('index', index);
            $(this).find('.phase-header').text('Phase ' + (index + 1));
            
            // Update input names
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/phases\[\d+\]/, `phases[${index}]`));
                }
            });
        });
    }

    // Update stimuli indexes within a phase
    function updateStimuliIndexes($container) {
        $container.find('.stimulus-block').each(function(index) {
            const phaseIndex = $(this).closest('.phase-block').data('index');
            
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(
                        /phases\[\d+\]\[stimuli\]\[\d+\]/,
                        `phases[${phaseIndex}][stimuli][${index}]`
                    ));
                }
            });
        });
    }

    // Initialize sortable functionality for new elements
    function initializeSortable($element) {
        $element.sortable({
            items: '.stimulus-block',
            handle: '.stimulus-handle',
            axis: 'y',
            update: function(event, ui) {
                updateStimuliIndexes($(this));
            }
        });
    }

    // Test type dependent settings
    $('#test_type').on('change', function() {
        const type = $(this).val();
        const $settings = $('.test-settings');
        
        switch(type) {
            case 'sustained':
                $settings.find('[name="test_settings[show_progress]"]').prop('checked', true);
                $settings.find('[name="test_settings[allow_pause]"]').prop('checked', false);
                $settings.find('[name="test_settings[feedback_frequency]"]').val('phase');
                break;
            
            case 'selective':
                $settings.find('[name="test_settings[show_progress]"]').prop('checked', true);
                $settings.find('[name="test_settings[allow_pause]"]').prop('checked', false);
                $settings.find('[name="test_settings[feedback_frequency]"]').val('immediate');
                break;
            
            case 'divided':
                $settings.find('[name="test_settings[show_progress]"]').prop('checked', true);
                $settings.find('[name="test_settings[allow_pause]"]').prop('checked', false);
                $settings.find('[name="test_settings[feedback_frequency]"]').val('end');
                break;
            
            case 'switching':
                $settings.find('[name="test_settings[show_progress]"]').prop('checked', true);
                $settings.find('[name="test_settings[allow_pause]"]').prop('checked', true);
                $settings.find('[name="test_settings[feedback_frequency]"]').val('phase');
                break;
        }
    });

    // Form validation
    $('form#post').on('submit', function(e) {
        const $testType = $('#test_type');
        const $testDuration = $('#test_duration');
        const $phases = $('.phase-block');
        
        let isValid = true;
        let message = '';

        // Check if test type is selected
        if (!$testType.val()) {
            isValid = false;
            message += 'Please select a test type.\n';
        }

        // Check if duration is set and valid
        if (!$testDuration.val() || $testDuration.val() < 1) {
            isValid = false;
            message += 'Please set a valid test duration (minimum 1 minute).\n';
        }

        // Check if at least one phase exists
        if ($phases.length === 0) {
            isValid = false;
            message += 'Please add at least one test phase.\n';
        }

        // Check each phase
        $phases.each(function(index) {
            const $phase = $(this);
            const title = $phase.find('input[name*="[title]"]').val();
            const duration = $phase.find('input[name*="[duration]"]').val();
            const stimuli = $phase.find('.stimulus-block').length;

            if (!title) {
                isValid = false;
                message += `Phase ${index + 1}: Please enter a title.\n`;
            }

            if (!duration || duration < 1) {
                isValid = false;
                message += `Phase ${index + 1}: Please set a valid duration.\n`;
            }

            if (stimuli === 0) {
                isValid = false;
                message += `Phase ${index + 1}: Please add at least one stimulus.\n`;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following issues:\n\n' + message);
        }
    });
});
