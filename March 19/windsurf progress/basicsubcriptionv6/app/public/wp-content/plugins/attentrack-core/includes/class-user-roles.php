<?php
if (!defined('ABSPATH')) exit;

class AttenTrack_User_Roles {
    private static $instance = null;
    
    // Define role capabilities
    private $role_caps = array(
        'administrator' => array(
            'manage_attentrack' => true,
            'edit_tests' => true,
            'edit_others_tests' => true,
            'publish_tests' => true,
            'read_private_tests' => true,
            'delete_tests' => true,
            'delete_others_tests' => true,
            'edit_published_tests' => true,
            'delete_published_tests' => true,
            'view_all_results' => true,
            'export_results' => true,
            'manage_stimulus_templates' => true,
        ),
        'therapist' => array(
            'read' => true,
            'edit_tests' => true,
            'publish_tests' => false,
            'edit_published_tests' => true,
            'view_assigned_results' => true,
            'export_assigned_results' => true,
            'manage_patients' => true,
        ),
        'patient' => array(
            'read' => true,
            'take_tests' => true,
            'view_own_results' => true,
        ),
        'guardian' => array(
            'read' => true,
            'view_dependent_results' => true,
        )
    );

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_roles'));
        add_action('admin_init', array($this, 'add_role_caps'));
        add_action('show_user_profile', array($this, 'add_custom_user_fields'));
        add_action('edit_user_profile', array($this, 'add_custom_user_fields'));
        add_action('personal_options_update', array($this, 'save_custom_user_fields'));
        add_action('edit_user_profile_update', array($this, 'save_custom_user_fields'));
        add_filter('user_has_cap', array($this, 'filter_user_caps'), 10, 4);
    }

    public function register_roles() {
        // Add Therapist Role
        add_role('therapist', __('Therapist', 'attentrack'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ));

        // Add Patient Role
        add_role('patient', __('Patient', 'attentrack'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ));

        // Add Guardian Role
        add_role('guardian', __('Guardian', 'attentrack'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ));
    }

    public function add_role_caps() {
        // Add capabilities to roles
        foreach ($this->role_caps as $role_name => $caps) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap => $grant) {
                    $role->add_cap($cap, $grant);
                }
            }
        }
    }

    public function add_custom_user_fields($user) {
        if (!current_user_can('manage_options') && !current_user_can('manage_patients')) {
            return;
        }
        ?>
        <h3><?php _e('AttenTrack Information', 'attentrack'); ?></h3>
        <table class="form-table">
            <?php if (in_array('patient', (array)$user->roles)): ?>
            <tr>
                <th>
                    <label for="patient_id"><?php _e('Patient ID', 'attentrack'); ?></label>
                </th>
                <td>
                    <input type="text" name="patient_id" id="patient_id" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'patient_id', true)); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="date_of_birth"><?php _e('Date of Birth', 'attentrack'); ?></label>
                </th>
                <td>
                    <input type="date" name="date_of_birth" id="date_of_birth" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'date_of_birth', true)); ?>" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="assigned_therapist"><?php _e('Assigned Therapist', 'attentrack'); ?></label>
                </th>
                <td>
                    <?php
                    wp_dropdown_users(array(
                        'name' => 'assigned_therapist',
                        'selected' => get_user_meta($user->ID, 'assigned_therapist', true),
                        'role' => 'therapist',
                        'show_option_none' => __('Select a Therapist', 'attentrack'),
                    ));
                    ?>
                </td>
            </tr>
            <?php endif; ?>

            <?php if (in_array('guardian', (array)$user->roles)): ?>
            <tr>
                <th>
                    <label><?php _e('Dependent Patients', 'attentrack'); ?></label>
                </th>
                <td>
                    <?php
                    $dependent_patients = get_users(array('role' => 'patient'));
                    $current_dependents = get_user_meta($user->ID, 'dependent_patients', true);
                    if (!is_array($current_dependents)) {
                        $current_dependents = array();
                    }
                    
                    foreach ($dependent_patients as $patient) {
                        ?>
                        <label>
                            <input type="checkbox" name="dependent_patients[]" 
                                   value="<?php echo $patient->ID; ?>"
                                   <?php checked(in_array($patient->ID, $current_dependents)); ?> />
                            <?php echo esc_html($patient->display_name); ?>
                            (<?php echo esc_html(get_user_meta($patient->ID, 'patient_id', true)); ?>)
                        </label><br>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php endif; ?>

            <?php if (in_array('therapist', (array)$user->roles)): ?>
            <tr>
                <th>
                    <label for="specialization"><?php _e('Specialization', 'attentrack'); ?></label>
                </th>
                <td>
                    <input type="text" name="specialization" id="specialization" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'specialization', true)); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="license_number"><?php _e('License Number', 'attentrack'); ?></label>
                </th>
                <td>
                    <input type="text" name="license_number" id="license_number" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'license_number', true)); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }

    public function save_custom_user_fields($user_id) {
        if (!current_user_can('manage_options') && !current_user_can('manage_patients')) {
            return false;
        }

        $user = get_userdata($user_id);
        
        // Patient fields
        if (in_array('patient', (array)$user->roles)) {
            update_user_meta($user_id, 'patient_id', sanitize_text_field($_POST['patient_id']));
            update_user_meta($user_id, 'date_of_birth', sanitize_text_field($_POST['date_of_birth']));
            update_user_meta($user_id, 'assigned_therapist', absint($_POST['assigned_therapist']));
        }

        // Guardian fields
        if (in_array('guardian', (array)$user->roles)) {
            $dependents = isset($_POST['dependent_patients']) ? array_map('absint', $_POST['dependent_patients']) : array();
            update_user_meta($user_id, 'dependent_patients', $dependents);
        }

        // Therapist fields
        if (in_array('therapist', (array)$user->roles)) {
            update_user_meta($user_id, 'specialization', sanitize_text_field($_POST['specialization']));
            update_user_meta($user_id, 'license_number', sanitize_text_field($_POST['license_number']));
        }
    }

    public function filter_user_caps($allcaps, $caps, $args, $user) {
        // Handle viewing test results
        if (in_array('view_test_result', $caps)) {
            $result_id = $args[2];
            $result = get_post($result_id);
            
            // Administrators can view all results
            if (isset($allcaps['view_all_results'])) {
                return $allcaps;
            }
            
            // Therapists can view their assigned patients' results
            if (isset($allcaps['view_assigned_results'])) {
                $patient_id = $result->post_author;
                $assigned_therapist = get_user_meta($patient_id, 'assigned_therapist', true);
                if ($assigned_therapist == $user->ID) {
                    $allcaps['view_test_result'] = true;
                }
            }
            
            // Patients can view their own results
            if (isset($allcaps['view_own_results']) && $result->post_author == $user->ID) {
                $allcaps['view_test_result'] = true;
            }
            
            // Guardians can view their dependents' results
            if (isset($allcaps['view_dependent_results'])) {
                $dependents = get_user_meta($user->ID, 'dependent_patients', true);
                if (is_array($dependents) && in_array($result->post_author, $dependents)) {
                    $allcaps['view_test_result'] = true;
                }
            }
        }
        
        return $allcaps;
    }

    public function get_patient_therapist($patient_id) {
        return get_user_meta($patient_id, 'assigned_therapist', true);
    }

    public function get_therapist_patients($therapist_id) {
        return get_users(array(
            'meta_key' => 'assigned_therapist',
            'meta_value' => $therapist_id,
            'role' => 'patient'
        ));
    }

    public function get_guardian_dependents($guardian_id) {
        $dependent_ids = get_user_meta($guardian_id, 'dependent_patients', true);
        if (!is_array($dependent_ids)) {
            return array();
        }
        return get_users(array(
            'include' => $dependent_ids,
            'role' => 'patient'
        ));
    }
}

// Initialize the roles class
AttenTrack_User_Roles::get_instance();
