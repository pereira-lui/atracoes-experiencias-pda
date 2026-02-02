/**
 * Atrações e Experiências PDA - Frontend Scripts
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.3.8
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize frontend functionality
        initAepdaPdaGallerySwiper();
        initAepdaPdaGalleryLightbox();
    });

    /**
     * Initialize Gallery Swiper
     */
    function initAepdaPdaGallerySwiper() {
        var $mainGallery = $('.aepda-pda-gallery-main');
        
        if (!$mainGallery.length) return;
        
        // Initialize Main Swiper with navigation
        var aepdaPdaMainSwiper = new Swiper('.aepda-pda-gallery-main', {
            slidesPerView: 1.2,
            spaceBetween: 15,
            loop: true,
            centeredSlides: false,
            navigation: {
                nextEl: '.aepda-pda-gallery-nav--next',
                prevEl: '.aepda-pda-gallery-nav--prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 1.2,
                    spaceBetween: 15,
                },
                1024: {
                    slidesPerView: 1.2,
                    spaceBetween: 20,
                }
            }
        });
    }

    /**
     * Initialize Gallery Lightbox
     */
    function initAepdaPdaGalleryLightbox() {
        // Handle click on gallery images
        $(document).on('click', '.aepda-pda-gallery-link', function(e) {
            e.preventDefault();
            
            var fullSrc = $(this).attr('href');
            var $img = $(this).find('img');
            
            aepdaPdaOpenLightbox(fullSrc, $img.attr('alt') || '');
        });
        
        function aepdaPdaOpenLightbox(src, alt) {
            // Create overlay with unique classes
            var $overlay = $('<div class="aepda-pda-lightbox-overlay">' +
                '<div class="aepda-pda-lightbox-content">' +
                    '<button class="aepda-pda-lightbox-close">&times;</button>' +
                    '<img src="' + src + '" alt="' + alt + '">' +
                '</div>' +
            '</div>');
            
            $('body').append($overlay);
            $('body').addClass('aepda-pda-no-scroll');
            
            // Fade in
            setTimeout(function() {
                $overlay.addClass('aepda-pda-lightbox--active');
            }, 10);
            
            // Close handlers
            $overlay.on('click', function(e) {
                if ($(e.target).hasClass('aepda-pda-lightbox-overlay') || $(e.target).hasClass('aepda-pda-lightbox-close')) {
                    aepdaPdaCloseLightbox($overlay);
                }
            });
            
            // ESC key
            $(document).on('keyup.aepdaPdaLightbox', function(e) {
                if (e.key === 'Escape') {
                    aepdaPdaCloseLightbox($overlay);
                }
            });
        }
        
        function aepdaPdaCloseLightbox($overlay) {
            $overlay.removeClass('aepda-pda-lightbox--active');
            $('body').removeClass('aepda-pda-no-scroll');
            
            setTimeout(function() {
                $overlay.remove();
                $(document).off('keyup.aepdaPdaLightbox');
            }, 300);
        }
    }

})(jQuery);

// Add lightbox styles dynamically with unique class names
(function() {
    var aepdaPdaStyles = `
        .aepda-pda-no-scroll {
            overflow: hidden !important;
        }
        
        .aepda-pda-lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .aepda-pda-lightbox--active {
            opacity: 1;
        }
        
        .aepda-pda-lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }
        
        .aepda-pda-lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            display: block;
            border-radius: 5px;
        }
        
        .aepda-pda-lightbox-close {
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
        
        .aepda-pda-lightbox-close:hover {
            opacity: 0.7;
        }
    `;
    
    var aepdaPdaStyleSheet = document.createElement('style');
    aepdaPdaStyleSheet.id = 'aepda-pda-lightbox-styles';
    aepdaPdaStyleSheet.type = 'text/css';
    aepdaPdaStyleSheet.textContent = aepdaPdaStyles;
    document.head.appendChild(aepdaPdaStyleSheet);
})();
