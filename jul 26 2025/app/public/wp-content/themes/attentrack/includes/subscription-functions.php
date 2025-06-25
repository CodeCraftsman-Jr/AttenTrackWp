<?php
/**
 * Functions for handling subscriptions and plans
 */

/**
 * Get subscription plans
 * 
 * @return array List of subscription plans
 */
function attentrack_get_subscription_plans() {
    return array(
        // Small Scale Plans
        'small_scale' => array(
            array(
                'type' => 'small_free',
                'name' => 'Free Tier',
                'description' => 'Perfect for individuals getting started',
                'price' => 0,
                'duration' => 0, // unlimited
                'member_limit' => 1,
                'days_limit' => 0, // unlimited
                'features' => array(
                    'Basic attention assessment tools',
                    'Limited reports',
                    'Email support',
                    'Free forever'
                )
            ),
            array(
                'type' => 'small_30',
                'name' => '30 Members Plan',
                'description' => 'For small teams and classrooms',
                'price' => 1999,
                'duration' => 1, // months
                'member_limit' => 30,
                'days_limit' => 30,
                'features' => array(
                    '30 members capacity',
                    'Standard assessment tools',
                    'Basic analytics dashboard',
                    'Priority email support',
                    '30 days access'
                )
            ),
            array(
                'type' => 'small_60',
                'name' => '60 Members Plan',
                'description' => 'For growing teams and schools',
                'price' => 3499,
                'duration' => 2, // months
                'member_limit' => 60,
                'days_limit' => 60,
                'features' => array(
                    '60 members capacity',
                    'Advanced assessment tools',
                    'Comprehensive analytics',
                    'Priority support',
                    '60 days access'
                )
            ),
            array(
                'type' => 'small_90',
                'name' => '90 Members Plan',
                'description' => 'For established teams',
                'price' => 4999,
                'duration' => 3, // months
                'member_limit' => 90,
                'days_limit' => 100,
                'features' => array(
                    '90 members capacity',
                    'Full assessment suite',
                    'Advanced reporting tools',
                    '24/7 priority support',
                    '100 days access'
                )
            )
        ),
        
        // Large Scale Plans
        'large_scale' => array(
            array(
                'type' => 'large_120',
                'name' => '120 Members Plan',
                'description' => 'For medium organizations',
                'price' => 5999,
                'duration' => 1, // months
                'member_limit' => 120,
                'days_limit' => 30,
                'features' => array(
                    '120 members capacity',
                    'Enterprise-grade assessment tools',
                    'Advanced analytics dashboard',
                    'Priority support with dedicated manager',
                    '30 days access'
                )
            ),
            array(
                'type' => 'large_160',
                'name' => '160 Members Plan',
                'description' => 'For larger organizations',
                'price' => 7999,
                'duration' => 2, // months
                'member_limit' => 160,
                'days_limit' => 60,
                'features' => array(
                    '160 members capacity',
                    'Complete assessment ecosystem',
                    'Custom reporting tools',
                    '24/7 priority support',
                    '60 days access'
                )
            ),
            array(
                'type' => 'large_unlimited',
                'name' => 'Unlimited Plan',
                'description' => 'For enterprise organizations',
                'price' => 9999,
                'duration' => 3, // months
                'member_limit' => 0, // unlimited
                'days_limit' => 90,
                'features' => array(
                    'Unlimited members',
                    'Full enterprise solution',
                    'Custom integration options',
                    'Dedicated account manager',
                    '90 days access'
                )
            )
        )
    );
}

/**
 * Get all plans as a flat array
 * 
 * @return array Flat list of all subscription plans
 */
function attentrack_get_all_plans_flat() {
    $grouped_plans = attentrack_get_subscription_plans();
    $flat_plans = array();
    
    foreach ($grouped_plans as $group => $plans) {
        foreach ($plans as $plan) {
            $plan['group'] = $group;
            $flat_plans[] = $plan;
        }
    }
    
    return $flat_plans;
}

/**
 * Get plan by type
 * 
 * @param string $plan_type Type of plan to get
 * @return array|null Plan details or null if not found
 */
function attentrack_get_plan_by_type($plan_type) {
    $plans = attentrack_get_all_plans_flat();
    foreach ($plans as $plan) {
        if ($plan['type'] === $plan_type) {
            return $plan;
        }
    }
    return null;
}

/**
 * Get subscription status for a user
 * 
 * @param int $user_id User ID
 * @return array Subscription status data
 */
