<?php
/**
 * Template para exibir uma única Atração/Experiência
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.3.0
 */

get_header();

while (have_posts()) :
    the_post();
    
    $post_id = get_the_ID();
    
    // Obter dados dos campos personalizados
    $imagem_topo = get_post_meta($post_id, '_atracao_imagem_topo', true);
    $texto_sobre = get_post_meta($post_id, '_atracao_texto_sobre', true);
    $galeria = get_post_meta($post_id, '_atracao_galeria', true);
    
    // Blog relacionado
    $blog_descricao = get_post_meta($post_id, '_atracao_blog_descricao', true);
    $blog_link_texto = get_post_meta($post_id, '_atracao_blog_link_texto', true);
    $blog_link_url = get_post_meta($post_id, '_atracao_blog_link_url', true);
    $blog_imagem = get_post_meta($post_id, '_atracao_blog_imagem', true);
    $blog_posts_ids = get_post_meta($post_id, '_atracao_blog_posts', true);
    if (!is_array($blog_posts_ids)) {
        $blog_posts_ids = [];
    }
    
    // Regras selecionadas
    $regras_selecionadas = get_post_meta($post_id, '_atracao_regras_selecionadas', true);
    if (!is_array($regras_selecionadas)) {
        $regras_selecionadas = [];
    }
    
    // Obter lista de regras disponíveis
    $regras_disponiveis = Atracoes_Experiencias_PDA::instance()->get_regras_disponiveis();
    
    // Preparar galeria
    $galeria_ids = !empty($galeria) ? explode(',', $galeria) : [];
?>

