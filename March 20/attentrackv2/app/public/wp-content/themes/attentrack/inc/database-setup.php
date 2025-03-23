<?php
// Function to create required database tables
function create_attentrack_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Patient Details Table
    $patient_details = $wpdb->prefix . 'attentrack_patient_details';
    $sql_patient_details = "CREATE TABLE IF NOT EXISTS $patient_details (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        profile_id varchar(50) NOT NULL,
        patient_id varchar(50) NOT NULL,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        age int(3) NOT NULL,
        gender varchar(20) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY profile_id (profile_id)
    ) $charset_collate;";

    // Selective Attention Test Results
    $selective_results = $wpdb->prefix . 'attentrack_selective_results';
    $sql_selective = "CREATE TABLE IF NOT EXISTS $selective_results (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        total_letters int NOT NULL,
        p_letters int NOT NULL,
        correct_responses int NOT NULL,
        incorrect_responses int NOT NULL,
        reaction_time decimal(10,2) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Selective Attention Test Extended Results
    $extended_results = $wpdb->prefix . 'attentrack_extended_results';
    $sql_extended = "CREATE TABLE IF NOT EXISTS $extended_results (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        phase int NOT NULL,
        total_letters int NOT NULL,
        p_letters int NOT NULL,
        correct_responses int NOT NULL,
        incorrect_responses int NOT NULL,
        reaction_time decimal(10,2) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Divided Attention Test Results
    $divided_results = $wpdb->prefix . 'attentrack_divided_results';
    $sql_divided = "CREATE TABLE IF NOT EXISTS $divided_results (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        correct_responses int NOT NULL,
        incorrect_responses int NOT NULL,
        reaction_time decimal(10,2) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Alternative Attention Test Results
    $alternative_results = $wpdb->prefix . 'attentrack_alternative_results';
    $sql_alternative = "CREATE TABLE IF NOT EXISTS $alternative_results (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        correct_responses int NOT NULL,
        incorrect_responses int NOT NULL,
        reaction_time decimal(10,2) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Execute SQL
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_patient_details);
    dbDelta($sql_selective);
    dbDelta($sql_extended);
    dbDelta($sql_divided);
    dbDelta($sql_alternative);
    
    // Log table creation
    error_log('AttenTrack tables created or updated');
}

// Create tables when plugin/theme is activated
add_action('after_switch_theme', 'create_attentrack_tables');

// Also run the function directly to ensure tables exist
create_attentrack_tables();

// Function to save patient details
function save_patient_details($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_patient_details';
    
    return $wpdb->insert($table_name, array(
        'profile_id' => $data['profile_id'],
        'patient_id' => $data['patient_id'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'age' => $data['age'],
        'gender' => $data['gender'],
        'email' => $data['email'],
        'phone' => $data['phone']
    ));
}

// Function to save selective attention test results
function save_selective_results($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_selective_results';
    
    return $wpdb->insert($table_name, array(
        'test_id' => $data['test_id'],
        'profile_id' => $data['profile_id'],
        'total_letters' => $data['total_letters'],
        'p_letters' => $data['p_letters'],
        'correct_responses' => $data['correct_responses'],
        'incorrect_responses' => $data['incorrect_responses'],
        'reaction_time' => $data['reaction_time']
    ));
}

// Function to save extended test results
function save_extended_results($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_extended_results';
    
    // Ensure all required fields are present
    $required_fields = array('test_id', 'profile_id', 'phase', 'total_letters', 'p_letters', 'correct_responses', 'incorrect_responses', 'reaction_time');
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            error_log("Missing required field for extended results: {$field}");
            return false;
        }
    }
    
    // Format data for insertion
    $insert_data = array(
        'test_id' => sanitize_text_field($data['test_id']),
        'profile_id' => sanitize_text_field($data['profile_id']),
        'phase' => intval($data['phase']),
        'total_letters' => intval($data['total_letters']),
        'p_letters' => intval($data['p_letters']),
        'correct_responses' => intval($data['correct_responses']),
        'incorrect_responses' => intval($data['incorrect_responses']),
        'reaction_time' => floatval($data['reaction_time'])
    );
    
    // Log the data being inserted
    error_log("Inserting data into {$table_name}: " . print_r($insert_data, true));
    
    // Perform the insert
    $result = $wpdb->insert($table_name, $insert_data);
    
    // Log any database errors
    if ($result === false) {
        error_log("Database error when inserting extended results: {$wpdb->last_error}");
    } else {
        error_log("Successfully inserted extended results with ID: {$wpdb->insert_id}");
    }
    
    return $result;
}

// Function to save alternative test results
function save_alternative_results($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_alternative_results';
    
    return $wpdb->insert($table_name, array(
        'test_id' => $data['test_id'],
        'profile_id' => $data['profile_id'],
        'correct_responses' => $data['correct_responses'],
        'incorrect_responses' => $data['incorrect_responses'],
        'reaction_time' => $data['reaction_time']
    ));
}

// Function to save divided attention test results
function save_divided_results($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_divided_results';
    
    return $wpdb->insert($table_name, array(
        'test_id' => $data['test_id'],
        'profile_id' => $data['profile_id'],
        'correct_responses' => $data['correct_responses'],
        'incorrect_responses' => $data['incorrect_responses'],
        'reaction_time' => $data['reaction_time']
    ));
}

// Function to get patient details by profile ID
function get_patient_details($profile_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'attentrack_patient_details';
    
    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE profile_id = %s", $profile_id)
    );
}

// Function to get all test results for a profile
function get_all_test_results($profile_id) {
    global $wpdb;
    
    $selective_table = $wpdb->prefix . 'attentrack_selective_results';
    $extended_table = $wpdb->prefix . 'attentrack_extended_results';
    $alternative_table = $wpdb->prefix . 'attentrack_alternative_results';
    $divided_table = $wpdb->prefix . 'attentrack_divided_results';
    
    $results = array(
        'selective' => $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $selective_table WHERE profile_id = %s ORDER BY test_date DESC", $profile_id)
        ),
        'extended' => $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $extended_table WHERE profile_id = %s ORDER BY test_date DESC", $profile_id)
        ),
        'alternative' => $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $alternative_table WHERE profile_id = %s ORDER BY test_date DESC", $profile_id)
        ),
        'divided' => $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $divided_table WHERE profile_id = %s ORDER BY test_date DESC", $profile_id)
        )
    );
    
    return $results;
}
