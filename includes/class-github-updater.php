<?php
/**
 * GitHub Plugin Updater
 * 
 * Permite atualização automática do plugin via GitHub
 *
 * @package Atracoes_Experiencias_PDA
 */

if (!defined('ABSPATH')) {
    exit;
}

class Atracoes_Exp_PDA_GitHub_Updater {

    private $slug;
    private $plugin_data;
    private $username;
    private $repo;
    private $plugin_file;
    private $github_response;
    private $plugin_activated;
    private $plugin_folder;

    /**
     * Constructor
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->username = 'pereira-lui';
        $this->repo = 'atracoes-experiencias-pda';
        $this->plugin_folder = 'atracoes-experiencias-pda';
        $this->slug = $this->plugin_folder . '/' . basename($plugin_file);

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_source_selection', [$this, 'fix_source_folder'], 10, 4);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
        
        // Add settings link
        add_filter('plugin_action_links_' . $this->slug, [$this, 'plugin_settings_link']);
        
        // Add version info row
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);
    }

    /**
     * Get plugin data
     */
    private function get_plugin_data() {
        if (!empty($this->plugin_data)) {
            return;
        }
        
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->plugin_data = get_plugin_data($this->plugin_file);
    }

    /**
     * Get repository info from GitHub
     */
    private function get_repository_info() {
        if (!empty($this->github_response)) {
            return;
        }

        // Check cache first
        $cache_key = 'atracoes_exp_pda_github_response';
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            $this->github_response = $cached;
            return;
        }

