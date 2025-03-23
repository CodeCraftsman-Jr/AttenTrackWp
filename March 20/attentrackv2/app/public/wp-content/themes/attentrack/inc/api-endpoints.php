<?php
// Register REST API endpoints
add_action('rest_api_init', function() {
    // Save patient details
    register_rest_route('attentrack/v1', '/save-patient-details', array(
        'methods' => 'POST',
        'callback' => 'save_patient_details_endpoint',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ));

    // Save test results
    register_rest_route('attentrack/v1', '/save-test-results', array(
        'methods' => 'POST',
        'callback' => 'save_test_results_endpoint',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ));

    // Get patient details
    register_rest_route('attentrack/v1', '/get-patient-details/(?P<profile_id>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_patient_details_endpoint',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ));

    // Get test results
    register_rest_route('attentrack/v1', '/get-test-results/(?P<profile_id>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_test_results_endpoint',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ));
});

// Endpoint callbacks
function save_patient_details_endpoint($request) {
    $params = $request->get_params();
    
    if (empty($params['profile_id'])) {
        return new WP_Error('missing_id', 'Profile ID is required', array('status' => 400));
    }

    $result = save_patient_details($params);
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to save patient details', array('status' => 500));
    }

    return array(
        'success' => true,
        'message' => 'Patient details saved successfully'
    );
}

function save_test_results_endpoint($request) {
    $params = $request->get_params();
    
    if (empty($params['test_type']) || empty($params['test_id']) || empty($params['profile_id'])) {
        return new WP_Error('missing_params', 'Test type, test ID, and profile ID are required', array('status' => 400));
    }

    $result = false;
    switch ($params['test_type']) {
        case 'selective':
            $result = save_selective_results($params);
            break;
        case 'extended':
            $result = save_extended_results($params);
            break;
        case 'alternative':
            $result = save_alternative_results($params);
            break;
        case 'divided':
            $result = save_divided_results($params);
            break;
        default:
            return new WP_Error('invalid_type', 'Invalid test type', array('status' => 400));
    }

    if ($result === false) {
        return new WP_Error('db_error', 'Failed to save test results', array('status' => 500));
    }

    return array(
        'success' => true,
        'message' => 'Test results saved successfully'
    );
}

function get_patient_details_endpoint($request) {
    $profile_id = $request['profile_id'];
    $details = get_patient_details($profile_id);

    if (!$details) {
        return new WP_Error('not_found', 'Patient details not found', array('status' => 404));
    }

    return $details;
}

function get_test_results_endpoint($request) {
    $profile_id = $request['profile_id'];
    $results = get_all_test_results($profile_id);

    return $results;
}
