<?php
if (!defined('ABSPATH')) exit;

class AttenTrack_Stimulus_Templates {
    private static $instance = null;
    private $templates = array();

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->register_default_templates();
        add_action('init', array($this, 'register_stimulus_post_type'));
        add_action('admin_menu', array($this, 'add_templates_menu'));
        add_action('wp_ajax_preview_stimulus', array($this, 'ajax_preview_stimulus'));
    }

    public function register_stimulus_post_type() {
        register_post_type('stimulus_template', array(
            'labels' => array(
                'name' => __('Stimulus Templates', 'attentrack'),
                'singular_name' => __('Stimulus Template', 'attentrack'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'custom-fields'),
            'capability_type' => 'post',
        ));
    }

    public function add_templates_menu() {
        add_submenu_page(
            'edit.php?post_type=attention_test',
            __('Stimulus Templates', 'attentrack'),
            __('Stimulus Templates', 'attentrack'),
            'manage_options',
            'stimulus-templates',
            array($this, 'render_templates_page')
        );
    }

    private function register_default_templates() {
        // Text Stimuli
        $this->register_template('simple_text', array(
            'name' => __('Simple Text', 'attentrack'),
            'type' => 'text',
            'config' => array(
                'text' => '',
                'font_size' => '24px',
                'color' => '#000000',
                'background' => '#ffffff',
                'duration' => 1000,
            )
        ));

        $this->register_template('flanker_text', array(
            'name' => __('Flanker Text', 'attentrack'),
            'type' => 'text',
            'config' => array(
                'target' => '',
                'flankers' => '',
                'spacing' => '1em',
                'font_size' => '24px',
                'duration' => 2000,
            )
        ));

        // Visual Stimuli
        $this->register_template('shape', array(
            'name' => __('Basic Shape', 'attentrack'),
            'type' => 'pattern',
            'config' => array(
                'shape' => 'circle', // circle, square, triangle
                'size' => '100px',
                'color' => '#000000',
                'border' => '2px solid #000000',
                'duration' => 1000,
            )
        ));

        $this->register_template('pattern_grid', array(
            'name' => __('Pattern Grid', 'attentrack'),
            'type' => 'pattern',
            'config' => array(
                'rows' => 3,
                'columns' => 3,
                'cell_size' => '50px',
                'target_position' => array(1, 1),
                'target_color' => '#ff0000',
                'grid_color' => '#000000',
                'duration' => 2000,
            )
        ));

        // Image Stimuli
        $this->register_template('single_image', array(
            'name' => __('Single Image', 'attentrack'),
            'type' => 'image',
            'config' => array(
                'url' => '',
                'size' => '200px',
                'duration' => 1500,
            )
        ));

        // Composite Stimuli
        $this->register_template('image_text', array(
            'name' => __('Image with Text', 'attentrack'),
            'type' => 'composite',
            'config' => array(
                'image_url' => '',
                'image_size' => '150px',
                'text' => '',
                'text_position' => 'below', // above, below, left, right
                'font_size' => '18px',
                'spacing' => '10px',
                'duration' => 2000,
            )
        ));
    }

    public function register_template($id, $template) {
        $this->templates[$id] = $template;
    }

    public function get_template($id) {
        return isset($this->templates[$id]) ? $this->templates[$id] : null;
    }

    public function get_all_templates() {
        return $this->templates;
    }

    public function render_stimulus($template_id, $config) {
        $template = $this->get_template($template_id);
        if (!$template) {
            return '';
        }

        $config = wp_parse_args($config, $template['config']);
        
        ob_start();
        switch ($template['type']) {
            case 'text':
                $this->render_text_stimulus($template_id, $config);
                break;
            case 'pattern':
                $this->render_pattern_stimulus($template_id, $config);
                break;
            case 'image':
                $this->render_image_stimulus($template_id, $config);
                break;
            case 'composite':
                $this->render_composite_stimulus($template_id, $config);
                break;
        }
        return ob_get_clean();
    }

    private function render_text_stimulus($template_id, $config) {
        switch ($template_id) {
            case 'simple_text':
                ?>
                <div class="stimulus-text" style="
                    font-size: <?php echo esc_attr($config['font_size']); ?>;
                    color: <?php echo esc_attr($config['color']); ?>;
                    background: <?php echo esc_attr($config['background']); ?>;
                    padding: 20px;
                    text-align: center;
                ">
                    <?php echo esc_html($config['text']); ?>
                </div>
                <?php
                break;

            case 'flanker_text':
                ?>
                <div class="stimulus-flanker" style="
                    font-size: <?php echo esc_attr($config['font_size']); ?>;
                    text-align: center;
                ">
                    <span class="flanker" style="margin-right: <?php echo esc_attr($config['spacing']); ?>">
                        <?php echo esc_html($config['flankers']); ?>
                    </span>
                    <span class="target">
                        <?php echo esc_html($config['target']); ?>
                    </span>
                    <span class="flanker" style="margin-left: <?php echo esc_attr($config['spacing']); ?>">
                        <?php echo esc_html($config['flankers']); ?>
                    </span>
                </div>
                <?php
                break;
        }
    }

    private function render_pattern_stimulus($template_id, $config) {
        switch ($template_id) {
            case 'shape':
                ?>
                <div class="stimulus-shape <?php echo esc_attr($config['shape']); ?>" style="
                    width: <?php echo esc_attr($config['size']); ?>;
                    height: <?php echo esc_attr($config['size']); ?>;
                    background-color: <?php echo esc_attr($config['color']); ?>;
                    border: <?php echo esc_attr($config['border']); ?>;
                "></div>
                <?php
                break;

            case 'pattern_grid':
                ?>
                <div class="stimulus-grid" style="
                    display: grid;
                    grid-template-columns: repeat(<?php echo esc_attr($config['columns']); ?>, <?php echo esc_attr($config['cell_size']); ?>);
                    gap: 2px;
                    background-color: <?php echo esc_attr($config['grid_color']); ?>;
                    padding: 2px;
                ">
                    <?php
                    for ($i = 0; $i < $config['rows']; $i++) {
                        for ($j = 0; $j < $config['columns']; $j++) {
                            $isTarget = ($i === $config['target_position'][0] && $j === $config['target_position'][1]);
                            ?>
                            <div class="grid-cell" style="
                                width: <?php echo esc_attr($config['cell_size']); ?>;
                                height: <?php echo esc_attr($config['cell_size']); ?>;
                                background-color: <?php echo $isTarget ? esc_attr($config['target_color']) : '#ffffff'; ?>;
                            "></div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
                break;
        }
    }

    private function render_image_stimulus($template_id, $config) {
        switch ($template_id) {
            case 'single_image':
                ?>
                <div class="stimulus-image" style="
                    width: <?php echo esc_attr($config['size']); ?>;
                    height: <?php echo esc_attr($config['size']); ?>;
                    background-image: url('<?php echo esc_url($config['url']); ?>');
                    background-size: contain;
                    background-position: center;
                    background-repeat: no-repeat;
                "></div>
                <?php
                break;
        }
    }

    private function render_composite_stimulus($template_id, $config) {
        switch ($template_id) {
            case 'image_text':
                $flexDirection = in_array($config['text_position'], array('above', 'below')) ? 'column' : 'row';
                $flexDirection = ($config['text_position'] === 'above' || $config['text_position'] === 'left') ? $flexDirection : $flexDirection . '-reverse';
                ?>
                <div class="stimulus-composite" style="
                    display: flex;
                    flex-direction: <?php echo esc_attr($flexDirection); ?>;
                    align-items: center;
                    gap: <?php echo esc_attr($config['spacing']); ?>;
                ">
                    <div class="composite-image" style="
                        width: <?php echo esc_attr($config['image_size']); ?>;
                        height: <?php echo esc_attr($config['image_size']); ?>;
                        background-image: url('<?php echo esc_url($config['image_url']); ?>');
                        background-size: contain;
                        background-position: center;
                        background-repeat: no-repeat;
                    "></div>
                    <div class="composite-text" style="
                        font-size: <?php echo esc_attr($config['font_size']); ?>;
                    ">
                        <?php echo esc_html($config['text']); ?>
                    </div>
                </div>
                <?php
                break;
        }
    }

    public function ajax_preview_stimulus() {
        check_ajax_referer('attentrack_preview_stimulus', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $template_id = sanitize_text_field($_POST['template_id']);
        $config = isset($_POST['config']) ? $_POST['config'] : array();
        
        // Sanitize config
        array_walk_recursive($config, 'sanitize_text_field');
        
        $html = $this->render_stimulus($template_id, $config);
        wp_send_json_success(array('html' => $html));
    }

    public function render_templates_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Stimulus Templates', 'attentrack'); ?></h1>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <a href="#" class="button button-primary" id="create-template">
                        <?php _e('Create New Template', 'attentrack'); ?>
                    </a>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Template Name', 'attentrack'); ?></th>
                        <th><?php _e('Type', 'attentrack'); ?></th>
                        <th><?php _e('Preview', 'attentrack'); ?></th>
                        <th><?php _e('Actions', 'attentrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->templates as $id => $template): ?>
                    <tr>
                        <td><?php echo esc_html($template['name']); ?></td>
                        <td><?php echo esc_html($template['type']); ?></td>
                        <td>
                            <div class="template-preview" data-template-id="<?php echo esc_attr($id); ?>">
                                <?php echo $this->render_stimulus($id, $template['config']); ?>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="edit-template" data-template-id="<?php echo esc_attr($id); ?>">
                                <?php _e('Edit', 'attentrack'); ?>
                            </a>
                            |
                            <a href="#" class="duplicate-template" data-template-id="<?php echo esc_attr($id); ?>">
                                <?php _e('Duplicate', 'attentrack'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Initialize the templates class
AttenTrack_Stimulus_Templates::get_instance();
