<?php
if (!defined('ABSPATH')) exit;

class AttenTrack_Test_Admin {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_test_meta_boxes'));
        add_action('save_post_attention_test', array($this, 'save_test_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_filter('manage_attention_test_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_attention_test_posts_custom_column', array($this, 'render_custom_columns'), 10, 2);
    }

    public function enqueue_admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        global $post;
        if ('attention_test' !== $post->post_type) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script(
            'attentrack-admin',
            plugins_url('js/admin.js', dirname(__FILE__)),
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            '1.0',
            true
        );

        wp_localize_script('attentrack-admin', 'attentrackAdmin', array(
            'nonce' => wp_create_nonce('attentrack_admin_nonce'),
            'i18n' => array(
                'addPhase' => __('Add Phase', 'attentrack'),
                'removePhase' => __('Remove Phase', 'attentrack'),
                'addStimulus' => __('Add Stimulus', 'attentrack'),
                'removeStimulus' => __('Remove Stimulus', 'attentrack'),
            )
        ));
    }

    public function add_test_meta_boxes() {
        add_meta_box(
            'attentrack_test_config',
            __('Test Configuration', 'attentrack'),
            array($this, 'render_test_config_meta_box'),
            'attention_test',
            'normal',
            'high'
        );

        add_meta_box(
            'attentrack_test_phases',
            __('Test Phases', 'attentrack'),
            array($this, 'render_test_phases_meta_box'),
            'attention_test',
            'normal',
            'high'
        );

        add_meta_box(
            'attentrack_test_settings',
            __('Test Settings', 'attentrack'),
            array($this, 'render_test_settings_meta_box'),
            'attention_test',
            'side',
            'default'
        );
    }

