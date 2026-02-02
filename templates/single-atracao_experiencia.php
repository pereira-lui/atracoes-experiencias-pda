<?php
/**
 * Template para exibir uma única Atração/Experiência
 *
 * @package Atracoes_Experiencias_PDA
 * @version 1.0.0
 */

get_header();

while (have_posts()) :
    the_post();
    
    $post_id = get_the_ID();
    
    // Obter dados dos campos personalizados
    $imagem_topo = get_post_meta($post_id, '_atracao_imagem_topo', true);
    $titulo_sobre = get_post_meta($post_id, '_atracao_titulo_sobre', true);
    $subtitulo = get_post_meta($post_id, '_atracao_subtitulo', true);
    $texto_sobre = get_post_meta($post_id, '_atracao_texto_sobre', true);
    $galeria = get_post_meta($post_id, '_atracao_galeria', true);
    
    // Blog relacionado
    $blog_titulo = get_post_meta($post_id, '_atracao_blog_titulo', true);
    $blog_descricao = get_post_meta($post_id, '_atracao_blog_descricao', true);
    $blog_link_texto = get_post_meta($post_id, '_atracao_blog_link_texto', true);
    $blog_link_url = get_post_meta($post_id, '_atracao_blog_link_url', true);
    $blog_imagem = get_post_meta($post_id, '_atracao_blog_imagem', true);
    
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
    
    <!-- Imagem do Topo -->
    <?php if ($imagem_topo) : 
        $imagem_topo_url = wp_get_attachment_image_url($imagem_topo, 'full');
    ?>
    <div class="aepda-hero-image">
        <img src="<?php echo esc_url($imagem_topo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
    </div>
    <?php endif; ?>
    
    <div class="aepda-single-container">
        
        <!-- Breadcrumb usando Rank Math -->
        <div class="aepda-breadcrumb">
            <?php echo do_shortcode('[rank_math_breadcrumb]'); ?>
        </div>
        
        <!-- Seção de Conteúdo Principal -->
        <section class="aepda-content-section">
            
            <!-- Título e Subtítulo -->
            <div class="aepda-title-block">
                <h1 class="aepda-page-title"><?php echo esc_html($titulo_sobre ? $titulo_sobre : get_the_title()); ?></h1>
                <?php if ($subtitulo) : ?>
                    <p class="aepda-page-subtitle"><?php echo esc_html($subtitulo); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Textos Sobre -->
            <?php if ($texto_sobre) : ?>
            <div class="aepda-texto-sobre">
                <?php echo wp_kses_post($texto_sobre); ?>
            </div>
            <?php endif; ?>
            
        </section>
        
        <!-- Galeria de Imagens -->
        <?php if (!empty($galeria_ids)) : ?>
        <section class="aepda-gallery-section">
            <div class="aepda-gallery-wrapper">
                <div class="aepda-gallery-slider" id="aepda-gallery-slider">
                    <?php foreach ($galeria_ids as $image_id) : 
                        $image_url = wp_get_attachment_image_url($image_id, 'large');
                        $image_full = wp_get_attachment_image_url($image_id, 'full');
                        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        if ($image_url) :
                    ?>
                    <div class="aepda-gallery-item">
                        <a href="<?php echo esc_url($image_full); ?>" data-lightbox="gallery">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                        </a>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
                
                <!-- Navegação da Galeria -->
                <div class="aepda-gallery-nav">
                    <button class="aepda-gallery-prev" aria-label="<?php esc_attr_e('Anterior', 'atracoes-experiencias-pda'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="aepda-gallery-next" aria-label="<?php esc_attr_e('Próximo', 'atracoes-experiencias-pda'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
    </div><!-- .aepda-single-container -->
    
    <!-- Seção Matérias do Blog (Fundo Roxo) -->
    <?php if ($blog_titulo || $blog_descricao || $blog_imagem) : ?>
    <section class="aepda-blog-section">
        <div class="aepda-blog-section__inner">
            <div class="aepda-blog-section__content">
                <?php if ($blog_titulo) : ?>
                    <h2 class="aepda-blog-section__title"><?php echo esc_html($blog_titulo); ?></h2>
                    <div class="aepda-blog-section__divider"></div>
                <?php endif; ?>
                
                <?php if ($blog_descricao) : ?>
                    <p class="aepda-blog-section__description"><?php echo esc_html($blog_descricao); ?></p>
                <?php endif; ?>
                
                <?php if ($blog_link_texto && $blog_link_url) : ?>
                    <a href="<?php echo esc_url($blog_link_url); ?>" class="aepda-blog-section__link">
                        <?php echo esc_html($blog_link_texto); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($blog_imagem) : 
                $blog_imagem_url = wp_get_attachment_image_url($blog_imagem, 'medium_large');
            ?>
            <div class="aepda-blog-section__image">
                <img src="<?php echo esc_url($blog_imagem_url); ?>" alt="<?php echo esc_attr($blog_titulo); ?>">
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Seção de Regras -->
    <?php if (!empty($regras_selecionadas)) : ?>
    <section class="aepda-rules-section">
        <div class="aepda-rules-section__inner">
            <h2 class="aepda-rules-section__title">
                <?php _e('Lembre-se de algumas regras para melhor<br>experiência do seu passeio e para os animais:', 'atracoes-experiencias-pda'); ?>
            </h2>
            <div class="aepda-rules-section__divider"></div>
            
            <div class="aepda-rules-grid">
                <?php foreach ($regras_selecionadas as $regra_key) : 
                    if (isset($regras_disponiveis[$regra_key])) :
                        $regra = $regras_disponiveis[$regra_key];
                ?>
                <div class="aepda-rule-item">
                    <div class="aepda-rule-item__icon">
                        <span class="aepda-icon aepda-icon-<?php echo esc_attr($regra['icone']); ?>"></span>
                    </div>
                    <span class="aepda-rule-item__text"><?php echo esc_html($regra['texto']); ?></span>
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
