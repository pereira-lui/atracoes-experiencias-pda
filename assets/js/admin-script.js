/**
 * Atrações e Experiências PDA - Admin Scripts
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Initialize color pickers
        initColorPickers();
        
        // Initialize gallery
        initGallery();
        
        // Initialize image uploads
        initImageUploads();
        
        // Initialize repeater fields (rules and links)
        initRepeaterFields();
    });

    /**
     * Initialize Color Pickers
     */
    function initColorPickers() {
        $('.atracao-color-picker').wpColorPicker();
    }

    /**
     * Initialize Gallery
     */
    function initGallery() {
        var $galeriaAdd = $('#atracao-galeria-add');
        var $galeriaPreview = $('#atracao-galeria-preview');
        var $galeriaInput = $('#atracao_galeria');

        // Add images button
        $galeriaAdd.on('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
                title: atracoesExpPda.selectImages,
                button: {
                    text: atracoesExpPda.useImages
                },
                multiple: true
            });

            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = $galeriaInput.val() ? $galeriaInput.val().split(',') : [];

                attachments.forEach(function(attachment) {
                    if (currentIds.indexOf(attachment.id.toString()) === -1) {
                        currentIds.push(attachment.id);
                        
                        var thumbnail = attachment.sizes && attachment.sizes.thumbnail 
                            ? attachment.sizes.thumbnail.url 
                            : attachment.url;

                        var $item = $('<div class="atracao-galeria-item" data-id="' + attachment.id + '">' +
                            '<img src="' + thumbnail + '" alt="">' +
                            '<button type="button" class="atracao-galeria-remove">&times;</button>' +
                            '</div>');

                        $galeriaPreview.append($item);
                    }
                });

                $galeriaInput.val(currentIds.join(','));
            });

            frame.open();
        });

        // Remove image
        $galeriaPreview.on('click', '.atracao-galeria-remove', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.atracao-galeria-item');
            var removeId = $item.data('id').toString();
            var currentIds = $galeriaInput.val() ? $galeriaInput.val().split(',') : [];

            currentIds = currentIds.filter(function(id) {
                return id !== removeId;
            });

            $galeriaInput.val(currentIds.join(','));
            $item.remove();
        });

        // Make gallery sortable
        if ($.fn.sortable) {
            $galeriaPreview.sortable({
                items: '.atracao-galeria-item',
                cursor: 'move',
                update: function() {
                    var newIds = [];
                    $galeriaPreview.find('.atracao-galeria-item').each(function() {
                        newIds.push($(this).data('id'));
                    });
                    $galeriaInput.val(newIds.join(','));
                }
            });
        }
    }

    /**
     * Initialize Single Image Uploads
     */
    function initImageUploads() {
        $(document).on('click', '.atracao-image-upload-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var targetId = $button.data('target');
            var previewId = $button.data('preview');
            var $input = $('#' + targetId);
            var $preview = $('#' + previewId);
            var $removeBtn = $button.siblings('.atracao-image-remove-btn');

            var frame = wp.media({
                title: atracoesExpPda.selectImage,
                button: {
                    text: atracoesExpPda.useImage
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var imageUrl = attachment.sizes && attachment.sizes.medium 
                    ? attachment.sizes.medium.url 
                    : attachment.url;

                $input.val(attachment.id);
                $preview.html('<img src="' + imageUrl + '" alt="">');
                $removeBtn.show();
            });

            frame.open();
        });

        // Remove image
        $(document).on('click', '.atracao-image-remove-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var targetId = $button.data('target');
            var previewId = $button.data('preview');

            $('#' + targetId).val('');
            $('#' + previewId).html('');
            $button.hide();
        });
    }

    /**
     * Initialize Repeater Fields (Rules and Links)
     */
    function initRepeaterFields() {
        
        // Rules repeater
        var $regrasList = $('#atracao-regras-list');
        var regraIndex = $regrasList.find('.atracao-regra-item').length;

        $('#atracao-regra-add').on('click', function(e) {
            e.preventDefault();

            var $newItem = $('<div class="atracao-regra-item">' +
                '<div class="atracao-regra-icon">' +
                    '<label>Ícone (Dashicon)</label>' +
                    '<input type="text" name="atracao_regras[' + regraIndex + '][icone]" placeholder="dashicons-warning">' +
                '</div>' +
                '<div class="atracao-regra-texto">' +
                    '<label>Texto</label>' +
                    '<input type="text" name="atracao_regras[' + regraIndex + '][texto]" class="widefat" placeholder="Ex: Não alimentar os animais">' +
                '</div>' +
                '<button type="button" class="button atracao-regra-remove">Remover</button>' +
            '</div>');

            $regrasList.append($newItem);
            regraIndex++;
        });

        $regrasList.on('click', '.atracao-regra-remove', function(e) {
            e.preventDefault();
            $(this).closest('.atracao-regra-item').remove();
        });

        // Links repeater
        var $linksList = $('#atracao-links-list');
        var linkIndex = $linksList.find('.atracao-link-item').length;

        $('#atracao-link-add').on('click', function(e) {
            e.preventDefault();

            var $newItem = $('<div class="atracao-link-item">' +
                '<div class="atracao-link-texto">' +
                    '<label>Texto</label>' +
                    '<input type="text" name="atracao_links[' + linkIndex + '][texto]" class="widefat" placeholder="Ex: Planeje sua Visita">' +
                '</div>' +
                '<div class="atracao-link-url">' +
                    '<label>URL</label>' +
                    '<input type="url" name="atracao_links[' + linkIndex + '][url]" class="widefat" placeholder="https://...">' +
                '</div>' +
                '<button type="button" class="button atracao-link-remove">Remover</button>' +
            '</div>');

            $linksList.append($newItem);
            linkIndex++;
        });

        $linksList.on('click', '.atracao-link-remove', function(e) {
            e.preventDefault();
            $(this).closest('.atracao-link-item').remove();
        });
    }

})(jQuery);