function attentrack_get_subscription_status($user_id = 0) {
    global $wpdb;
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array(
            'has_subscription' => false,
            'plan_name' => 'Free',
            'plan_name_formatted' => 'Free',
            'plan_group' => 'small_scale',
            'status' => 'inactive',
            'member_limit' => 1,
            'days_limit' => 0,
            'members_used' => 0,
            'days_used' => 0,
            'days_remaining' => 0,
            'start_date' => '',
            'end_date' => '',
            'is_expired' => false,
            'is_institution' => false
        );
    }
    
    // Check if user is an institution
    $is_institution = user_can($user_id, 'institution');
    
    // Get active subscription - prioritize paid plans over free plans
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_subscriptions
        WHERE user_id = %d AND status = 'active'
        ORDER BY amount DESC, created_at DESC LIMIT 1",
        $user_id
    ));

    // Debug logging
    error_log('Subscription query for user ' . $user_id . ': ' . $wpdb->last_query);
    if ($subscription) {
        error_log('Found active subscription: ID=' . $subscription->id . ', plan=' . $subscription->plan_name . ', amount=' . $subscription->amount . ', status=' . $subscription->status);
    } else {
        error_log('No active subscription found for user ' . $user_id);

        // Check if there are any subscriptions at all
        $all_subs = $wpdb->get_results($wpdb->prepare(
            "SELECT id, plan_name, amount, status, created_at FROM {$wpdb->prefix}attentrack_subscriptions
            WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));

        if ($all_subs) {
            error_log('Found ' . count($all_subs) . ' total subscriptions for user ' . $user_id . ': ' . print_r($all_subs, true));

            // If there are paid subscriptions that are inactive, activate the latest one
            foreach ($all_subs as $sub) {
                if ($sub->amount > 0 && $sub->status !== 'active') {
                    error_log('Reactivating paid subscription ID ' . $sub->id . ' for user ' . $user_id);
                    $wpdb->update(
                        $wpdb->prefix . 'attentrack_subscriptions',
                        array('status' => 'active'),
                        array('id' => $sub->id)
                    );

                    // Deactivate others
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}attentrack_subscriptions
                        SET status = 'inactive'
                        WHERE user_id = %d AND id != %d",
                        $user_id, $sub->id
                    ));

                    // Re-fetch the subscription
                    $subscription = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}attentrack_subscriptions WHERE id = %d",
                        $sub->id
                    ));
                    break;
                }
            }
        }
    }
    
    if (!$subscription) {
        // For institutions, return active free plan instead of inactive
        if ($is_institution) {
            return array(
                'has_subscription' => true,
                'plan_name' => 'small_free',
                'plan_name_formatted' => 'Free Tier',
                'plan_group' => 'small_scale',
                'status' => 'active',
                'member_limit' => 1,
                'days_limit' => 0,
                'members_used' => 0,
                'days_used' => 0,
                'days_remaining' => 0,
                'start_date' => current_time('mysql'),
                'end_date' => '',
                'is_expired' => false,
                'is_institution' => $is_institution
            );
        } else {
            return array(
                'has_subscription' => false,
                'plan_name' => 'Free',
                'plan_name_formatted' => 'Free',
                'plan_group' => 'small_scale',
                'status' => 'inactive',
                'member_limit' => 1,
                'days_limit' => 0,
                'members_used' => 0,
                'days_used' => 0,
                'days_remaining' => 0,
                'start_date' => '',
                'end_date' => '',
                'is_expired' => false,
                'is_institution' => $is_institution
            );
        }
    }
    
    // Calculate days used and remaining
    $start_date = strtotime($subscription->start_date);
    $current_date = current_time('timestamp');
    $days_used = floor(($current_date - $start_date) / (60 * 60 * 24));
    
    $days_remaining = 0;
    $is_expired = false;
    
    if ($subscription->end_date) {
        $end_date = strtotime($subscription->end_date);
        $days_remaining = max(0, floor(($end_date - $current_date) / (60 * 60 * 24)));
        $is_expired = $current_date > $end_date;
    }
    
    // Get members count for institution
    $members_used = 0;
    
    if ($is_institution) {
        // Check if institution exists in the new table
        $institution = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
            $user_id
        ));
        
        if ($institution) {
            // Get count from the new table
            $members_used = $institution->members_used;
        } else {
            // Legacy: Get count from user meta
            $members_used = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'institution_id' AND meta_value = %d",
                $user_id
            ));
            
            // Create institution record with this data
            require_once get_template_directory() . '/inc/institution-functions.php';
            $institution_id = attentrack_create_or_update_institution($user_id, array(
                'member_limit' => $subscription->member_limit,
                'members_used' => $members_used
            ));
        }
    }
    
    // Format plan name properly
    $plan_name_formatted = $subscription->plan_name;
    if ($subscription->plan_name === 'small_free') {
        $plan_name_formatted = 'Free Tier';
    } elseif ($subscription->plan_name === 'small_30') {
        $plan_name_formatted = '30 Members Plan';
    } elseif ($subscription->plan_name === 'small_60') {
        $plan_name_formatted = '60 Members Plan';
    } elseif ($subscription->plan_name === 'large_120') {
        $plan_name_formatted = '120 Members Plan';
    } elseif ($subscription->plan_name === 'large_160') {
        $plan_name_formatted = '160 Members Plan';
    } else {
        $plan_name_formatted = ucfirst(str_replace('_', ' ', $subscription->plan_name));
    }

    return array(
        'has_subscription' => true,
        'plan_name' => $subscription->plan_name,
        'plan_name_formatted' => $plan_name_formatted,
        'plan_group' => $subscription->plan_group,
        'status' => $subscription->status,
        'member_limit' => $subscription->member_limit,
        'days_limit' => $subscription->days_limit,
        'members_used' => $members_used,
        'days_used' => $days_used,
        'days_remaining' => $days_remaining,
        'start_date' => $subscription->start_date,
        'end_date' => $subscription->end_date,
        'is_expired' => $is_expired,
        'is_institution' => $is_institution
    );
}