    public function render_test_config_meta_box($post) {
        wp_nonce_field('attentrack_test_meta', 'attentrack_test_nonce');
        
        $test_type = get_post_meta($post->ID, 'test_type', true);
        $test_duration = get_post_meta($post->ID, 'test_duration', true);
        $min_age = get_post_meta($post->ID, 'min_age', true);
        $max_age = get_post_meta($post->ID, 'max_age', true);
        ?>
        <div class="attentrack-meta-box">
            <p>
                <label for="test_type"><?php _e('Test Type:', 'attentrack'); ?></label>
                <select name="test_type" id="test_type" class="widefat">
                    <option value="sustained" <?php selected($test_type, 'sustained'); ?>><?php _e('Sustained Attention', 'attentrack'); ?></option>
                    <option value="selective" <?php selected($test_type, 'selective'); ?>><?php _e('Selective Attention', 'attentrack'); ?></option>
                    <option value="divided" <?php selected($test_type, 'divided'); ?>><?php _e('Divided Attention', 'attentrack'); ?></option>
                    <option value="switching" <?php selected($test_type, 'switching'); ?>><?php _e('Attention Switching', 'attentrack'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="test_duration"><?php _e('Test Duration (minutes):', 'attentrack'); ?></label>
                <input type="number" name="test_duration" id="test_duration" value="<?php echo esc_attr($test_duration); ?>" class="widefat" min="1" max="60">
            </p>
            
            <div class="age-range">
                <p>
                    <label for="min_age"><?php _e('Minimum Age:', 'attentrack'); ?></label>
                    <input type="number" name="min_age" id="min_age" value="<?php echo esc_attr($min_age); ?>" class="small-text" min="0" max="120">
                </p>
                
                <p>
                    <label for="max_age"><?php _e('Maximum Age:', 'attentrack'); ?></label>
                    <input type="number" name="max_age" id="max_age" value="<?php echo esc_attr($max_age); ?>" class="small-text" min="0" max="120">
                </p>
            </div>
        </div>
        <?php
    }

    public function render_test_phases_meta_box($post) {
        $phases = get_post_meta($post->ID, 'test_phases', true);
        if (!is_array($phases)) {
            $phases = array();
        }
        ?>
        <div id="test-phases-container">
            <?php foreach ($phases as $index => $phase): ?>
            <div class="phase-block" data-index="<?php echo $index; ?>">
                <h3 class="phase-header">
                    <?php _e('Phase', 'attentrack'); ?> <?php echo $index + 1; ?>
                    <button type="button" class="remove-phase button-link"><?php _e('Remove', 'attentrack'); ?></button>
                </h3>
                
                <div class="phase-content">
                    <p>
                        <label><?php _e('Phase Title:', 'attentrack'); ?></label>
                        <input type="text" name="phases[<?php echo $index; ?>][title]" 
                               value="<?php echo esc_attr($phase['title']); ?>" class="widefat">
                    </p>
                    
                    <p>
                        <label><?php _e('Duration (seconds):', 'attentrack'); ?></label>
                        <input type="number" name="phases[<?php echo $index; ?>][duration]" 
                               value="<?php echo esc_attr($phase['duration']); ?>" min="1" max="3600">
                    </p>
                    
                    <div class="stimuli-container">
                        <h4><?php _e('Stimuli', 'attentrack'); ?></h4>
                        <?php foreach ($phase['stimuli'] as $stim_index => $stimulus): ?>
                        <div class="stimulus-block">
                            <select name="phases[<?php echo $index; ?>][stimuli][<?php echo $stim_index; ?>][type]">
                                <option value="text" <?php selected($stimulus['type'], 'text'); ?>><?php _e('Text', 'attentrack'); ?></option>
                                <option value="image" <?php selected($stimulus['type'], 'image'); ?>><?php _e('Image', 'attentrack'); ?></option>
                                <option value="pattern" <?php selected($stimulus['type'], 'pattern'); ?>><?php _e('Pattern', 'attentrack'); ?></option>
                            </select>
                            
                            <input type="text" name="phases[<?php echo $index; ?>][stimuli][<?php echo $stim_index; ?>][content]" 
                                   value="<?php echo esc_attr($stimulus['content']); ?>" class="widefat stimulus-content">
                            
                            <button type="button" class="remove-stimulus button-link"><?php _e('Remove', 'attentrack'); ?></button>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="button" class="add-stimulus button"><?php _e('Add Stimulus', 'attentrack'); ?></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <button type="button" id="add-phase" class="button button-primary"><?php _e('Add Phase', 'attentrack'); ?></button>
        </div>
        <?php
    }

    public function render_test_settings_meta_box($post) {
        $settings = get_post_meta($post->ID, 'test_settings', true);
        if (!is_array($settings)) {
            $settings = array(
                'randomize_stimuli' => true,
                'show_progress' => true,
                'allow_pause' => false,
                'feedback_frequency' => 'end'
            );
        }
        ?>
        <div class="test-settings">
            <p>
                <label>
                    <input type="checkbox" name="test_settings[randomize_stimuli]" 
                           <?php checked($settings['randomize_stimuli']); ?>>
                    <?php _e('Randomize Stimuli', 'attentrack'); ?>
                </label>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="test_settings[show_progress]" 
                           <?php checked($settings['show_progress']); ?>>
                    <?php _e('Show Progress Bar', 'attentrack'); ?>
                </label>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="test_settings[allow_pause]" 
                           <?php checked($settings['allow_pause']); ?>>
                    <?php _e('Allow Pause', 'attentrack'); ?>
                </label>
            </p>
            
            <p>
                <label><?php _e('Feedback Frequency:', 'attentrack'); ?></label>
                <select name="test_settings[feedback_frequency]" class="widefat">
                    <option value="none" <?php selected($settings['feedback_frequency'], 'none'); ?>><?php _e('No Feedback', 'attentrack'); ?></option>
                    <option value="immediate" <?php selected($settings['feedback_frequency'], 'immediate'); ?>><?php _e('Immediate', 'attentrack'); ?></option>
                    <option value="phase" <?php selected($settings['feedback_frequency'], 'phase'); ?>><?php _e('After Each Phase', 'attentrack'); ?></option>
                    <option value="end" <?php selected($settings['feedback_frequency'], 'end'); ?>><?php _e('End of Test', 'attentrack'); ?></option>
                </select>
            </p>
        </div>
        <?php
    }

    public function save_test_meta($post_id) {
        if (!isset($_POST['attentrack_test_nonce']) || 
            !wp_verify_nonce($_POST['attentrack_test_nonce'], 'attentrack_test_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save basic test configuration
        $fields = array('test_type', 'test_duration', 'min_age', 'max_age');
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save test phases
        if (isset($_POST['phases'])) {
            $phases = array();
            foreach ($_POST['phases'] as $phase) {
                $phases[] = array(
                    'title' => sanitize_text_field($phase['title']),
                    'duration' => intval($phase['duration']),
                    'stimuli' => array_map(function($stimulus) {
                        return array(
                            'type' => sanitize_text_field($stimulus['type']),
                            'content' => sanitize_text_field($stimulus['content'])
                        );
                    }, $phase['stimuli'])
                );
            }
            update_post_meta($post_id, 'test_phases', $phases);
        }

        // Save test settings
        if (isset($_POST['test_settings'])) {
            $settings = array(
                'randomize_stimuli' => isset($_POST['test_settings']['randomize_stimuli']),
                'show_progress' => isset($_POST['test_settings']['show_progress']),
                'allow_pause' => isset($_POST['test_settings']['allow_pause']),
                'feedback_frequency' => sanitize_text_field($_POST['test_settings']['feedback_frequency'])
            );
            update_post_meta($post_id, 'test_settings', $settings);
        }
    }

    public function add_custom_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            if ($key === 'date') {
                $new_columns['test_type'] = __('Test Type', 'attentrack');
                $new_columns['duration'] = __('Duration', 'attentrack');
                $new_columns['age_range'] = __('Age Range', 'attentrack');
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'test_type':
                $test_type = get_post_meta($post_id, 'test_type', true);
                echo esc_html(ucfirst($test_type));
                break;
            
            case 'duration':
                $duration = get_post_meta($post_id, 'test_duration', true);
                printf(__('%d minutes', 'attentrack'), $duration);
                break;
            
            case 'age_range':
                $min_age = get_post_meta($post_id, 'min_age', true);
                $max_age = get_post_meta($post_id, 'max_age', true);
                if ($min_age && $max_age) {
                    printf(__('%d-%d years', 'attentrack'), $min_age, $max_age);
                } else {
                    _e('All ages', 'attentrack');
                }
                break;
        }
    }
}

// Initialize the admin class
AttenTrack_Test_Admin::get_instance();
