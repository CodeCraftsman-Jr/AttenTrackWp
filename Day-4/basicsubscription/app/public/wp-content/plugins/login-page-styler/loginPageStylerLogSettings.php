<?php

function lps_display_login_logs() {
	global $wpdb;

	// Display successful logins
	display_login_logs_table( $wpdb->prefix . 'user_login_logs', 'Admin And User Login Logs', false );

	// Display failed login attempts
	display_login_logs_table( $wpdb->prefix . 'user_failed_login_logs', 'All Failed Login Attempts ,Users and Non Users', true );
}

function display_login_logs_table( $table_name, $title, $is_failed_login = false ) {
    global $wpdb;

    // Fetch logs
    $logs = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY login_time DESC" );

    echo '<style>
        .lps-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .lps-table th, .lps-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .lps-table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }
        .lps-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .lps-table tr:hover {
            background-color: #f1f1f1;
        }
        .lps-button {
            background-color: #0073aa;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin: 5px 0;
            transition: background-color 0.3s;
        }
        .lps-button:hover {
            background-color: #ffba00;
        }
        .lps-no-logs {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>';

    echo '<h2>' . esc_html( $title ) . '</h2>';

    // Display Delete All Logs Form
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="' . ( $is_failed_login ? 'lps_delete_all_failed_logs' : 'lps_delete_all_login_logs' ) . '" />';
    wp_nonce_field( 'lps_delete_all_logs_nonce', 'lps_nonce_field' );
    echo '<input type="submit" class="lps-button" value="Delete All ' . ( $is_failed_login ? 'Failed' : '' ) . ' Login Logs" onclick="return confirm(\'Are you sure you want to delete all ' . ( $is_failed_login ? 'failed' : '' ) . ' login logs?\');" />';
    echo '</form>';

    if ( empty( $logs ) ) {
        echo '<p class="lps-no-logs">No logs found.</p>';
        return;
    }

    echo '<table class="lps-table">';
    echo '<thead><tr><th>User</th><th>Time</th><th>IP Address</th>';

    if ( ! $is_failed_login ) {
        echo '<th>Role</th><th>Profile Picture</th><th>Status</th><th>Actions</th>';
    }

    echo '</tr></thead><tbody>';

    foreach ( $logs as $log ) {
        echo '<tr>';
        echo '<td>' . esc_html( $log->user_login ) . '</td>';
        echo '<td>' . esc_html( $log->login_time ) . '</td>';
        echo '<td>' . esc_html( $log->user_ip ) . '</td>';

        if ( ! $is_failed_login ) {
            echo '<td>' . esc_html( $log->user_role ) . '</td>';
            echo '<td><img src="' . esc_url( $log->profile_picture ) . '" alt="Profile Picture" width="50" /></td>';
            
            $is_active = lps_check_user_active_status( $log->user_id );
            echo '<td>' . ( $is_active ? '<span style="color: green; font-weight: bold;">● Active</span>' : '<span style="color: red; font-weight: bold;">● Inactive</span>' ) . '</td>';
            
            $delete_url = add_query_arg(
                [
                    'action'    => 'lps_delete_login_log',
                    'log_id'    => $log->id,
                    'lps_nonce' => wp_create_nonce( 'lps_delete_login_log_nonce' ),
                ],
                admin_url( 'admin-post.php' )
            );
            $end_session_url = add_query_arg(
                [
                    'action'    => 'lps_end_session',
                    'user_id'   => $log->user_id,
                    'lps_nonce' => wp_create_nonce( 'lps_end_session_nonce' ),
                ],
                admin_url( 'admin-post.php' )
            );

            echo '<td>
                <a href="' . esc_url( $delete_url ) . '" class="lps-button" onclick="return confirm(\'Are you sure you want to delete this log?\');">Delete</a>
                <a href="' . esc_url( $end_session_url ) . '" class="lps-button">End Session</a>
            </td>';
        }

        echo '</tr>';
    }

    echo '</tbody></table>';
}


// Add hooks and functions to handle login log actions
add_action('admin_post_lps_delete_login_log', 'lps_handle_delete_login_log');
add_action('admin_post_lps_end_session', 'lps_handle_end_session');
add_action('admin_post_lps_delete_all_login_logs', 'lps_handle_delete_all_logs');
add_action('admin_post_lps_delete_all_failed_logs', 'lps_handle_delete_all_failed_logs');

