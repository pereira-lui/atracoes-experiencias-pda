<?php
/**
 * Plugin Name: Atrações e Experiências PDA
 * Plugin URI: https://github.com/pereira-lui/atracoes-experiencias-pda
 * Description: Plugin para gerenciar Custom Post Type "Atrações e Experiências" com campos personalizados e widget para Elementor.
 * Version: 1.0.0
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
define('ATRACOES_EXP_PDA_VERSION', '1.0.0');
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
        add_action('save_post', [$this, 'save_meta_boxes']);

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
            'supports'              => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'taxonomies'            => ['atracao_categoria'],
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
            'show_in_rest'          => true,
            'rewrite'               => ['slug' => 'atracoes-experiencias'],
        ];

        register_post_type('atracao_experiencia', $args);

        // Registrar Taxonomia - Categoria de Atrações
        $cat_labels = [
            'name'                       => _x('Categorias de Atrações', 'Taxonomy General Name', 'atracoes-experiencias-pda'),
            'singular_name'              => _x('Categoria de Atração', 'Taxonomy Singular Name', 'atracoes-experiencias-pda'),
            'menu_name'                  => __('Categorias', 'atracoes-experiencias-pda'),
            'all_items'                  => __('Todas as Categorias', 'atracoes-experiencias-pda'),
            'parent_item'                => __('Categoria Pai', 'atracoes-experiencias-pda'),
            'parent_item_colon'          => __('Categoria Pai:', 'atracoes-experiencias-pda'),
            'new_item_name'              => __('Nova Categoria', 'atracoes-experiencias-pda'),
            'add_new_item'               => __('Adicionar Nova Categoria', 'atracoes-experiencias-pda'),
            'edit_item'                  => __('Editar Categoria', 'atracoes-experiencias-pda'),
            'update_item'                => __('Atualizar Categoria', 'atracoes-experiencias-pda'),
            'view_item'                  => __('Ver Categoria', 'atracoes-experiencias-pda'),
            'separate_items_with_commas' => __('Separar categorias com vírgulas', 'atracoes-experiencias-pda'),
            'add_or_remove_items'        => __('Adicionar ou remover categorias', 'atracoes-experiencias-pda'),
            'choose_from_most_used'      => __('Escolher das mais usadas', 'atracoes-experiencias-pda'),
            'popular_items'              => __('Categorias populares', 'atracoes-experiencias-pda'),
            'search_items'               => __('Buscar Categorias', 'atracoes-experiencias-pda'),
            'not_found'                  => __('Não encontrada', 'atracoes-experiencias-pda'),
            'no_terms'                   => __('Sem categorias', 'atracoes-experiencias-pda'),
            'items_list'                 => __('Lista de categorias', 'atracoes-experiencias-pda'),
            'items_list_navigation'      => __('Navegação da lista de categorias', 'atracoes-experiencias-pda'),
        ];

        $cat_args = [
            'labels'                     => $cat_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => ['slug' => 'categoria-atracao'],
        ];

        register_taxonomy('atracao_categoria', ['atracao_experiencia'], $cat_args);
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        // Meta Box - Informações Principais
        add_meta_box(
            'atracao_info_principal',
            __('Informações Principais', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_info_principal'],
            'atracao_experiencia',
            'normal',
            'high'
        );

        // Meta Box - Subtítulo e Descrição Curta
        add_meta_box(
            'atracao_subtitulo',
            __('Subtítulo e Descrição Curta', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_subtitulo'],
            'atracao_experiencia',
            'normal',
            'high'
        );

        // Meta Box - Galeria de Imagens
        add_meta_box(
            'atracao_galeria',
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

        // Meta Box - Regras e Dicas
        add_meta_box(
            'atracao_regras',
            __('Regras e Dicas para Visitantes', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_regras'],
            'atracao_experiencia',
            'normal',
            'default'
        );

        // Meta Box - Seção de Blog Relacionado
        add_meta_box(
            'atracao_blog_relacionado',
            __('Blog Relacionado', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_blog_relacionado'],
            'atracao_experiencia',
            'normal',
            'default'
        );

        // Meta Box - Links Adicionais
        add_meta_box(
            'atracao_links',
            __('Links Adicionais (Saiba Mais)', 'atracoes-experiencias-pda'),
            [$this, 'render_meta_box_links'],
            'atracao_experiencia',
            'normal',
            'default'
        );
    }

    /**
     * Render Meta Box - Informações Principais
     */
    public function render_meta_box_info_principal($post) {
        wp_nonce_field('atracao_meta_box', 'atracao_meta_box_nonce');

        $localizacao = get_post_meta($post->ID, '_atracao_localizacao', true);
        $horario_funcionamento = get_post_meta($post->ID, '_atracao_horario_funcionamento', true);
        $duracao_visita = get_post_meta($post->ID, '_atracao_duracao_visita', true);
        $nivel_dificuldade = get_post_meta($post->ID, '_atracao_nivel_dificuldade', true);
        ?>
        <table class="form-table atracao-meta-table">
            <tr>
                <th><label for="atracao_localizacao"><?php _e('Localização', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_localizacao" name="atracao_localizacao" value="<?php echo esc_attr($localizacao); ?>" class="widefat" placeholder="Ex: Área Central do Parque">
                    <p class="description"><?php _e('Localização da atração dentro do parque.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="atracao_horario_funcionamento"><?php _e('Horário de Funcionamento', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_horario_funcionamento" name="atracao_horario_funcionamento" value="<?php echo esc_attr($horario_funcionamento); ?>" class="widefat" placeholder="Ex: 8h às 17h">
                    <p class="description"><?php _e('Horário de funcionamento da atração.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="atracao_duracao_visita"><?php _e('Duração da Visita', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_duracao_visita" name="atracao_duracao_visita" value="<?php echo esc_attr($duracao_visita); ?>" class="widefat" placeholder="Ex: 30 minutos">
                    <p class="description"><?php _e('Tempo médio de visita.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="atracao_nivel_dificuldade"><?php _e('Nível de Dificuldade', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <select id="atracao_nivel_dificuldade" name="atracao_nivel_dificuldade" class="widefat">
                        <option value=""><?php _e('Selecione...', 'atracoes-experiencias-pda'); ?></option>
                        <option value="facil" <?php selected($nivel_dificuldade, 'facil'); ?>><?php _e('Fácil', 'atracoes-experiencias-pda'); ?></option>
                        <option value="moderado" <?php selected($nivel_dificuldade, 'moderado'); ?>><?php _e('Moderado', 'atracoes-experiencias-pda'); ?></option>
                        <option value="dificil" <?php selected($nivel_dificuldade, 'dificil'); ?>><?php _e('Difícil', 'atracoes-experiencias-pda'); ?></option>
                    </select>
                    <p class="description"><?php _e('Nível de dificuldade da atração/experiência.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Meta Box - Subtítulo e Descrição Curta
     */
    public function render_meta_box_subtitulo($post) {
        $subtitulo = get_post_meta($post->ID, '_atracao_subtitulo', true);
        $descricao_curta = get_post_meta($post->ID, '_atracao_descricao_curta', true);
        ?>
        <table class="form-table atracao-meta-table">
            <tr>
                <th><label for="atracao_subtitulo"><?php _e('Subtítulo', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_subtitulo" name="atracao_subtitulo" value="<?php echo esc_attr($subtitulo); ?>" class="widefat" placeholder="Ex: O visitante pode ficar muito próximo de mutuns, perdizes, jacutingas e muitas outras aves incríveis.">
                    <p class="description"><?php _e('Texto curto que aparece abaixo do título principal.', 'atracoes-experiencias-pda'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="atracao_descricao_curta"><?php _e('Descrição Curta', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <?php
                    wp_editor($descricao_curta, 'atracao_descricao_curta', [
                        'textarea_name' => 'atracao_descricao_curta',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => true,
                    ]);
                    ?>
                    <p class="description"><?php _e('Descrição que aparece na página da atração, abaixo do subtítulo.', 'atracoes-experiencias-pda'); ?></p>
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
        $card_cor_fundo = get_post_meta($post->ID, '_atracao_card_cor_fundo', true);
        $card_cor_texto = get_post_meta($post->ID, '_atracao_card_cor_texto', true);
        
        // Valores padrão
        if (empty($card_cor_fundo)) $card_cor_fundo = '#8B5CF6';
        if (empty($card_cor_texto)) $card_cor_texto = '#FFFFFF';
        ?>
        <p>
            <label for="atracao_card_cor_fundo"><?php _e('Cor de Fundo do Card', 'atracoes-experiencias-pda'); ?></label><br>
            <input type="text" id="atracao_card_cor_fundo" name="atracao_card_cor_fundo" value="<?php echo esc_attr($card_cor_fundo); ?>" class="atracao-color-picker" data-default-color="#8B5CF6">
        </p>
        <p>
            <label for="atracao_card_cor_texto"><?php _e('Cor do Texto do Card', 'atracoes-experiencias-pda'); ?></label><br>
            <input type="text" id="atracao_card_cor_texto" name="atracao_card_cor_texto" value="<?php echo esc_attr($card_cor_texto); ?>" class="atracao-color-picker" data-default-color="#FFFFFF">
        </p>
        <p class="description"><?php _e('Cores usadas na listagem de cards.', 'atracoes-experiencias-pda'); ?></p>
        <?php
    }

    /**
     * Render Meta Box - Regras e Dicas
     */
    public function render_meta_box_regras($post) {
        $regras = get_post_meta($post->ID, '_atracao_regras', true);
        if (!is_array($regras)) {
            $regras = [];
        }
        ?>
        <div class="atracao-regras-wrapper">
            <div id="atracao-regras-list">
                <?php
                if (!empty($regras)) {
                    foreach ($regras as $index => $regra) {
                        ?>
                        <div class="atracao-regra-item">
                            <div class="atracao-regra-icon">
                                <label><?php _e('Ícone (Dashicon)', 'atracoes-experiencias-pda'); ?></label>
                                <input type="text" name="atracao_regras[<?php echo $index; ?>][icone]" value="<?php echo esc_attr($regra['icone'] ?? ''); ?>" placeholder="dashicons-warning">
                            </div>
                            <div class="atracao-regra-texto">
                                <label><?php _e('Texto', 'atracoes-experiencias-pda'); ?></label>
                                <input type="text" name="atracao_regras[<?php echo $index; ?>][texto]" value="<?php echo esc_attr($regra['texto'] ?? ''); ?>" class="widefat" placeholder="Ex: Não alimentar os animais">
                            </div>
                            <button type="button" class="button atracao-regra-remove"><?php _e('Remover', 'atracoes-experiencias-pda'); ?></button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" class="button atracao-regra-add" id="atracao-regra-add">
                <?php _e('Adicionar Regra/Dica', 'atracoes-experiencias-pda'); ?>
            </button>
            <p class="description"><?php _e('Adicione regras e dicas para os visitantes. Use classes de ícones Dashicons (ex: dashicons-warning, dashicons-heart, etc.)', 'atracoes-experiencias-pda'); ?></p>
        </div>
        <?php
    }

    /**
     * Render Meta Box - Blog Relacionado
     */
    public function render_meta_box_blog_relacionado($post) {
        $blog_titulo = get_post_meta($post->ID, '_atracao_blog_titulo', true);
        $blog_descricao = get_post_meta($post->ID, '_atracao_blog_descricao', true);
        $blog_link_texto = get_post_meta($post->ID, '_atracao_blog_link_texto', true);
        $blog_link_url = get_post_meta($post->ID, '_atracao_blog_link_url', true);
        $blog_imagem = get_post_meta($post->ID, '_atracao_blog_imagem', true);
        ?>
        <table class="form-table atracao-meta-table">
            <tr>
                <th><label for="atracao_blog_titulo"><?php _e('Título da Seção', 'atracoes-experiencias-pda'); ?></label></th>
                <td>
                    <input type="text" id="atracao_blog_titulo" name="atracao_blog_titulo" value="<?php echo esc_attr($blog_titulo); ?>" class="widefat" placeholder="Ex: Matérias do Blog">
                </td>
            </tr>
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
            'atracao_localizacao' => '_atracao_localizacao',
            'atracao_horario_funcionamento' => '_atracao_horario_funcionamento',
            'atracao_duracao_visita' => '_atracao_duracao_visita',
            'atracao_nivel_dificuldade' => '_atracao_nivel_dificuldade',
            'atracao_subtitulo' => '_atracao_subtitulo',
            'atracao_galeria' => '_atracao_galeria',
            'atracao_card_cor_fundo' => '_atracao_card_cor_fundo',
            'atracao_card_cor_texto' => '_atracao_card_cor_texto',
            'atracao_blog_titulo' => '_atracao_blog_titulo',
            'atracao_blog_descricao' => '_atracao_blog_descricao',
            'atracao_blog_link_texto' => '_atracao_blog_link_texto',
            'atracao_blog_link_url' => '_atracao_blog_link_url',
            'atracao_blog_imagem' => '_atracao_blog_imagem',
        ];

        foreach ($text_fields as $field_name => $meta_key) {
            if (isset($_POST[$field_name])) {
                $value = sanitize_text_field($_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }

        // Salvar descrição curta (permite HTML)
        if (isset($_POST['atracao_descricao_curta'])) {
            $descricao_curta = wp_kses_post($_POST['atracao_descricao_curta']);
            update_post_meta($post_id, '_atracao_descricao_curta', $descricao_curta);
        }

        // Salvar regras (array)
        if (isset($_POST['atracao_regras']) && is_array($_POST['atracao_regras'])) {
            $regras = [];
            foreach ($_POST['atracao_regras'] as $regra) {
                if (!empty($regra['texto'])) {
                    $regras[] = [
                        'icone' => sanitize_text_field($regra['icone'] ?? ''),
                        'texto' => sanitize_text_field($regra['texto']),
                    ];
                }
            }
            update_post_meta($post_id, '_atracao_regras', $regras);
        } else {
            delete_post_meta($post_id, '_atracao_regras');
        }

        // Salvar links (array)
        if (isset($_POST['atracao_links']) && is_array($_POST['atracao_links'])) {
            $links = [];
            foreach ($_POST['atracao_links'] as $link) {
                if (!empty($link['texto']) && !empty($link['url'])) {
                    $links[] = [
                        'texto' => sanitize_text_field($link['texto']),
                        'url' => esc_url_raw($link['url']),
                    ];
                }
            }
            update_post_meta($post_id, '_atracao_links', $links);
        } else {
            delete_post_meta($post_id, '_atracao_links');
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
        // Main CSS
        wp_enqueue_style(
            'atracoes-exp-pda-style',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/css/frontend-style.css',
            [],
            ATRACOES_EXP_PDA_VERSION
        );

        // Main JS
        wp_enqueue_script(
            'atracoes-exp-pda-script',
            ATRACOES_EXP_PDA_PLUGIN_URL . 'assets/js/frontend-script.js',
            ['jquery'],
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
