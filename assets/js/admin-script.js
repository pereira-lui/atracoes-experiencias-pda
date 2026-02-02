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
        // Debug: verificar quantos campos existem
        console.log('AEPDA: Initializing image uploads');
        console.log('AEPDA: atracao_imagem_topo found:', $('#atracao_imagem_topo').length);
        console.log('AEPDA: atracao_galeria found:', $('#atracao_galeria').length);
        console.log('AEPDA: atracao_blog_imagem found:', $('#atracao_blog_imagem').length);
        
        $(document).on('click', '.atracao-image-upload-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var targetId = $button.data('target');
            var previewId = $button.data('preview');
            var $input = $('#' + targetId);
            var $preview = $('#' + previewId);
            var $removeBtn = $button.siblings('.atracao-image-remove-btn');
            
            console.log('AEPDA: Click on upload btn, target:', targetId);
            console.log('AEPDA: Input element found:', $input.length);
            console.log('AEPDA: Input current value:', $input.val());

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

                console.log('AEPDA: Selected attachment ID:', attachment.id);
                console.log('AEPDA: Setting value to input:', targetId);
                
                $input.val(attachment.id);
                
                console.log('AEPDA: Input value after set:', $input.val());
                
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
    
    /**
     * Initialize Blog Posts Selector
     */
    function initBlogPostsSelector() {
        var $searchInput = $('#blog-posts-search');
        var $availableList = $('#blog-posts-list');
        var $selectedList = $('#blog-posts-selected-list');
        var $selectedCount = $('#selected-count');
        var $selectedEmpty = $('#selected-empty');
        
        if (!$searchInput.length) return;
        
        // Função para atualizar contagem
        function updateCount() {
            var count = $selectedList.find('.atracao-blog-post-item').length;
            $selectedCount.text('(' + count + ')');
            
            if (count === 0) {
                if (!$selectedEmpty.length) {
                    $selectedList.html('<p class="atracao-blog-posts-empty" id="selected-empty">Nenhum post selecionado</p>');
                }
            } else {
                $selectedEmpty.remove();
            }
        }
        
        // Pesquisa
        $searchInput.on('input', function() {
            var searchTerm = $(this).val().toLowerCase().trim();
            
            $availableList.find('.atracao-blog-post-item').each(function() {
                var $item = $(this);
                var title = $item.data('title');
                
                if ($item.hasClass('atracao-blog-post-item--hidden')) {
                    return; // Já está selecionado, manter escondido
                }
                
                if (searchTerm === '' || title.indexOf(searchTerm) !== -1) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        });
        
        // Adicionar post
        $availableList.on('click', '.atracao-blog-post-add', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.atracao-blog-post-item');
            var postId = $item.data('id');
            var postTitle = $item.find('.atracao-blog-post-title').text();
            
            // Criar item selecionado
            var $selectedItem = $('<div class="atracao-blog-post-item atracao-blog-post-item--selected" data-id="' + postId + '">' +
                '<input type="checkbox" name="atracao_blog_posts[]" value="' + postId + '" checked class="atracao-blog-post-checkbox">' +
                '<span class="atracao-blog-post-title">' + postTitle + '</span>' +
                '<button type="button" class="atracao-blog-post-remove" title="Remover">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                '</button>' +
            '</div>');
            
            // Remover mensagem de vazio
            $selectedEmpty.remove();
            
            // Adicionar à lista de selecionados
            $selectedList.append($selectedItem);
            
            // Esconder da lista de disponíveis
            $item.addClass('atracao-blog-post-item--hidden');
            
            updateCount();
        });
        
        // Remover post
        $selectedList.on('click', '.atracao-blog-post-remove', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.atracao-blog-post-item');
            var postId = $item.data('id');
            
            // Mostrar na lista de disponíveis
            $availableList.find('.atracao-blog-post-item[data-id="' + postId + '"]').removeClass('atracao-blog-post-item--hidden');
            
            // Remover da lista de selecionados
            $item.remove();
            
            updateCount();
        });
    }
    
    // Inicializar seletor de posts do blog
    initBlogPostsSelector();

})(jQuery);