function lps_handle_delete_login_log() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Verify nonce
    if (!isset($_GET['lps_nonce']) || !wp_verify_nonce($_GET['lps_nonce'], 'lps_delete_login_log_nonce')) {
        wp_die(__('Nonce verification failed.'));
    }

    if (isset($_GET['log_id']) && $_GET['action'] === 'lps_delete_login_log') {
        $log_id = intval($_GET['log_id']);
        lps_delete_login_log($log_id); // Call your delete function

        // Redirect back to the logs page after deletion
        wp_redirect(admin_url('admin.php?page=lps_user_login_logs'));
        exit();
    }
}

function lps_handle_end_session() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Verify nonce
    if (!isset($_GET['lps_nonce']) || !wp_verify_nonce($_GET['lps_nonce'], 'lps_end_session_nonce')) {
        wp_die(__('Nonce verification failed.'));
    }

    if (isset($_GET['user_id']) && $_GET['action'] === 'lps_end_session') {
        $user_id = intval($_GET['user_id']);
        lps_end_user_session($user_id); // Call the end session function

        // Redirect back to the logs page after ending the session
        wp_redirect(admin_url('admin.php?page=lps_user_login_logs'));
        exit();
    }
}

function lps_handle_delete_all_logs() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Verify nonce
    if (!isset($_POST['lps_nonce_field']) || !wp_verify_nonce($_POST['lps_nonce_field'], 'lps_delete_all_logs_nonce')) {
        wp_die(__('Nonce verification failed.'));
    }

    global $wpdb;
    $action = sanitize_text_field($_POST['action']);

    // Determine the table based on action
    $table_name = $wpdb->prefix . ($action === 'lps_delete_all_failed_logs' ? 'user_failed_login_logs' : 'user_login_logs');

    // Delete logs
    $wpdb->query("DELETE FROM $table_name");

    // Redirect with success message
    wp_redirect(admin_url('admin.php?page=lps_user_login_logs'));
    exit();
}

function lps_handle_delete_all_failed_logs() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Verify nonce
    if (!isset($_POST['lps_nonce_field']) || !wp_verify_nonce($_POST['lps_nonce_field'], 'lps_delete_all_logs_nonce')) {
        wp_die(__('Nonce verification failed.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_failed_login_logs';

    // Delete all failed login logs
    $wpdb->query("DELETE FROM $table_name");

    wp_redirect(admin_url('admin.php?page=lps_user_login_logs'));
    exit();
}


function lps_delete_login_log( $log_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'user_login_logs';
	$wpdb->delete( $table_name, array( 'id' => $log_id ) );  // Change 'log_id' to 'id'
}

function lps_end_user_session( $user_id ) {
	if ( class_exists( 'WP_Session_Tokens' ) ) {
		$sessions = WP_Session_Tokens::get_instance( $user_id );
		$sessions->destroy_all();
	}
}

function lps_check_user_active_status( $user_id ) {
	if ( class_exists( 'WP_Session_Tokens' ) ) {
		$sessions       = WP_Session_Tokens::get_instance( $user_id );
		$session_tokens = $sessions->get_all();
		return ! empty( $session_tokens );
	}
	return false;
}

function lps_export_login_logs() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'user_login_logs';
	$logs       = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY login_time DESC" );

	// Set headers for CSV download
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment;filename="login_logs.csv"' );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'User', 'Login Time', 'IP Address', 'Role', 'Profile Picture', 'Active Status', 'Failed Logins', 'Password Resets', 'Session Duration' ) );

	foreach ( $logs as $log ) {
		// Check if the user is currently active
		$is_active = false;
		if ( class_exists( 'WP_Session_Tokens' ) ) {
			$sessions       = WP_Session_Tokens::get_instance( $log->user_id );
			$session_tokens = $sessions->get_all();
			if ( ! empty( $session_tokens ) ) {
				$is_active = true;
			}
		}

		fputcsv(
			$output,
			array(
				$log->user_login,
				$log->login_time,
				$log->user_ip,
				$log->user_role,
				$log->profile_picture,
				$is_active ? 'Active' : 'Inactive',
				'0', // Placeholder for failed logins
				'0', // Placeholder for password resets
				'0',  // Placeholder for session duration
			)
		);
	}

	fclose( $output );
	exit();
}
