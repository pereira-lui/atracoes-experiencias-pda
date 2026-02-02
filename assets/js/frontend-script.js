/**
 * Atrações e Experiências PDA - Frontend Scripts
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.3.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize frontend functionality
        initGalleryThumbnails();
        initGalleryLightbox();
    });

    /**
     * Initialize Gallery Thumbnails Navigation
     */
    function initGalleryThumbnails() {
        var $thumbs = $('.aepda-gallery__thumb');
        var $mainImg = $('#aepda-gallery-main-img');
        var $mainLink = $('#aepda-gallery-main-link');
        var $thumbsContainer = $('#aepda-gallery-thumbs');
        var $prevBtn = $('.aepda-gallery__nav--prev');
        var $nextBtn = $('.aepda-gallery__nav--next');
        
        if (!$thumbs.length) return;
        
        var currentIndex = 0;
        var totalThumbs = $thumbs.length;
        
        // Click on thumbnail to change main image
        $thumbs.on('click', function() {
            var $thumb = $(this);
            var largeUrl = $thumb.data('large');
            var fullUrl = $thumb.data('full');
            
            // Update main image
            $mainImg.attr('src', largeUrl);
            $mainLink.attr('href', fullUrl);
            
            // Update active state
            $thumbs.removeClass('aepda-gallery__thumb--active');
            $thumb.addClass('aepda-gallery__thumb--active');
            
            // Update current index
            currentIndex = $thumbs.index($thumb);
        });
        
        // Navigation buttons
        $prevBtn.on('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                $thumbs.eq(currentIndex).trigger('click');
                scrollToThumb(currentIndex);
            }
        });
        
        $nextBtn.on('click', function() {
            if (currentIndex < totalThumbs - 1) {
                currentIndex++;
                $thumbs.eq(currentIndex).trigger('click');
                scrollToThumb(currentIndex);
            }
        });
        
        // Scroll thumbnail into view
        function scrollToThumb(index) {
            var $thumb = $thumbs.eq(index);
            if ($thumb.length && $thumbsContainer.length) {
                var thumbLeft = $thumb.position().left;
                var containerWidth = $thumbsContainer.width();
                var thumbWidth = $thumb.outerWidth(true);
                
                if (thumbLeft < 0 || thumbLeft + thumbWidth > containerWidth) {
                    $thumbsContainer.animate({
                        scrollLeft: $thumbsContainer.scrollLeft() + thumbLeft - (containerWidth / 2) + (thumbWidth / 2)
                    }, 200);
                }
            }
        }
    }

    /**
     * Initialize Gallery Lightbox
     */
    function initGalleryLightbox() {
        // Handle click on main gallery image
        $('.aepda-gallery__main-link').on('click', function(e) {
            e.preventDefault();
            
            var fullSrc = $(this).attr('href');
            var $img = $(this).find('img');
            
            openLightbox(fullSrc, $img.attr('alt') || '');
        });
        
        function openLightbox(src, alt) {
            // Create overlay
            var $overlay = $('<div class="aepda-lightbox-overlay">' +
                '<div class="aepda-lightbox-content">' +
                    '<button class="aepda-lightbox-close">&times;</button>' +
                    '<img src="' + src + '" alt="' + alt + '">' +
                '</div>' +
            '</div>');
            
            $('body').append($overlay);
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
        }
        
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
