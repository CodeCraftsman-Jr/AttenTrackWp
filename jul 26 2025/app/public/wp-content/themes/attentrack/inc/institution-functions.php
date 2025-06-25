<?php
/**
 * Institution Functions
 * 
 * Functions for managing institution data in the dedicated tables
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create or update institution data
 * 
 * @param int $user_id User ID of the institution
 * @param array $data Institution data
 * @return int|false Institution ID on success, false on failure
 */
function attentrack_create_or_update_institution($user_id, $data = array()) {
    global $wpdb;
    
    // Check if user exists and has institution role
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return false;
    }
    
    // Set institution role if not already set
    if (!in_array('institution', $user->roles)) {
        $user->set_role('institution');
    }
    
    // Default data
    $default_data = array(
        'institution_name' => $user->display_name,
        'contact_email' => $user->user_email,
        'status' => 'active',
        'member_limit' => 0,
        'members_used' => 0
    );
    
    // Merge with provided data
    $data = wp_parse_args($data, $default_data);
    
    // Check if institution already exists
    $institution_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
        $user_id
    ));
    
    if ($institution_id) {
        // Update existing institution
        $wpdb->update(
            $wpdb->prefix . 'attentrack_institutions',
            $data,
            array('id' => $institution_id)
        );
        
        return $institution_id;
    } else {
        // Create new institution
        $data['user_id'] = $user_id;
        
        $wpdb->insert(
            $wpdb->prefix . 'attentrack_institutions',
            $data
        );
        
        return $wpdb->insert_id;
    }
}

/**
 * Get institution data
 * 
 * @param int $user_id User ID of the institution
 * @return array|false Institution data on success, false if not found
 */
function attentrack_get_institution($user_id) {
    global $wpdb;
    
    $institution = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
        $user_id
    ), ARRAY_A);
    
    if (!$institution) {
        return false;
    }
    
    return $institution;
}

/**
 * Add a member to an institution
 * 
 * @param int $institution_id Institution ID
 * @param int $user_id User ID to add as member
 * @param string $role Role of the member (default: 'member')
 * @param int $added_by User ID of the admin who added this member
 * @return int|false Member ID on success, false on failure
 */
function attentrack_add_institution_member($institution_id, $user_id, $role = 'member', $added_by = 0) {
    global $wpdb;
    
    // Check if institution exists
    $institution = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_institutions WHERE id = %d",
        $institution_id
    ));
    
    if (!$institution) {
        return false;
    }
    
    // Check if user exists
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return false;
    }
    
    // Check if user is already a member
    $existing_member = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}attentrack_institution_members WHERE institution_id = %d AND user_id = %d",
        $institution_id,
        $user_id
    ));
    
    if ($existing_member) {
        // Update existing member
        $wpdb->update(
            $wpdb->prefix . 'attentrack_institution_members',
            array(
                'role' => $role,
                'status' => 'active',
                'updated_at' => current_time('mysql', true)
            ),
            array('id' => $existing_member)
        );
        
        return $existing_member;
    } else {
        // Add new member
        $wpdb->insert(
            $wpdb->prefix . 'attentrack_institution_members',
            array(
                'institution_id' => $institution_id,
                'user_id' => $user_id,
                'role' => $role,
                'added_by' => $added_by,
                'status' => 'active',
                'created_at' => current_time('mysql', true)
            )
        );
        
        // Update members_used count
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}attentrack_institutions 
            SET members_used = (
                SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members 
                WHERE institution_id = %d AND status = 'active'
            ) 
            WHERE id = %d",
            $institution_id,
            $institution_id
        ));
        
        return $wpdb->insert_id;
    }
}

/**
 * Remove a member from an institution
 * 
 * @param int $institution_id Institution ID
 * @param int $user_id User ID to remove
 * @return bool True on success, false on failure
 */
function attentrack_remove_institution_member($institution_id, $user_id) {
    global $wpdb;
    
    // Update member status to inactive
    $result = $wpdb->update(
        $wpdb->prefix . 'attentrack_institution_members',
        array(
            'status' => 'inactive',
            'updated_at' => current_time('mysql', true)
        ),
        array(
            'institution_id' => $institution_id,
            'user_id' => $user_id
        )
    );
    
    if ($result) {
        // Update members_used count
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}attentrack_institutions 
            SET members_used = (
                SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members 
                WHERE institution_id = %d AND status = 'active'
            ) 
            WHERE id = %d",
            $institution_id,
            $institution_id
        ));
        
        return true;
    }
    
    return false;
}

/**
 * Get institution members
 * 
 * @param int $institution_id Institution ID
 * @param string $status Member status (active, inactive, or all)
 * @return array List of members
 */
function attentrack_get_institution_members($institution_id, $status = 'active') {
    global $wpdb;
    
    $query = "SELECT m.*, u.user_login, u.user_email, u.display_name 
              FROM {$wpdb->prefix}attentrack_institution_members m
              LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID
              WHERE m.institution_id = %d";
    
    $params = array($institution_id);
    
    if ($status !== 'all') {
        $query .= " AND m.status = %s";
        $params[] = $status;
    }
    
    $query .= " ORDER BY m.created_at DESC";
    
    $members = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
    
    return $members ?: array();
}

