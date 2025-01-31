jQuery(document).ready(function($) {
    function checkFormatSupport() {
        var selectedFormat = $('#wpic_format').val();
        var messageElement = $('#format-support-message');
        var qualitySlider = $('#quality-slider');
        var enableCheckbox = $('input[name="wpic_enabled"]');
        var replaceUrlCheckbox = $('input[name="wpic_replace_url"]');
        var submitButton = $('input[type="submit"]');
        messageElement.html('');

        if (selectedFormat === 'webp') {
            if (!wpic_vars.webp_supported) {
                messageElement.html(wpic_vars.webp_not_supported);
                disableControls(qualitySlider, enableCheckbox, replaceUrlCheckbox, submitButton);
            } else {
                messageElement.html(wpic_vars.webp_supported_message);
                enableControls(qualitySlider, enableCheckbox, replaceUrlCheckbox, submitButton);
            }
        } else if (selectedFormat === 'avif') {
            if (!wpic_vars.avif_supported) {
                messageElement.html(wpic_vars.avif_not_supported);
                disableControls(qualitySlider, enableCheckbox, replaceUrlCheckbox, submitButton);
            } else {
                messageElement.html(wpic_vars.avif_supported_message);
                enableControls(qualitySlider, enableCheckbox, replaceUrlCheckbox, submitButton);
            }
        }
    }

    function disableControls(slider, checkbox, replaceUrlCheckbox, button) {
        slider.prop('disabled', true);
        checkbox.prop('disabled', true);
        replaceUrlCheckbox.prop('disabled', true);
        button.prop('disabled', true);
    }

    function enableControls(slider, checkbox, replaceUrlCheckbox, button) {
        slider.prop('disabled', false);
        checkbox.prop('disabled', false);
        replaceUrlCheckbox.prop('disabled', false);
        button.prop('disabled', false);
    }

    function updateQualityValue() {
        var qualitySlider = $('#quality-slider');
        var qualityValue = $('#quality-value');
        qualityValue.text(qualitySlider.val());
    }

    $('#wpic_format').on('change', checkFormatSupport);
    $('#quality-slider').on('input', updateQualityValue);

    checkFormatSupport();
    updateQualityValue();
});

jQuery(document).ready(function($) {
    // Función para habilitar/deshabilitar inputs
    function toggleInputs() {
        var isEnabled = $('#wpic_enabled').is(':checked');
        $('#wpic_format, #quality-slider, #wpic_max_size, #wpic_replace_url, #wpic_add_dimensions').prop('disabled', !isEnabled);

        // Desmarcar checkbox si el plugin está desactivado
        if (!isEnabled) {
            $('#wpic_replace_url, #wpic_add_dimensions, #wpic_replace_url, #quality-slider').prop('checked', false);
        }else{
	        $("#wpic_max_size").val("5");
	        $("#quality-slider").val("50");
	        $("#quality-value").html("50");
        }
    }

    // Escuchar cambios en el checkbox "Habilitar conversión"
    $('#wpic_enabled').on('change', toggleInputs);

    // Ejecutar al cargar la página
    toggleInputs();
});