/**
 * Check if subscription is active
 * 
 * @param int $user_id User ID to check subscription for
 * @return bool True if subscription is active, false otherwise
 */
function attentrack_is_subscription_active($user_id) {
    $subscription = attentrack_get_subscription_status($user_id);
    return $subscription['status'] === 'active';
}

/**
 * Create a new subscription
 * 
 * @param int $user_id User ID
 * @param string $plan_type Plan type
 * @param string $payment_id Payment ID
 * @param string $order_id Order ID
 * @return bool|int ID of new subscription or false on failure
 */
function attentrack_create_subscription($user_id, $plan_type, $payment_id, $order_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_subscriptions';
    
    $plan = attentrack_get_plan_by_type($plan_type);
    if (!$plan) {
        return false;
    }
    
    $start_date = current_time('mysql');
    $end_date = null;
    
    // Calculate end date if not free plan
    if ($plan['duration'] > 0) {
        $end_date = date('Y-m-d H:i:s', strtotime("+{$plan['duration']} months", strtotime($start_date)));
    }
    
    // Insert subscription
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'profile_id' => 'profile_' . $user_id . '_' . time(),
            'plan_name' => $plan_type,
            'plan_group' => $plan['group'],
            'amount' => $plan['price'],
            'duration_months' => $plan['duration'],
            'member_limit' => $plan['member_limit'],
            'days_limit' => $plan['days_limit'],
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'status' => 'active',
            'start_date' => $start_date,
            'end_date' => $end_date
        )
    );
    
    if ($result) {
        return $wpdb->insert_id;
    }
    
    return false;
}

/**
 * Format subscription expiry date
 * 
 * @param string $end_date Expiry date to format
 * @return string Formatted expiry date
 */
function attentrack_format_subscription_expiry($end_date) {
    if (!$end_date) return 'Never expires';
    
    $expiry_date = strtotime($end_date);
    $now = current_time('timestamp');
    $diff = $expiry_date - $now;
    
    if ($diff < 0) {
        return 'Expired';
    }
    
    $days = floor($diff / (60 * 60 * 24));
    if ($days > 30) {
        return date('F j, Y', $expiry_date);
    } elseif ($days > 0) {
        return $days . ' day' . ($days > 1 ? 's' : '') . ' remaining';
    } else {
        $hours = floor($diff / (60 * 60));
        if ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' remaining';
        } else {
            return 'Less than an hour remaining';
        }
    }
}

/**
 * Get available member slots for a user
 * 
 * @param int $user_id User ID
 * @return int Number of available member slots
 */
function attentrack_get_available_member_slots($user_id) {
    global $wpdb;
    
    $subscription = attentrack_get_subscription_status($user_id);
    
    // If unlimited members
    if ($subscription['member_limit'] === 0) {
        return PHP_INT_MAX;
    }
    
    // Count current members
    $table_name = $wpdb->prefix . 'attentrack_patient_details';
    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
            $user_id
        )
    );
    
    return max(0, $subscription['member_limit'] - (int)$count);
}

/**
 * Get remaining days for a subscription
 * 
 * @param int $user_id User ID
 * @return int Number of remaining days (0 if expired, -1 if unlimited)
 */
function attentrack_get_remaining_days($user_id) {
    $subscription = attentrack_get_subscription_status($user_id);
    
    // If no end date (unlimited)
    if (!$subscription['end_date']) {
        return -1;
    }
    
    $expiry_date = strtotime($subscription['end_date']);
    $now = current_time('timestamp');
    $diff = $expiry_date - $now;
    
    if ($diff < 0) {
        return 0;
    }
    
    return ceil($diff / (60 * 60 * 24));
}
