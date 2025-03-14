jQuery(document).ready(function($) {
    // Manejar la subida de imágenes y actualizar la vista previa
    $(document).on('click', '.upload-icon-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var frame = wp.media({
            title: 'Select or Upload an Image',
            library: { type: 'image' },
            button: { text: 'Use this Image' },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            var iconUrl = attachment.url;
            button.prev('.icon-url').val(iconUrl); // Actualizar el campo de URL del ícono
            button.siblings('.icon-preview').html('<img src="' + iconUrl + '" alt="Icon Preview" class="icon-preview-image" />'); // Actualizar la vista previa
        });
        frame.open();
    });

    // Actualizar la vista previa cuando se cambia manualmente la URL del ícono
    $(document).on('input', '.icon-url', function() {
        var iconUrl = $(this).val();
        var iconPreview = $(this).siblings('.icon-preview');
        if (iconUrl) {
            iconPreview.html('<img src="' + iconUrl + '" alt="Icon Preview" class="icon-preview-image" />');
        } else {
            iconPreview.html('<span class="icon-preview-placeholder">' + 'No icon selected' + '</span>');
        }
    });

    // Mover botones hacia arriba o hacia abajo
    $('.move-up').click(function() {
        var row = $(this).closest('tr');
        if (row.prev().length) {
            row.insertBefore(row.prev());
        }
    });

    $('.move-down').click(function() {
        var row = $(this).closest('tr');
        if (row.next().length) {
            row.insertAfter(row.next());
        }
    });

    // Guardar el nuevo orden al hacer clic en "Save Buttons"
    $('form').on('submit', function() {
        var buttons = [];
        $('#button-list .button-item').each(function() {
            buttons.push({
                text: $(this).find('input[name$="[text]"]').val(),
                icon: $(this).find('input[name$="[icon]"]').val(),
                link: $(this).find('input[name$="[link]"]').val()
            });
        });
        $('#button_order').val(JSON.stringify(buttons)); // Guardar el orden en un campo oculto
    });
});