<div class="aepda-single-page">
    
    <!-- Imagem do Topo (Hero) -->
    <?php if ($imagem_topo) : 
        $imagem_topo_url = wp_get_attachment_image_url($imagem_topo, 'full');
    ?>
    <div class="aepda-hero">
        <img src="<?php echo esc_url($imagem_topo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="aepda-hero__image">
    </div>
    <?php endif; ?>
    
    <!-- Breadcrumb -->
    <div class="aepda-breadcrumb-wrapper">
        <div class="aepda-container">
            <?php echo do_shortcode('[rank_math_breadcrumb]'); ?>
        </div>
    </div>
    
    <!-- Seção Principal: Título/Texto + Galeria lado a lado -->
    <section class="aepda-main-section">
        <div class="aepda-container">
            <div class="aepda-main-grid">
                
                <!-- Coluna Esquerda: Título e Texto -->
                <div class="aepda-main-content">
                    <h1 class="aepda-main-title"><?php echo esc_html(get_the_title()); ?></h1>
                    <div class="aepda-main-divider"></div>
                    
                    <?php if ($texto_sobre) : ?>
                    <div class="aepda-main-text">
                        <?php echo wp_kses_post($texto_sobre); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Coluna Direita: Galeria com Swiper -->
                <?php if (!empty($galeria_ids)) : ?>
                <div class="aepda-main-gallery">
                    <!-- Swiper Principal -->
                    <div class="swiper aepda-gallery-main">
                        <div class="swiper-wrapper">
                            <?php foreach ($galeria_ids as $image_id) : 
                                $large_url = wp_get_attachment_image_url($image_id, 'large');
                                $full_url = wp_get_attachment_image_url($image_id, 'full');
                                if ($large_url) :
                            ?>
                            <div class="swiper-slide">
                                <a href="<?php echo esc_url($full_url); ?>" class="aepda-gallery__link" data-lightbox="gallery">
                                    <img src="<?php echo esc_url($large_url); ?>" alt="">
                                </a>
                            </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Swiper Thumbs -->
                    <div class="aepda-gallery-thumbs-wrapper">
                        <button class="aepda-gallery-nav aepda-gallery-nav--prev" aria-label="Anterior">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        
                        <div class="swiper aepda-gallery-thumbs">
                            <div class="swiper-wrapper">
                                <?php foreach ($galeria_ids as $image_id) : 
                                    $thumb_url = wp_get_attachment_image_url($image_id, 'medium');
                                    if ($thumb_url) :
                                ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                </div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        
                        <button class="aepda-gallery-nav aepda-gallery-nav--next" aria-label="Próximo">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </section>
    
    <!-- Seção Matérias do Blog (Fundo Roxo) -->
    <?php if ($blog_descricao || $blog_imagem || !empty($blog_posts_ids)) : ?>
    <section class="aepda-blog-section">
        <div class="aepda-container">
            <div class="aepda-blog-grid">
                <!-- Conteúdo do Blog -->
                <div class="aepda-blog-content">
                    <h2 class="aepda-blog-title"><?php _e('Matérias do Blog', 'atracoes-experiencias-pda'); ?></h2>
                    <div class="aepda-blog-divider"></div>
                    
                    <?php if ($blog_descricao) : ?>
                        <p class="aepda-blog-description"><?php echo esc_html($blog_descricao); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($blog_link_texto && $blog_link_url) : ?>
                        <a href="<?php echo esc_url($blog_link_url); ?>" class="aepda-blog-link">
                            <?php echo esc_html($blog_link_texto); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Imagem do Blog -->
                <?php if ($blog_imagem) : 
                    $blog_imagem_url = wp_get_attachment_image_url($blog_imagem, 'medium_large');
                ?>
                <div class="aepda-blog-image">
                    <img src="<?php echo esc_url($blog_imagem_url); ?>" alt="">
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Cards de Posts do Blog -->
            <?php if (!empty($blog_posts_ids)) : 
                $card_colors = [
                    '#0891B2', // Azul/Ciano
                    '#7C3AED', // Roxo
                    '#DB2777', // Rosa
                    '#EA580C', // Laranja
                    '#16A34A', // Verde
                    '#CA8A04', // Amarelo
                ];
                $color_index = 0;
                $total_colors = count($card_colors);
                
                $blog_posts_query = get_posts([
                    'post_type' => 'blog_post',
                    'post__in' => $blog_posts_ids,
                    'orderby' => 'post__in',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                ]);
            ?>
            <div class="aepda-blog-cards">
                <?php foreach ($blog_posts_query as $blog_post) : 
                    $current_color = $card_colors[$color_index % $total_colors];
                    $color_index++;
                    $thumbnail_url = get_the_post_thumbnail_url($blog_post->ID, 'medium_large');
                ?>
                <a href="<?php echo esc_url(get_permalink($blog_post->ID)); ?>" class="aepda-blog-card">
                    <?php if ($thumbnail_url) : ?>
                        <img class="aepda-blog-card__image" src="<?php echo esc_url($thumbnail_url); ?>" alt="">
                    <?php else : ?>
                        <div class="aepda-blog-card__placeholder"></div>
                    <?php endif; ?>
                    <span class="aepda-blog-card__text" style="background-color: <?php echo esc_attr($current_color); ?>;">
                        <?php echo esc_html($blog_post->post_title); ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Seção de Regras -->
    <?php if (!empty($regras_selecionadas)) : ?>
    <section class="aepda-rules-section">
        <div class="aepda-container">
            <h2 class="aepda-rules-title">
                <?php _e('Lembre-se de algumas regras para melhor', 'atracoes-experiencias-pda'); ?><br>
                <?php _e('experiência do seu passeio e para os animais:', 'atracoes-experiencias-pda'); ?>
            </h2>
            <div class="aepda-rules-divider"></div>
            
            <div class="aepda-rules-grid">
                <?php foreach ($regras_selecionadas as $regra_key) : 
                    if (isset($regras_disponiveis[$regra_key])) :
                        $regra = $regras_disponiveis[$regra_key];
                ?>
                <div class="aepda-rule-item">
                    <div class="aepda-rule-icon">
                        <span class="aepda-icon aepda-icon-<?php echo esc_attr($regra['icone']); ?>"></span>
                    </div>
                    <p class="aepda-rule-text"><?php echo esc_html($regra['texto']); ?></p>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
</div><!-- .aepda-single-page -->

<?php
endwhile;

get_footer();
