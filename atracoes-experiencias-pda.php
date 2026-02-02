<?php
/**
 * Plugin Name: Atrações e Experiências PDA
 * Plugin URI: https://github.com/pereira-lui/atracoes-experiencias-pda
 * Description: Plugin para gerenciar Custom Post Type "Atrações e Experiências" com campos personalizados e widget para Elementor.
 * Version: 1.4.1
 * Author: Lui
 * Author URI: https://github.com/pereira-lui
 * Text Domain: atracoes-experiencias-pda
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/pereira-lui/atracoes-experiencias-pda
 * GitHub Branch: main
 * Update URI: https://github.com/pereira-lui/atracoes-experiencias-pda
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('ATRACOES_EXP_PDA_VERSION', '1.4.1');
define('ATRACOES_EXP_PDA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ATRACOES_EXP_PDA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ATRACOES_EXP_PDA_PLUGIN_FILE', __FILE__);

/**
 * Main Atrações e Experiências PDA Class
 */
final class Atracoes_Experiencias_PDA {

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Singleton Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Load translation
        add_action('init', [$this, 'load_textdomain']);

        // Register Custom Post Type
        add_action('init', [$this, 'register_custom_post_type']);

        // Add Meta Boxes
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 1);
        
        // Add nonce field via edit_form_after_title to ensure it's always present
        add_action('edit_form_after_title', [$this, 'add_nonce_field']);

        // Reordenar meta boxes - Rank Math por último
        add_action('add_meta_boxes', [$this, 'reorder_meta_boxes'], 99);

        // Check for Elementor
        if ($this->is_compatible()) {
            add_action('elementor/init', [$this, 'init_elementor']);
        }

        // Include GitHub updater
        $this->includes();

        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Enqueue editor assets
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_assets']);

        // Template filter for single post
        add_filter('single_template', [$this, 'load_single_template']);
    }

    /**
     * Reordenar meta boxes para colocar Rank Math por último
     */
    public function reorder_meta_boxes() {
        global $wp_meta_boxes;
        
        if (!isset($wp_meta_boxes['atracao_experiencia'])) {
            return;
        }
        
        // Procurar o metabox do Rank Math e movê-lo para baixo
        foreach (['normal', 'side', 'advanced'] as $context) {
            foreach (['high', 'core', 'default', 'low'] as $priority) {
                if (isset($wp_meta_boxes['atracao_experiencia'][$context][$priority])) {
                    foreach ($wp_meta_boxes['atracao_experiencia'][$context][$priority] as $id => $metabox) {
                        // Rank Math meta box
                        if (strpos($id, 'rank_math') !== false || $id === 'rank_math_metabox') {
                            // Remove e adiciona novamente com prioridade baixa
                            $saved_metabox = $wp_meta_boxes['atracao_experiencia'][$context][$priority][$id];
                            unset($wp_meta_boxes['atracao_experiencia'][$context][$priority][$id]);
                            $wp_meta_boxes['atracao_experiencia'][$context]['low'][$id] = $saved_metabox;
                        }
                    }
                }
            }
        }
    }

    /**
     * Load custom single template
     */
    public function load_single_template($template) {
        global $post;
        
        if ($post->post_type === 'atracao_experiencia') {
            $plugin_template = ATRACOES_EXP_PDA_PLUGIN_DIR . 'templates/single-atracao_experiencia.php';
            
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('atracoes-experiencias-pda', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Register Custom Post Type - Atrações e Experiências
     */
    public function register_custom_post_type() {
        $labels = [
            'name'                  => _x('Atrações e Experiências', 'Post Type General Name', 'atracoes-experiencias-pda'),
            'singular_name'         => _x('Atração/Experiência', 'Post Type Singular Name', 'atracoes-experiencias-pda'),
            'menu_name'             => __('Atrações', 'atracoes-experiencias-pda'),
            'name_admin_bar'        => __('Atração/Experiência', 'atracoes-experiencias-pda'),
            'archives'              => __('Arquivos de Atrações', 'atracoes-experiencias-pda'),
            'attributes'            => __('Atributos da Atração', 'atracoes-experiencias-pda'),
            'parent_item_colon'     => __('Atração Pai:', 'atracoes-experiencias-pda'),
            'all_items'             => __('Todas as Atrações', 'atracoes-experiencias-pda'),
            'add_new_item'          => __('Adicionar Nova Atração', 'atracoes-experiencias-pda'),
            'add_new'               => __('Adicionar Nova', 'atracoes-experiencias-pda'),
            'new_item'              => __('Nova Atração', 'atracoes-experiencias-pda'),
            'edit_item'             => __('Editar Atração', 'atracoes-experiencias-pda'),
            'update_item'           => __('Atualizar Atração', 'atracoes-experiencias-pda'),
            'view_item'             => __('Ver Atração', 'atracoes-experiencias-pda'),
            'view_items'            => __('Ver Atrações', 'atracoes-experiencias-pda'),
            'search_items'          => __('Buscar Atrações', 'atracoes-experiencias-pda'),
            'not_found'             => __('Não encontrada', 'atracoes-experiencias-pda'),
            'not_found_in_trash'    => __('Não encontrada na lixeira', 'atracoes-experiencias-pda'),
            'featured_image'        => __('Imagem Principal', 'atracoes-experiencias-pda'),
            'set_featured_image'    => __('Definir imagem principal', 'atracoes-experiencias-pda'),
            'remove_featured_image' => __('Remover imagem principal', 'atracoes-experiencias-pda'),
            'use_featured_image'    => __('Usar como imagem principal', 'atracoes-experiencias-pda'),
            'insert_into_item'      => __('Inserir na atração', 'atracoes-experiencias-pda'),
            'uploaded_to_this_item' => __('Enviado para esta atração', 'atracoes-experiencias-pda'),
            'items_list'            => __('Lista de atrações', 'atracoes-experiencias-pda'),
            'items_list_navigation' => __('Navegação da lista de atrações', 'atracoes-experiencias-pda'),
            'filter_items_list'     => __('Filtrar lista de atrações', 'atracoes-experiencias-pda'),
        ];

        $args = [
            'label'                 => __('Atração/Experiência', 'atracoes-experiencias-pda'),
            'description'           => __('Atrações e Experiências do Parque', 'atracoes-experiencias-pda'),
            'labels'                => $labels,
            'supports'              => ['title', 'revisions'], // Apenas título e revisões
            'taxonomies'            => [], // Removido categorias
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-palmtree',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => false, // Desabilita Gutenberg para usar meta boxes clássicos
            'rewrite'               => ['slug' => 'atracoes-experiencias'],
        ];

        register_post_type('atracao_experiencia', $args);
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        // Meta Box - Imagem do Topo
        add_meta_box(
            'aepda_metabox_imagem_topo',
            __('Imagem do Topo', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_imagem_topo'],
            'atracao_experiencia',
            'normal',
            'high'
        );

        // Meta Box - Textos Sobre (Título e Conteúdo)
        add_meta_box(
            'atracao_textos_sobre',
            __('Textos Sobre', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_textos_sobre'],
            'atracao_experiencia',
            'normal',
            'high'
        );

        // Meta Box - Galeria de Imagens
        add_meta_box(
            'aepda_metabox_galeria',
            __('Galeria de Imagens', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_galeria'],
            'atracao_experiencia',
            'normal',
            'default'
        );

        // Meta Box - Configurações do Card
        add_meta_box(
            'atracao_card_config',
            __('Configurações do Card (Listagem)', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_card_config'],
            'atracao_experiencia',
            'side',
            'default'
        );

        // Meta Box - Seção de Blog/Matérias
        add_meta_box(
            'atracao_blog_relacionado',
            __('Matérias do Blog', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_blog_relacionado'],
            'atracao_experiencia',
            'normal',
            'default'
        );

        // Meta Box - Regras para Visitantes (Checkboxes)
        add_meta_box(
            'atracao_regras',
            __('Regras para Melhor Experiência', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_regras'],
            'atracao_experiencia',
            'normal',
            'default'
        );
    }

    /**
     * Add nonce field to edit form
     */
    public function add_nonce_field($post) {
        if ($post->post_type !== 'atracao_experiencia') {
            return;
        }
        wp_nonce_field('atracao_meta_box', 'atracao_meta_box_nonce');
    }

    /**
     * Render Meta Box - Imagem do Topo
     */
    public function render_meta_box_imagem_topo($post) {
        $imagem_topo = get_post_meta($post->ID, '_atracao_imagem_topo', true);
        ?>
        <div class="atracao-imagem-topo-wrapper">
            <p class="description"><?php _e('Esta imagem será exibida no topo da página, em formato widescreen.', 'atracoes-experiencias-pda'); ?></p>
            <div class="atracao-image-upload">
                <input type="hidden" id="atracao_imagem_topo" name="atracao_imagem_topo" value="<?php echo esc_attr($imagem_topo); ?>">
                <div id="atracao-imagem-topo-preview" class="atracao-image-preview atracao-image-preview--large">
                    <?php
                    if ($imagem_topo) {
                        $image_url = wp_get_attachment_image_url($imagem_topo, 'large');
                        if ($image_url) {
                            echo '<img src="' . esc_url($image_url) . '" alt="">';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button button-primary atracao-image-upload-btn" data-target="atracao_imagem_topo" data-preview="atracao-imagem-topo-preview">
                    <?php _e('Selecionar Imagem do Topo', 'atracoes-experiencias-pda'); ?>
                </button>
                <button type="button" class="button atracao-image-remove-btn" data-target="atracao_imagem_topo" data-preview="atracao-imagem-topo-preview" <?php echo empty($imagem_topo) ? 'style="display:none;"' : ''; ?>>
                    <?php _e('Remover', 'atracoes-experiencias-pda'); ?>
                </button>
            </div>
            <p class="description"><?php _e('Recomendado: 1920x600px ou proporção similar.', 'atracoes-experiencias-pda'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Meta Box - Textos Sobre
     */
    public function render_meta_box_textos_sobre($post) {
        $texto_sobre = get_post_meta($post->ID, '_atracao_texto_sobre', true);
        ?>
        <p class="description" style="margin-bottom: 15px; padding: 10px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 0 4px 4px 0;">
            <strong><?php _e('Título:', 'atracoes-experiencias-pda'); ?></strong> 
            <?php echo esc_html(get_the_title($post->ID)); ?>
            <em>(<?php _e('usa o título da página automaticamente', 'atracoes-experiencias-pda'); ?>)</em>
        </p>
        <table class="form-table atracao-meta-table">
            <tr>
                <th><label for="atracao_texto_sobre"><?php _e('Textos Sobre', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <?php
                    wp_editor($texto_sobre, 'atracao_texto_sobre', [
                        'textarea_name' => 'atracao_texto_sobre',
                        'textarea_rows' => 8,
                        'media_buttons' => true,
                        'teeny' => false,
                        'quicktags' => true,
                    ]);
                    ?>
                    <p class="description"><?php _e('Conteúdo descritivo sobre a atração/experiência. Use negrito para destacar a primeira frase.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Meta Box - Galeria de Imagens
     */
    public function render_meta_box_galeria($post) {
        $galeria = get_post_meta($post->ID, '_atracao_galeria', true);
        $galeria_ids = !empty($galeria) ? explode(',', $galeria) : [];
        ?>
        <div class="atracao-galeria-wrapper">
            <div id="atracao-galeria-preview" class="atracao-galeria-preview">
                <?php
                if (!empty($galeria_ids)) {
                    foreach ($galeria_ids as $image_id) {
                        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        if ($image_url) {
                            echo '<div class="atracao-galeria-item" data-id="' . esc_attr($image_id) . '">';
                            echo '<img src="' . esc_url($image_url) . '" alt="">';
                            echo '<button type="button" class="atracao-galeria-remove">&times;</button>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            <input type="hidden" id="atracao_galeria" name="atracao_galeria" value="<?php echo esc_attr($galeria); ?>">
            <button type="button" class="button atracao-galeria-add" id="atracao-galeria-add">
                <?php _e('Adicionar Imagens à Galeria', 'atracoes-experiencias-pda'); ?>
            </button>
            <p class="description"><?php _e('Adicione imagens para a galeria da atração.', 'atracoes-experiencias-pda'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Meta Box - Configurações do Card
     */
    public function render_meta_box_card_config($post) {
        $card_imagem = get_post_meta($post->ID, '_atracao_card_imagem', true);
        $card_texto = get_post_meta($post->ID, '_atracao_card_texto', true);
        
        // Texto padrão: título da página
        if (empty($card_texto)) {
            $card_texto = get_the_title($post->ID);
        }
        ?>
        
        <!-- Imagem do Card -->
        <div class="atracao-card-image-wrapper" style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                <?php _e('Imagem do Card (Widget)', 'atracoes-experiencias-pda'); ?>
            </label>
            <div class="atracao-image-upload">
                <input type="hidden" id="atracao_card_imagem" name="atracao_card_imagem" value="<?php echo esc_attr($card_imagem); ?>">
                <div id="atracao-card-imagem-preview" class="atracao-image-preview atracao-image-preview--card">
                    <?php
                    if ($card_imagem) {
                        $image_url = wp_get_attachment_image_url($card_imagem, 'medium');
                        if ($image_url) {
                            echo '<img src="' . esc_url($image_url) . '" alt="">';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button atracao-image-upload-btn" data-target="atracao_card_imagem" data-preview="atracao-card-imagem-preview">
                    <?php _e('Selecionar Imagem', 'atracoes-experiencias-pda'); ?>
                </button>
                <button type="button" class="button atracao-image-remove-btn" data-target="atracao_card_imagem" data-preview="atracao-card-imagem-preview" <?php echo empty($card_imagem) ? 'style="display:none;"' : ''; ?>>
                    <?php _e('Remover', 'atracoes-experiencias-pda'); ?>
                </button>
            </div>
            <p class="description"><?php _e('Imagem que aparece na listagem de cards do widget.', 'atracoes-experiencias-pda'); ?></p>
        </div>
        
        <!-- Texto do Card -->
        <p style="margin-bottom: 15px;">
            <label for="atracao_card_texto" style="display: block; font-weight: 600; margin-bottom: 5px;">
                <?php _e('Texto do Card', 'atracoes-experiencias-pda'); ?>
            </label>
            <input type="text" id="atracao_card_texto" name="atracao_card_texto" value="<?php echo esc_attr($card_texto); ?>" class="widefat" placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>">
            <span class="description"><?php _e('Texto exibido no card. Por padrão usa o título da página.', 'atracoes-experiencias-pda'); ?></span>
        </p>
        
        <p class="description" style="margin-top: 15px; padding: 10px; background: #f0f0f1; border-radius: 4px;">
            <strong><?php _e('Nota:', 'atracoes-experiencias-pda'); ?></strong>
            <?php _e('As cores dos cards são aplicadas automaticamente de forma intercalada (azul, roxo, verde, amarelo, rosa).', 'atracoes-experiencias-pda'); ?>
        </p>
        <?php
    }

    /**
     * Obter lista de regras disponíveis
     */
    public function get_regras_disponiveis() {
        return [
            'nao_alimentar' => [
                'texto' => __('Não alimentar os animais', 'atracoes-experiencias-pda'),
                'icone' => 'nao-alimentar'
            ],
            'nao_tocar' => [
                'texto' => __('Não tocar nos animais', 'atracoes-experiencias-pda'),
                'icone' => 'nao-tocar'
            ],
            'nao_sair_trilha' => [
                'texto' => __('Não sair da trilha', 'atracoes-experiencias-pda'),
                'icone' => 'nao-sair-trilha'
            ],
            'fotografar_sem_flash' => [
                'texto' => __('É permitido fotografar e fazer vídeos, mas não usar flash', 'atracoes-experiencias-pda'),
                'icone' => 'sem-flash'
            ],
            'manter_silencio' => [
                'texto' => __('Manter silêncio em alguns pontos para ouvir o barulho da floresta e dos animais', 'atracoes-experiencias-pda'),
                'icone' => 'silencio'
            ],
        ];
    }

    /**
     * Render Meta Box - Regras para Visitantes
     */
    public function render_meta_box_regras($post) {
        $regras_selecionadas = get_post_meta($post->ID, '_atracao_regras_selecionadas', true);
        if (!is_array($regras_selecionadas)) {
            $regras_selecionadas = [];
        }
        
        $regras_disponiveis = $this->get_regras_disponiveis();
        ?>
        <div class="atracao-regras-wrapper">
            <p class="description" style="margin-bottom: 15px;">
                <?php _e('Selecione as regras que deseja exibir nesta atração/experiência:', 'atracoes-experiencias-pda'); ?>
            </p>
            <div class="atracao-regras-checkboxes">
                <?php foreach ($regras_disponiveis as $key => $regra) : ?>
                    <label class="atracao-regra-checkbox">
                        <input type="checkbox" 
                               name="atracao_regras_selecionadas[]" 
                               value="<?php echo esc_attr($key); ?>"
                               <?php checked(in_array($key, $regras_selecionadas)); ?>>
                        <span class="atracao-regra-checkbox-icon atracao-icon-<?php echo esc_attr($regra['icone']); ?>"></span>
                        <span class="atracao-regra-checkbox-text"><?php echo esc_html($regra['texto']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Meta Box - Blog Relacionado
     */
    public function render_meta_box_blog_relacionado($post) {
        $blog_descricao = get_post_meta($post->ID, '_atracao_blog_descricao', true);
        $blog_link_texto = get_post_meta($post->ID, '_atracao_blog_link_texto', true);
        $blog_link_url = get_post_meta($post->ID, '_atracao_blog_link_url', true);
        $blog_imagem = get_post_meta($post->ID, '_atracao_blog_imagem', true);
        $blog_posts_selecionados = get_post_meta($post->ID, '_atracao_blog_posts', true);
        if (!is_array($blog_posts_selecionados)) {
            $blog_posts_selecionados = [];
        }
        
        // Buscar posts do blog para o seletor
        $blog_posts = get_posts([
            'post_type' => 'blog_post',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish',
        ]);
        ?>
        <p class="description" style="margin-bottom: 15px;">
            <strong><?php _e('Título da Seção:', 'atracoes-experiencias-pda'); ?></strong> 
            <?php echo esc_html(get_the_title($post->ID)); ?>
            <em>(<?php _e('usa o título da página automaticamente', 'atracoes-experiencias-pda'); ?>)</em>
        </p>
        <table class="form-table atracao-meta-table">
            <tr>
                <th><label for="atracao_blog_descricao"><?php _e('Descrição', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <textarea id="atracao_blog_descricao" name="atracao_blog_descricao" class="widefat" rows="3" placeholder="Ex: O Guan-etê das "jacus-do-mato" dá nome às jacutingas..."><?php echo esc_textarea($blog_descricao); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="atracao_blog_link_texto"><?php _e('Texto do Link', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_blog_link_texto" name="atracao_blog_link_texto" value="<?php echo esc_attr($blog_link_texto); ?>" class="widefat" placeholder="Ex: Confira nossa matéria completa no blog!">
                </td>
            </tr>
            <tr>
                <th><label for="atracao_blog_link_url"><?php _e('URL do Link', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="url" id="atracao_blog_link_url" name="atracao_blog_link_url" value="<?php echo esc_url($blog_link_url); ?>" class="widefat" placeholder="https://...">
                </td>
            </tr>
            <tr>
                <th><label for="atracao_blog_imagem"><?php _e('Imagem da Seção', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <div class="atracao-image-upload">
                        <input type="hidden" id="atracao_blog_imagem" name="atracao_blog_imagem" value="<?php echo esc_attr($blog_imagem); ?>">
                        <div id="atracao-blog-imagem-preview" class="atracao-image-preview">
                            <?php
                            if ($blog_imagem) {
                                $image_url = wp_get_attachment_image_url($blog_imagem, 'medium');
                                if ($image_url) {
                                    echo '<img src="' . esc_url($image_url) . '" alt="">';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button atracao-image-upload-btn" data-target="atracao_blog_imagem" data-preview="atracao-blog-imagem-preview">
                            <?php _e('Selecionar Imagem', 'atracoes-experiencias-pda'); ?>
                        </button>
                        <button type="button" class="button atracao-image-remove-btn" data-target="atracao_blog_imagem" data-preview="atracao-blog-imagem-preview" <?php echo empty($blog_imagem) ? 'style="display:none;"' : ''; ?>>
                            <?php _e('Remover', 'atracoes-experiencias-pda'); ?>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="atracao-blog-posts-section">
            <h4 class="atracao-blog-posts-title">
                <?php _e('Posts do Blog para Exibir como Cards', 'atracoes-experiencias-pda'); ?>
            </h4>
            <p class="description">
                <?php _e('Selecione os posts do blog que aparecerão como cards abaixo desta seção.', 'atracoes-experiencias-pda'); ?>
            </p>
            
            <?php if (!empty($blog_posts)) : ?>
            
            <!-- Posts Selecionados -->
            <div class="atracao-blog-posts-selected">
                <label class="atracao-blog-posts-label">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php _e('Selecionados', 'atracoes-experiencias-pda'); ?>
                    <span class="atracao-blog-posts-count" id="selected-count">(<?php echo count($blog_posts_selecionados); ?>)</span>
                </label>
                <div class="atracao-blog-posts-selected-list" id="blog-posts-selected-list">
                    <?php 
                    if (!empty($blog_posts_selecionados)) :
                        foreach ($blog_posts as $blog_post) :
                            if (in_array($blog_post->ID, $blog_posts_selecionados)) :
                    ?>
                    <div class="atracao-blog-post-item atracao-blog-post-item--selected" data-id="<?php echo esc_attr($blog_post->ID); ?>">
                        <input type="checkbox" 
                               name="atracao_blog_posts[]" 
                               value="<?php echo esc_attr($blog_post->ID); ?>"
                               checked
                               class="atracao-blog-post-checkbox">
                        <span class="atracao-blog-post-title"><?php echo esc_html($blog_post->post_title); ?></span>
                        <button type="button" class="atracao-blog-post-remove" title="<?php _e('Remover', 'atracoes-experiencias-pda'); ?>">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <?php 
                            endif;
                        endforeach;
                    else :
                    ?>
                    <p class="atracao-blog-posts-empty" id="selected-empty"><?php _e('Nenhum post selecionado', 'atracoes-experiencias-pda'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Campo de Pesquisa -->
            <div class="atracao-blog-posts-search">
                <label class="atracao-blog-posts-label">
                    <span class="dashicons dashicons-search"></span>
                    <?php _e('Adicionar Posts', 'atracoes-experiencias-pda'); ?>
                </label>
                <input type="text" 
                       id="blog-posts-search" 
                       class="atracao-blog-posts-search-input" 
                       placeholder="<?php _e('Pesquisar posts do blog...', 'atracoes-experiencias-pda'); ?>">
            </div>
            
            <!-- Lista de Posts Disponíveis -->
            <div class="atracao-blog-posts-available">
                <div class="atracao-blog-posts-list" id="blog-posts-list">
                    <?php foreach ($blog_posts as $blog_post) : 
                        $is_selected = in_array($blog_post->ID, $blog_posts_selecionados);
                    ?>
                    <div class="atracao-blog-post-item <?php echo $is_selected ? 'atracao-blog-post-item--hidden' : ''; ?>" 
                         data-id="<?php echo esc_attr($blog_post->ID); ?>"
                         data-title="<?php echo esc_attr(strtolower($blog_post->post_title)); ?>">
                        <span class="atracao-blog-post-title"><?php echo esc_html($blog_post->post_title); ?></span>
                        <button type="button" class="atracao-blog-post-add" title="<?php _e('Adicionar', 'atracoes-experiencias-pda'); ?>">
                            <span class="dashicons dashicons-plus-alt2"></span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php else : ?>
                <p class="description" style="margin-top: 15px;"><?php _e('Nenhum post do blog encontrado. Crie posts do tipo "blog_post" para selecioná-los aqui.', 'atracoes-experiencias-pda'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Meta Box - Links Adicionais
     */
    public function render_meta_box_links($post) {
        $links = get_post_meta($post->ID, '_atracao_links', true);
        if (!is_array($links)) {
            $links = [];
        }
        ?>
        <div class="atracao-links-wrapper">
            <p><?php _e('Links para a seção "Saiba mais:" no final da página.', 'atracoes-experiencias-pda'); ?></p>
            <div id="atracao-links-list">
                <?php
                if (!empty($links)) {
                    foreach ($links as $index => $link) {
                        ?>
                        <div class="atracao-link-item">
                            <div class="atracao-link-texto">
                                <label><?php _e('Texto', 'atracoes-experiencias-pda'); ?></label>
                                <input type="text" name="atracao_links[<?php echo $index; ?>][texto]" value="<?php echo esc_attr($link['texto'] ?? ''); ?>" class="widefat" placeholder="Ex: Planeje sua Visita">
                            </div>
                            <div class="atracao-link-url">
                                <label><?php _e('URL', 'atracoes-experiencias-pda'); ?></label>
                                <input type="url" name="atracao_links[<?php echo $index; ?>][url]" value="<?php echo esc_url($link['url'] ?? ''); ?>" class="widefat" placeholder="https://...">
                            </div>
                            <button type="button" class="button atracao-link-remove"><?php _e('Remover', 'atracoes-experiencias-pda'); ?></button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" class="button atracao-link-add" id="atracao-link-add">
                <?php _e('Adicionar Link', 'atracoes-experiencias-pda'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Save Meta Boxes
     */
    public function save_meta_boxes($post_id) {
        // Verificar se é uma revisão
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Verificar nonce
        if (!isset($_POST['atracao_meta_box_nonce']) || !wp_verify_nonce($_POST['atracao_meta_box_nonce'], 'atracao_meta_box')) {
            return;
        }

        // Verificar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verificar permissões
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Verificar post type
        if (get_post_type($post_id) !== 'atracao_experiencia') {
            return;
        }

        // Salvar campos de texto simples
        $text_fields = [
            'atracao_card_texto' => '_atracao_card_texto',
            'atracao_blog_descricao' => '_atracao_blog_descricao',
            'atracao_blog_link_texto' => '_atracao_blog_link_texto',
            'atracao_blog_link_url' => '_atracao_blog_link_url',
        ];

        foreach ($text_fields as $field_name => $meta_key) {
            if (isset($_POST[$field_name])) {
                $value = sanitize_text_field($_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }
        
        // Salvar galeria separadamente (precisa de lógica especial)
        if (isset($_POST['atracao_galeria']) && $_POST['atracao_galeria'] !== '') {
            $galeria_value = sanitize_text_field($_POST['atracao_galeria']);
            update_post_meta($post_id, '_atracao_galeria', $galeria_value);
        }
        
        // Salvar campos de imagem (IDs de attachments)
        $image_fields = [
            'atracao_imagem_topo' => '_atracao_imagem_topo',
            'atracao_card_imagem' => '_atracao_card_imagem',
            'atracao_blog_imagem' => '_atracao_blog_imagem',
        ];

        foreach ($image_fields as $field_name => $meta_key) {
            if (isset($_POST[$field_name]) && $_POST[$field_name] !== '') {
                $value = absint($_POST[$field_name]);
                if ($value > 0) {
                    update_post_meta($post_id, $meta_key, $value);
                }
            }
            // Só deleta se o campo foi explicitamente enviado vazio
            elseif (isset($_POST[$field_name]) && $_POST[$field_name] === '') {
                delete_post_meta($post_id, $meta_key);
            }
        }

        // Salvar texto sobre (permite HTML)
        if (isset($_POST['atracao_texto_sobre'])) {
            $texto_sobre = wp_kses_post($_POST['atracao_texto_sobre']);
            update_post_meta($post_id, '_atracao_texto_sobre', $texto_sobre);
        }

        // Salvar regras selecionadas (array de checkboxes)
        if (isset($_POST['atracao_regras_selecionadas']) && is_array($_POST['atracao_regras_selecionadas'])) {
            $regras_selecionadas = array_map('sanitize_text_field', $_POST['atracao_regras_selecionadas']);
            update_post_meta($post_id, '_atracao_regras_selecionadas', $regras_selecionadas);
        } else {
            delete_post_meta($post_id, '_atracao_regras_selecionadas');
        }
        
        // Salvar posts do blog selecionados (array de checkboxes)
        if (isset($_POST['atracao_blog_posts']) && is_array($_POST['atracao_blog_posts'])) {
            $blog_posts = array_map('intval', $_POST['atracao_blog_posts']);
            update_post_meta($post_id, '_atracao_blog_posts', $blog_posts);
        } else {
            delete_post_meta($post_id, '_atracao_blog_posts');
        }
    }

    /**
     * Check if the plugin is compatible
     */
    public function is_compatible() {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return false;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return false;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        return true;
    }

    /**
     * Include required files
     */
    public function includes() {
        // GitHub Updater
        require_once ATRACOES_EXP_PDA_PLUGIN_DIR . 'includes/class-github-updater.php';
        new Atracoes_Exp_PDA_GitHub_Updater(ATRACOES_EXP_PDA_PLUGIN_FILE);
    }

    /**
     * Initialize Elementor integration
     */
    public function init_elementor() {
        // Register widget category
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_category']);

        // Register widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Register custom widget category
     */
    public function register_widget_category($elements_manager) {
        $elements_manager->add_category(
            'atracoes-pda-widgets',
            [
                'title' => __('Atrações PDA', 'atracoes-experiencias-pda'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register all widgets
     */
    public function register_widgets($widgets_manager) {
        // Include widget files
        require_once ATRACOES_EXP_PDA_PLUGIN_DIR . 'widgets/class-widget-atracoes-grid.php';

        // Register widgets
        $widgets_manager->register(new Atracoes_Exp_PDA_Widget_Grid());
    }

    /**
     * Enqueue frontend styles and scripts
     */
    public function enqueue_frontend_assets() {
        // Swiper CSS (CDN)
        wp_enqueue_style(
            'swiper-css',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11.0.0'
        );
        
        // Main CSS
        wp_enqueue_style(
            'atracoes-exp-pda-style',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/css/frontend-style.css',
            ['swiper-css'],
            ATRACOES_EXP_PDA_VERSION
        );

        // Swiper JS (CDN)
        wp_enqueue_script(
            'swiper-js',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11.0.0',
            true
        );

        // Main JS
        wp_enqueue_script(
            'atracoes-exp-pda-script',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/js/frontend-script.js',
            ['jquery', 'swiper-js'],
            ATRACOES_EXP_PDA_VERSION,
            true
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post;

        // Only on post edit screens for our CPT
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        if (empty($post) || $post->post_type !== 'atracao_experiencia') {
            return;
        }

        // WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // WordPress media
        wp_enqueue_media();

        // Admin CSS
        wp_enqueue_style(
            'atracoes-exp-pda-admin',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/css/admin-style.css',
            [],
            ATRACOES_EXP_PDA_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'atracoes-exp-pda-admin',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery', 'wp-color-picker'],
            ATRACOES_EXP_PDA_VERSION,
            true
        );

        wp_localize_script('atracoes-exp-pda-admin', 'atracoesExpPda', [
            'selectImages' => __('Selecionar Imagens', 'atracoes-experiencias-pda'),
            'selectImage' => __('Selecionar Imagem', 'atracoes-experiencias-pda'),
            'useImages' => __('Usar Imagens', 'atracoes-experiencias-pda'),
            'useImage' => __('Usar Imagem', 'atracoes-experiencias-pda'),
        ]);
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        wp_enqueue_style(
            'atracoes-exp-pda-editor',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/css/editor-style.css',
            [],
            ATRACOES_EXP_PDA_VERSION
        );
    }

    /**
     * Admin notice for missing Elementor
     */
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requer que o "%2$s" esteja instalado e ativado para os widgets funcionarem.', 'atracoes-experiencias-pda'),
            '<strong>' . esc_html__('Atrações e Experiências PDA', 'atracoes-experiencias-pda') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'atracoes-experiencias-pda') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requer o "%2$s" versão %3$s ou superior.', 'atracoes-experiencias-pda'),
            '<strong>' . esc_html__('Atrações e Experiências PDA', 'atracoes-experiencias-pda') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'atracoes-experiencias-pda') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requer o "%2$s" versão %3$s ou superior.', 'atracoes-experiencias-pda'),
            '<strong>' . esc_html__('Atrações e Experiências PDA', 'atracoes-experiencias-pda') . '</strong>',
            '<strong>' . esc_html__('PHP', 'atracoes-experiencias-pda') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Register CPT first
        $this->register_custom_post_type();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Create necessary directories
        $dirs = [
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'assets/css',
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'assets/js',
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'assets/imgs',
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'widgets',
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'includes',
            ATRACOES_EXP_PDA_PLUGIN_DIR . 'templates',
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }

        // Set activation flag
        set_transient('atracoes_exp_pda_activated', true, 30);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clean up transients
        delete_transient('atracoes_exp_pda_github_response');
    }
}

/**
 * Initialize the plugin
 */
function atracoes_experiencias_pda_init() {
    return Atracoes_Experiencias_PDA::instance();
}

// Start the plugin after plugins are loaded
add_action('plugins_loaded', 'atracoes_experiencias_pda_init');
