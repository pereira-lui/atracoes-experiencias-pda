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
    $blog_titulo = get_post_meta($post_id, '_atracao_blog_titulo', true);
    $blog_descricao = get_post_meta($post_id, '_atracao_blog_descricao', true);
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

<div id="aepda-pda-single-wrapper" class="aepda-pda-single-page">
    
    <!-- Imagem do Topo (Hero) -->
    <?php if ($imagem_topo) : 
        $imagem_topo_url = wp_get_attachment_image_url($imagem_topo, 'full');
        if ($imagem_topo_url) :
    ?>
    <div id="aepda-pda-hero-section" class="aepda-pda-hero">
        <img src="<?php echo esc_url($imagem_topo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="aepda-pda-hero-img">
    </div>
    <?php endif; endif; ?>
    
    <!-- Breadcrumb -->
    <div class="aepda-pda-breadcrumb">
        <div class="aepda-pda-container">
            <?php echo do_shortcode('[rank_math_breadcrumb]'); ?>
        </div>
    </div>
    
    <!-- Seção Principal: Título e Texto -->
    <section id="aepda-pda-main-section" class="aepda-pda-main-section">
        <div class="aepda-pda-container">
            <h1 class="aepda-pda-main-title"><?php echo esc_html(get_the_title()); ?></h1>
            <div class="aepda-pda-main-divider"></div>
            
            <?php if ($texto_sobre) : ?>
            <div class="aepda-pda-main-text">
                <?php echo wp_kses_post($texto_sobre); ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Galeria Carrossel (alinhado à esquerda com container, estende à direita) -->
    <?php if (!empty($galeria_ids)) : ?>
    <section id="aepda-pda-gallery-section" class="aepda-pda-gallery-section">
        <div class="aepda-pda-gallery-container">
            <div class="swiper aepda-pda-gallery-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($galeria_ids as $image_id) : 
                        $large_url = wp_get_attachment_image_url($image_id, 'large');
                        $full_url = wp_get_attachment_image_url($image_id, 'full');
                        if ($large_url) :
                    ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($full_url); ?>" class="aepda-pda-gallery-link" data-lightbox="gallery">
                            <img src="<?php echo esc_url($large_url); ?>" alt="">
                        </a>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Navegação do Swiper -->
        <div class="aepda-pda-container">
            <div class="aepda-pda-gallery-nav-wrapper">
                <button class="aepda-pda-gallery-nav aepda-pda-gallery-nav--prev" aria-label="Anterior">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="aepda-pda-gallery-nav aepda-pda-gallery-nav--next" aria-label="Próximo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Seção Matérias do Blog (Fundo Roxo) -->
    <?php if ($blog_descricao || $blog_imagem || !empty($blog_posts_ids)) : 
        $blog_section_title = !empty($blog_titulo) ? $blog_titulo : get_the_title();
    ?>
    <section id="aepda-pda-blog-section" class="aepda-pda-blog-section">
        <div class="aepda-pda-container">
            <div class="aepda-pda-blog-grid">
                <!-- Conteúdo do Blog -->
                <div class="aepda-pda-blog-content">
                    <h2 class="aepda-pda-blog-title"><?php echo esc_html($blog_section_title); ?></h2>
                    <div class="aepda-pda-blog-divider"></div>
                    
                    <?php if ($blog_descricao) : ?>
                        <div class="aepda-pda-blog-description"><?php echo wp_kses_post($blog_descricao); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Imagem do Blog -->
                <?php if ($blog_imagem) : 
                    $blog_imagem_url = wp_get_attachment_image_url($blog_imagem, 'medium_large');
                ?>
                <div class="aepda-pda-blog-image">
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
            <div class="aepda-pda-blog-cards">
                <?php foreach ($blog_posts_query as $blog_post) : 
                    $current_color = $card_colors[$color_index % $total_colors];
                    $color_index++;
                    $thumbnail_url = get_the_post_thumbnail_url($blog_post->ID, 'medium_large');
                ?>
                <a href="<?php echo esc_url(get_permalink($blog_post->ID)); ?>" class="aepda-pda-blog-card">
                    <?php if ($thumbnail_url) : ?>
                        <img class="aepda-pda-blog-card-image" src="<?php echo esc_url($thumbnail_url); ?>" alt="">
                    <?php else : ?>
                        <div class="aepda-pda-blog-card-placeholder"></div>
                    <?php endif; ?>
                    <span class="aepda-pda-blog-card-text" style="background-color: <?php echo esc_attr($current_color); ?>;">
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
    <section id="aepda-pda-rules-section" class="aepda-pda-rules-section">
        <div class="aepda-pda-container">
            <h2 class="aepda-pda-rules-title">
                <?php _e('Lembre-se de algumas regras para melhor', 'atracoes-experiencias-pda'); ?><br>
                <?php _e('experiência do seu passeio e para os animais:', 'atracoes-experiencias-pda'); ?>
            </h2>
            <div class="aepda-pda-rules-divider"></div>
            
            <div class="aepda-pda-rules-grid">
                <?php foreach ($regras_selecionadas as $regra_key) : 
                    if (isset($regras_disponiveis[$regra_key])) :
                        $regra = $regras_disponiveis[$regra_key];
                ?>
                <div class="aepda-pda-rule-item">
                    <div class="aepda-pda-rule-icon">
                        <span class="aepda-pda-icon aepda-pda-icon-<?php echo esc_attr($regra['icone']); ?>"></span>
                    </div>
                    <p class="aepda-pda-rule-text"><?php echo esc_html($regra['texto']); ?></p>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
</div><!-- .aepda-pda-single-page -->

<?php
endwhile;

get_footer();