/**
 * Check if user is a member of an institution
 * 
 * @param int $user_id User ID
 * @param int $institution_id Institution ID (optional)
 * @return bool|array True if member, array of institution data if institution_id not provided, false if not a member
 */
function attentrack_is_institution_member($user_id, $institution_id = 0) {
    global $wpdb;
    
    if ($institution_id) {
        // Check specific institution
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $user_id,
            $institution_id
        ));
        
        return (bool) $is_member;
    } else {
        // Get all institutions user is a member of
        $institutions = $wpdb->get_results($wpdb->prepare(
            "SELECT i.* FROM {$wpdb->prefix}attentrack_institutions i
            INNER JOIN {$wpdb->prefix}attentrack_institution_members m ON i.id = m.institution_id
            WHERE m.user_id = %d AND m.status = 'active' AND i.status = 'active'",
            $user_id
        ), ARRAY_A);
        
        return $institutions ?: false;
    }
}

/**
 * Get institution analytics data
 * 
 * @param int $institution_id Institution ID
 * @return array Analytics data
 */
function attentrack_get_institution_analytics($institution_id) {
    global $wpdb;
    
    // Get total members
    $total_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members 
        WHERE institution_id = %d AND status = 'active'",
        $institution_id
    ));
    
    if (!$total_members) {
        $total_members = 0;
    }
    
    // Get active members (who have taken tests in the last 30 days)
    $active_members = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT m.user_id) FROM {$wpdb->prefix}attentrack_institution_members m
        LEFT JOIN {$wpdb->prefix}attentrack_selective_results r ON m.user_id = r.user_id
        WHERE m.institution_id = %d AND m.status = 'active' 
        AND (r.test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR r.test_date IS NULL)",
        $institution_id
    ));
    
    if (!$active_members) {
        $active_members = 0;
    }
    
    // Get total tests taken
    $tests_taken = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members m
        LEFT JOIN {$wpdb->prefix}attentrack_selective_results r ON m.user_id = r.user_id
        WHERE m.institution_id = %d AND m.status = 'active'",
        $institution_id
    ));
    
    if (!$tests_taken) {
        $tests_taken = 0;
    }
    
    // Get selective attention tests
    $selective_tests = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members m
        LEFT JOIN {$wpdb->prefix}attentrack_selective_results r ON m.user_id = r.user_id
        WHERE m.institution_id = %d AND m.status = 'active'",
        $institution_id
    ));
    
    // Get divided attention tests
    $divided_tests = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members m
        LEFT JOIN {$wpdb->prefix}attentrack_divided_results r ON m.user_id = r.user_id
        WHERE m.institution_id = %d AND m.status = 'active'",
        $institution_id
    ));
    
    // Get extended attention tests
    $extended_tests = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members m
        LEFT JOIN {$wpdb->prefix}attentrack_extended_results r ON m.user_id = r.user_id
        WHERE m.institution_id = %d AND m.status = 'active'",
        $institution_id
    ));
    
    // Get test performance data
    $test_performance = array(
        array(
            'name' => 'Selective Attention',
            'total' => intval($selective_tests),
            'average_score' => 75,
            'best_performer' => '',
            'needs_improvement' => ''
        ),
        array(
            'name' => 'Divided Attention',
            'total' => intval($divided_tests),
            'average_score' => 68,
            'best_performer' => '',
            'needs_improvement' => ''
        ),
        array(
            'name' => 'Extended Attention',
            'total' => intval($extended_tests),
            'average_score' => 82,
            'best_performer' => '',
            'needs_improvement' => ''
        )
    );
    
    return array(
        'total_members' => intval($total_members),
        'active_members' => intval($active_members),
        'tests_taken' => intval($tests_taken),
        'test_performance' => $test_performance
    );
}

/**
 * Migrate existing institution data to the new tables
 * 
 * This function should be called once to migrate data from user meta to the new tables
 */
function attentrack_migrate_institution_data() {
    global $wpdb;
    
    // Get all users with institution role
    $institution_users = get_users(array(
        'role' => 'institution',
        'fields' => 'ID'
    ));
    
    if (empty($institution_users)) {
        return false;
    }
    
    $count = 0;
    
    foreach ($institution_users as $user_id) {
        // Create institution record
        $institution_id = attentrack_create_or_update_institution($user_id);
        
        if ($institution_id) {
            $count++;
            
            // Get users linked to this institution
            $member_users = $wpdb->get_col($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'institution_id' AND meta_value = %d",
                $user_id
            ));
            
            if (!empty($member_users)) {
                foreach ($member_users as $member_id) {
                    attentrack_add_institution_member($institution_id, $member_id);
                }
            }
        }
    }
    
    return $count;
}
