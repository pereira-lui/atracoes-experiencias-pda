<?php
/**
 * Atrações Grid Widget
 * 
 * Widget para listar Atrações e Experiências em formato de grid com cards
 *
 * @package Atracoes_Experiencias_PDA
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Atrações Grid Widget Class
 */
class Atracoes_Exp_PDA_Widget_Grid extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'atracoes_grid';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('PDA - Grid de Atrações', 'atracoes-experiencias-pda');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['atracoes-pda-widgets', 'pda-widgets', 'general'];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['atracoes', 'experiencias', 'grid', 'card', 'image', 'imagem', 'pda', 'viveiros'];
    }

    /**
     * Get style dependencies
     */
    public function get_style_depends() {
        return ['atracoes-exp-pda-style'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // ==============================
        // Content Tab - Query
        // ==============================
        
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Configurações da Query', 'atracoes-experiencias-pda'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Número de Itens', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 50,
            ]
        );

        // Obter categorias
        $categories = get_terms([
            'taxonomy' => 'atracao_categoria',
            'hide_empty' => false,
        ]);

        $category_options = ['' => __('Todas as Categorias', 'atracoes-experiencias-pda')];
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $category_options[$category->term_id] = $category->name;
            }
        }

        $this->add_control(
            'category',
            [
                'label' => __('Categoria', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $category_options,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Ordenar por', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Data', 'atracoes-experiencias-pda'),
                    'title' => __('Título', 'atracoes-experiencias-pda'),
                    'menu_order' => __('Menu Order', 'atracoes-experiencias-pda'),
                    'rand' => __('Aleatório', 'atracoes-experiencias-pda'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Ordem', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __('Decrescente', 'atracoes-experiencias-pda'),
                    'ASC' => __('Crescente', 'atracoes-experiencias-pda'),
                ],
            ]
        );

        $this->end_controls_section();

        // ==============================
        // Content Tab - Layout
        // ==============================
        
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'atracoes-experiencias-pda'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Colunas', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label' => __('Espaçamento', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==============================
        // Style Tab - Card
        // ==============================
        
        $this->start_controls_section(
            'style_card_section',
            [
                'label' => __('Card', 'atracoes-experiencias-pda'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'card_height',
            [
                'label' => __('Altura do Card', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 150,
                        'max' => 800,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 280,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_fit',
            [
                'label' => __('Ajuste da Imagem', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover' => __('Cover', 'atracoes-experiencias-pda'),
                    'contain' => __('Contain', 'atracoes-experiencias-pda'),
                    'fill' => __('Fill', 'atracoes-experiencias-pda'),
                    'none' => __('None', 'atracoes-experiencias-pda'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__image' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'image_position',
            [
                'label' => __('Posição da Imagem', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'center center',
                'options' => [
                    'top left' => __('Top Left', 'atracoes-experiencias-pda'),
                    'top center' => __('Top Center', 'atracoes-experiencias-pda'),
                    'top right' => __('Top Right', 'atracoes-experiencias-pda'),
                    'center left' => __('Center Left', 'atracoes-experiencias-pda'),
                    'center center' => __('Center Center', 'atracoes-experiencias-pda'),
                    'center right' => __('Center Right', 'atracoes-experiencias-pda'),
                    'bottom left' => __('Bottom Left', 'atracoes-experiencias-pda'),
                    'bottom center' => __('Bottom Center', 'atracoes-experiencias-pda'),
                    'bottom right' => __('Bottom Right', 'atracoes-experiencias-pda'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__image' => 'object-position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('Borda Arredondada', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '15',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => __('Sombra do Card', 'atracoes-experiencias-pda'),
                'selector' => '{{WRAPPER}} .aepda-card',
            ]
        );

        $this->end_controls_section();

        // ==============================
        // Style Tab - Texto
        // ==============================
        
        $this->start_controls_section(
            'style_text_section',
            [
                'label' => __('Texto', 'atracoes-experiencias-pda'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'use_custom_colors',
            [
                'label' => __('Usar cores personalizadas', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'atracoes-experiencias-pda'),
                'label_off' => __('Não', 'atracoes-experiencias-pda'),
                'return_value' => 'yes',
                'default' => '',
                'description' => __('Se desativado, usa as cores definidas no post.', 'atracoes-experiencias-pda'),
            ]
        );

        $this->add_control(
            'text_background_color',
            [
                'label' => __('Cor de Fundo', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#8B5CF6',
                'condition' => [
                    'use_custom_colors' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Cor do Texto', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'condition' => [
                    'use_custom_colors' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'label' => __('Tipografia', 'atracoes-experiencias-pda'),
                'selector' => '{{WRAPPER}} .aepda-card__text',
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label' => __('Alinhamento', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Esquerda', 'atracoes-experiencias-pda'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Centro', 'atracoes-experiencias-pda'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Direita', 'atracoes-experiencias-pda'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_padding',
            [
                'label' => __('Padding', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '12',
                    'right' => '20',
                    'bottom' => '12',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_margin',
            [
                'label' => __('Margem', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '0',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_border_radius',
            [
                'label' => __('Borda Arredondada do Texto', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '8',
                    'right' => '8',
                    'bottom' => '8',
                    'left' => '8',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card__text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==============================
        // Style Tab - Hover
        // ==============================
        
        $this->start_controls_section(
            'style_hover_section',
            [
                'label' => __('Hover', 'atracoes-experiencias-pda'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Animação', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->add_control(
            'image_hover_zoom',
            [
                'label' => __('Zoom na Imagem', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'atracoes-experiencias-pda'),
                'label_off' => __('Não', 'atracoes-experiencias-pda'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'image_hover_zoom_scale',
            [
                'label' => __('Escala do Zoom', 'atracoes-experiencias-pda'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 2,
                        'step' => 0.05,
                    ],
                ],
                'default' => [
                    'size' => 1.1,
                ],
                'condition' => [
                    'image_hover_zoom' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .aepda-card:hover .aepda-card__image' => 'transform: scale({{SIZE}});',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_hover_box_shadow',
                'label' => __('Sombra (Hover)', 'atracoes-experiencias-pda'),
                'selector' => '{{WRAPPER}} .aepda-card:hover',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Query arguments
        $args = [
            'post_type' => 'atracao_experiencia',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];

        // Filter by category
        if (!empty($settings['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'atracao_categoria',
                    'field' => 'term_id',
                    'terms' => $settings['category'],
                ],
            ];
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            echo '<p class="aepda-no-results">' . __('Nenhuma atração encontrada.', 'atracoes-experiencias-pda') . '</p>';
            return;
        }

        $use_custom_colors = $settings['use_custom_colors'] === 'yes';
        ?>

        <div class="aepda-grid">
            <?php while ($query->have_posts()) : $query->the_post();
                $post_id = get_the_ID();
                $title = get_the_title();
                $permalink = get_permalink();
                $thumbnail_id = get_post_thumbnail_id();
                $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';

                // Cores do card (do meta ou default)
                $card_bg_color = get_post_meta($post_id, '_atracao_card_cor_fundo', true);
                $card_text_color = get_post_meta($post_id, '_atracao_card_cor_texto', true);

                if (empty($card_bg_color)) $card_bg_color = '#8B5CF6';
                if (empty($card_text_color)) $card_text_color = '#FFFFFF';

                // Classes do card
                $card_classes = ['aepda-card'];
                if (!empty($settings['hover_animation'])) {
                    $card_classes[] = 'elementor-animation-' . $settings['hover_animation'];
                }

                // Inline styles para cores (apenas se não usar cores personalizadas)
                $inline_style = '';
                if (!$use_custom_colors) {
                    $inline_style = '--aepda-card-bg: ' . esc_attr($card_bg_color) . '; --aepda-card-color: ' . esc_attr($card_text_color) . ';';
                }
            ?>
                <a href="<?php echo esc_url($permalink); ?>" class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" style="<?php echo esc_attr($inline_style); ?>">
                    <?php if ($image_url) : ?>
                        <img class="aepda-card__image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                    <?php endif; ?>
                    <span class="aepda-card__text"><?php echo esc_html($title); ?></span>
                </a>
            <?php endwhile; ?>
        </div>

        <?php
        wp_reset_postdata();
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="aepda-grid">
            <div class="aepda-card aepda-card--preview">
                <span class="aepda-card__text"><?php _e('Os Pequenos Marrons', 'atracoes-experiencias-pda'); ?></span>
            </div>
            <div class="aepda-card aepda-card--preview">
                <span class="aepda-card__text"><?php _e('Aves do Rio e Manguezais', 'atracoes-experiencias-pda'); ?></span>
            </div>
            <div class="aepda-card aepda-card--preview">
                <span class="aepda-card__text"><?php _e('Mundo dos Tucanos', 'atracoes-experiencias-pda'); ?></span>
            </div>
            <div class="aepda-card aepda-card--preview">
                <span class="aepda-card__text"><?php _e('Corujaria', 'atracoes-experiencias-pda'); ?></span>
            </div>
        </div>
        <p class="elementor-panel-alert elementor-panel-alert-info">
            <?php _e('Este é um preview. Os cards reais serão carregados do banco de dados.', 'atracoes-experiencias-pda'); ?>
        </p>
        <?php
    }
}
