/**
 * Atrações e Experiências PDA - Frontend Scripts
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize any frontend functionality here
        initGalleryLightbox();
    });

    /**
     * Initialize Gallery Lightbox (if needed)
     */
    function initGalleryLightbox() {
        // Simple lightbox for gallery items
        $('.aepda-gallery__item').on('click', function(e) {
            e.preventDefault();
            
            var $img = $(this).find('img');
            var fullSrc = $img.attr('src').replace(/-\d+x\d+\./, '.'); // Try to get full size
            
            // Create overlay
            var $overlay = $('<div class="aepda-lightbox-overlay">' +
                '<div class="aepda-lightbox-content">' +
                    '<button class="aepda-lightbox-close">&times;</button>' +
                    '<img src="' + fullSrc + '" alt="' + $img.attr('alt') + '">' +
                '</div>' +
            '</div>');
            
            $('body').append($overlay);
            
            // Prevent body scroll
            $('body').addClass('aepda-no-scroll');
            
            // Fade in
            setTimeout(function() {
                $overlay.addClass('aepda-lightbox--active');
            }, 10);
            
            // Close handlers
            $overlay.on('click', function(e) {
                if ($(e.target).hasClass('aepda-lightbox-overlay') || $(e.target).hasClass('aepda-lightbox-close')) {
                    closeLightbox($overlay);
                }
            });
            
            // ESC key
            $(document).on('keyup.lightbox', function(e) {
                if (e.key === 'Escape') {
                    closeLightbox($overlay);
                }
            });
        });
        
        function closeLightbox($overlay) {
            $overlay.removeClass('aepda-lightbox--active');
            $('body').removeClass('aepda-no-scroll');
            
            setTimeout(function() {
                $overlay.remove();
                $(document).off('keyup.lightbox');
            }, 300);
        }
    }

})(jQuery);

// Add lightbox styles dynamically
(function() {
    var styles = `
        .aepda-no-scroll {
            overflow: hidden;
        }
        
        .aepda-lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .aepda-lightbox--active {
            opacity: 1;
        }
        
        .aepda-lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }
        
        .aepda-lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            display: block;
            border-radius: 5px;
        }
        
        .aepda-lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            padding: 5px 10px;
            transition: opacity 0.3s ease;
        }
        
        .aepda-lightbox-close:hover {
            opacity: 0.7;
        }
    `;
    
    var styleSheet = document.createElement('style');
    styleSheet.type = 'text/css';
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
})();
