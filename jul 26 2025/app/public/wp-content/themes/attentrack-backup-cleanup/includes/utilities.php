<?php
/**
 * Common utility functions for AttenTrack
 */

// Initialize logging
function init_attentrack_logging() {
    $log_dir = WP_CONTENT_DIR . '/attentrack_logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
}

/**
 * Write to log file
 * 
 * @param mixed $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function write_log($message, $level = 'info') {
    $log_file = WP_CONTENT_DIR . '/attentrack_logs/attentrack.log';
    $timestamp = current_time('Y-m-d H:i:s');
    $formatted_message = "[{$timestamp}] [{$level}] {$message}\n";
    error_log($formatted_message, 3, $log_file);
}

// Initialize logging on load
init_attentrack_logging();