        // First try to get the latest release
        $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo);
        
        $response = wp_remote_get($request_uri, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            ],
            'timeout' => 10,
        ]);

        $use_tags = false;
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $use_tags = true;
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $release_data = json_decode($response_body);
            
            // Check if we need to use tags instead (if tag is newer than release)
            $tags_response = $this->get_latest_tag();
            if ($tags_response && !empty($tags_response->name)) {
                $release_version = isset($release_data->tag_name) ? ltrim($release_data->tag_name, 'v') : '0.0.0';
                $tag_version = ltrim($tags_response->name, 'v');
                
                if (version_compare($tag_version, $release_version, '>')) {
                    $use_tags = true;
                } else {
                    $this->github_response = $release_data;
                }
            } else {
                $this->github_response = $release_data;
            }
        }
        
        // If release not found or tag is newer, get latest tag
        if ($use_tags) {
            $tag_data = $this->get_latest_tag();
            if ($tag_data) {
                $this->github_response = (object) [
                    'tag_name' => $tag_data->name,
                    'zipball_url' => sprintf('https://api.github.com/repos/%s/%s/zipball/%s', $this->username, $this->repo, $tag_data->name),
                    'published_at' => $tag_data->commit->committer->date ?? date('c'),
                    'body' => '',
                    'assets' => [],
                ];
            }
        }
        
        if (!empty($this->github_response)) {
            // Cache for 1 hour
            set_transient($cache_key, $this->github_response, 1 * HOUR_IN_SECONDS);
        }
    }
    
    /**
     * Get latest tag from GitHub
     */
    private function get_latest_tag() {
        $request_uri = sprintf('https://api.github.com/repos/%s/%s/tags', $this->username, $this->repo);
        
        $response = wp_remote_get($request_uri, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $tags = json_decode(wp_remote_retrieve_body($response));
        
        if (empty($tags) || !is_array($tags)) {
            return null;
        }

        // Sort tags by version
        usort($tags, function($a, $b) {
            return version_compare(ltrim($b->name, 'v'), ltrim($a->name, 'v'));
        });

        // Get commit info for the latest tag
        $tag = $tags[0];
        $commit_uri = sprintf('https://api.github.com/repos/%s/%s/commits/%s', $this->username, $this->repo, $tag->commit->sha);
        
        $commit_response = wp_remote_get($commit_uri, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            ],
            'timeout' => 10,
        ]);

        if (!is_wp_error($commit_response) && wp_remote_retrieve_response_code($commit_response) === 200) {
            $tag->commit = json_decode(wp_remote_retrieve_body($commit_response));
        }

        return $tag;
    }

    /**
     * Check for plugin updates
     */
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->get_plugin_data();
        $this->get_repository_info();

        if (empty($this->github_response) || !isset($this->github_response->tag_name)) {
            return $transient;
        }

        $github_version = ltrim($this->github_response->tag_name, 'v');
        $current_version = $this->plugin_data['Version'];

        if (version_compare($github_version, $current_version, '>')) {
            $package = $this->github_response->zipball_url;

            // Check if there's a specific zip asset
            if (!empty($this->github_response->assets)) {
                foreach ($this->github_response->assets as $asset) {
                    if (strpos($asset->name, '.zip') !== false) {
                        $package = $asset->browser_download_url;
                        break;
                    }
                }
            }

            $transient->response[$this->slug] = (object) [
                'slug' => $this->plugin_folder,
                'plugin' => $this->slug,
                'new_version' => $github_version,
                'url' => $this->plugin_data['PluginURI'],
                'package' => $package,
                'icons' => [],
                'banners' => [],
                'banners_rtl' => [],
                'tested' => '',
                'requires_php' => $this->plugin_data['RequiresPHP'] ?? '7.4',
                'compatibility' => new stdClass(),
            ];
        }

        return $transient;
    }

    /**
     * Plugin information for the update details popup
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($this->plugin_folder !== $args->slug) {
            return $result;
        }

        $this->get_plugin_data();
        $this->get_repository_info();

        if (empty($this->github_response)) {
            return $result;
        }

        $plugin_info = (object) [
            'name' => $this->plugin_data['Name'],
            'slug' => $this->plugin_folder,
            'version' => ltrim($this->github_response->tag_name ?? '1.0.0', 'v'),
            'author' => $this->plugin_data['AuthorName'],
            'author_profile' => $this->plugin_data['AuthorURI'],
            'requires' => $this->plugin_data['RequiresWP'] ?? '5.0',
            'tested' => '',
            'requires_php' => $this->plugin_data['RequiresPHP'] ?? '7.4',
            'sections' => [
                'description' => $this->plugin_data['Description'],
                'changelog' => $this->github_response->body ?? __('Veja o repositório no GitHub para o changelog completo.', 'atracoes-experiencias-pda'),
            ],
            'download_link' => $this->github_response->zipball_url ?? '',
            'last_updated' => $this->github_response->published_at ?? '',
            'homepage' => $this->plugin_data['PluginURI'],
        ];

        return $plugin_info;
    }

    /**
     * Fix the source folder name after download
     */
    public function fix_source_folder($source, $remote_source, $upgrader, $hook_extra) {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->slug) {
            return $source;
        }

        global $wp_filesystem;

        $corrected_source = trailingslashit($remote_source) . $this->plugin_folder . '/';

        if ($source === $corrected_source) {
            return $source;
        }

        if ($wp_filesystem->move($source, $corrected_source)) {
            return $corrected_source;
        }

        return new WP_Error(
            'rename_failed',
            __('Não foi possível renomear a pasta do plugin.', 'atracoes-experiencias-pda')
        );
    }

    /**
     * After plugin install
     */
    public function after_install($response, $hook_extra, $result) {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->slug) {
            return $result;
        }

        // Re-activate plugin if it was active
        if (is_plugin_active($this->slug)) {
            activate_plugin($this->slug);
        }

        return $result;
    }

    /**
     * Add settings link to plugins page
     */
    public function plugin_settings_link($links) {
        $github_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://github.com/' . $this->username . '/' . $this->repo,
            __('GitHub', 'atracoes-experiencias-pda')
        );
        
        array_unshift($links, $github_link);
        
        return $links;
    }

    /**
     * Add version info to plugin row
     */
    public function plugin_row_meta($links, $file) {
        if ($this->slug !== $file) {
            return $links;
        }

        $this->get_repository_info();

        if (!empty($this->github_response) && isset($this->github_response->tag_name)) {
            $links[] = sprintf(
                '<span style="color: #666;">%s: %s</span>',
                __('Última versão no GitHub', 'atracoes-experiencias-pda'),
                ltrim($this->github_response->tag_name, 'v')
            );
        }

        return $links;
    }
}
