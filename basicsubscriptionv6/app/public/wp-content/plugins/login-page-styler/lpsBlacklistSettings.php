<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display the IP blocking settings page
function lps_display_ip_blocking() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lps_ip_blocking';

	// List of countries/regions.
	$regions = array(
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BR' => 'Brazil',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'CV' => 'Cabo Verde',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'SZ' => 'Eswatini',
		'ET' => 'Ethiopia',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GR' => 'Greece',
		'GD' => 'Grenada',
		'GT' => 'Guatemala',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HN' => 'Honduras',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KP' => 'Korea (North)',
		'KR' => 'Korea (South)',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestine',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'QA' => 'Qatar',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States of America',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);

	// Handle form submissions.
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		// Check nonce for security.
		if ( ! isset( $_POST['lps_nonce_blacklist'] ) || ! wp_verify_nonce( $_POST['lps_nonce_blacklist'], 'lpsblacklist_nonce_action' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Nonce verification failed. Please try again.', 'login-page-styler' ) . '</p></div>';
			return; // Exit early if nonce verification fails.
		}

		// Add IP address.
		if ( ! empty( $_POST['add_ip'] ) && ! empty( $_POST['ip_address'] ) ) {
			// Unsplash before sanitization.
			$ip_address = wp_unslash( $_POST['ip_address'] );
			$ip_address = sanitize_text_field( $ip_address );

		$wpdb->insert(
			$table_name,
			array(
				'ip_address' => $ip_address,
				'region'     => null,
			)
		);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'IP Address added successfully!', 'login-page-styler' ) . '</p></div>';
		}

		// Add region.
		if ( ! empty( $_POST['add_region'] ) && ! empty( $_POST['region'] ) ) {
			// Unsplash before sanitization.
			$region = wp_unslash( $_POST['region'] );
			$region = sanitize_text_field( $region );

			$wpdb->insert(
				$table_name,
				array(
					'region'     => $region,
					'ip_address' => null,
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Region added successfully!', 'login-page-styler' ) . '</p></div>';
		}

		// Unblock IP address.
		if ( ! empty( $_POST['unblock_ip'] ) && ! empty( $_POST['ip_id'] ) ) {
			$ip_id = intval( $_POST['ip_id'] );
			$wpdb->delete( $table_name, array( 'id' => $ip_id ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'IP Address unblocked successfully!', 'login-page-styler' ) . '</p></div>';
		}

		// Unblock region.
		if ( ! empty( $_POST['unblock_region'] ) && ! empty( $_POST['region_id'] ) ) {
			$region_id = intval( $_POST['region_id'] );
			$wpdb->delete( $table_name, array( 'id' => $region_id ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Region unblocked successfully!', 'login-page-styler' ) . '</p></div>';
		}
	}

	// Add styles for the page
	echo '<style>
/* General styling for the blocking section */
.block-ip-form, .block-region-form, .blocked-ips, .blocked-regions {
    background-color: #f9f9f9; /* Light background for sections */
    border: 1px solid #ddd; /* Light border for separation */
    border-radius: 5px; /* Rounded corners */
    padding: 15px; /* Padding for spacing */
    margin-bottom: 20px; /* Space between sections */
}

/* Heading styles */
.block-ip-form h2, .block-region-form h2, .blocked-ips h3, .blocked-regions h3 {
    color: #333; /* Darker text color */
    font-size: 1.5em; /* Larger font size for headings */
    margin-bottom: 10px; /* Space below headings */
}

/* Input field styling */
input[type="text"], select {
    width: 100%; /* Full width for inputs */
    padding: 10px; /* Padding inside inputs */
    border: 1px solid #ccc; /* Border for inputs */
    border-radius: 4px; /* Rounded corners */
    margin-bottom: 10px; /* Space below inputs */
}

/* Button styling */
.button {
    background-color: #0073aa; /* Primary button color */
    color: #fff; /* White text color */
    border: none; /* No border */
    padding: 10px 15px; /* Padding for buttons */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
}

.button-primary {
    background-color: #0073aa; /* Blue color for primary buttons */
}

.button-secondary {
    background-color: #ccc; /* Grey color for secondary buttons */
}

/* List styles */
ul {
    list-style-type: none; /* Remove default list styles */
    padding: 0; /* Remove default padding */
}

li {
    margin-bottom: 10px; /* Space between list items */
}

/* Message styles */
p {
    color: #666; /* Grey color for messages */
    font-style: italic; /* Italic for messages */
}

</style>';

	// Form for adding IP address
	echo '<div class="block-ip-form">';
	echo '<h2>Block IP Addresses</h2>';
	echo '<p>Block IP  from accessing login page . Add 1 ip at a time and hit block ip</p>';
	echo '<form method="post" action="">';
	echo '<input type="text" name="ip_address" placeholder="Enter IP Address" required />';
	echo '<input type="hidden" name="lps_nonce_blacklist" value="' . esc_attr( wp_create_nonce( 'lpsblacklist_nonce_action' ) ) . '">';
	echo '<input type="submit" name="add_ip" value="Block IP" class="button button-primary" />';
	echo '</form>';
	echo '</div>'; // Closing div for block-ip-form.

	// Form for adding region using a dropdown.
	echo '<div class="block-region-form">';
	echo '<h2>Block Regions</h2>';
	echo '<p>Block Countries from accessing login page </p>';
	echo '<form method="post" action="">';
	echo '<select name="region" required>';
	echo '<option value="">Select Region</option>';
	foreach ( $regions as $code => $name ) {
		echo '<option value="' . esc_attr( $code ) . '">' . esc_html( $name ) . '</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="lps_nonce_blacklist" value="' . esc_attr( wp_create_nonce( 'lpsblacklist_nonce_action' ) ) . '">';
	echo '<input type="submit" name="add_region" value="Block Region" class="button button-primary" />';
	echo '</form>';
	echo '</div>'; // Closing div for block-region-form.

	// Display blocked IPs
	echo '<div class="blocked-ips">';
	echo '<h3>Blocked IPs</h3>';
	$blocked_ips = $wpdb->get_results( "SELECT * FROM $table_name WHERE ip_address IS NOT NULL" );
	if ( $blocked_ips ) {
		echo '<ul>';
		foreach ( $blocked_ips as $ip ) {
			echo '<li>' . esc_html( $ip->ip_address ) . ' ';
			echo '<form method="post" style="display:inline;">
                <input type="hidden" name="ip_id" value="' . esc_attr( $ip->id ) . '">
				<input type="hidden" name="lps_nonce_blacklist" value="' . esc_attr( wp_create_nonce( 'lpsblacklist_nonce_action' ) ) . '">
                <input type="submit" name="unblock_ip" value="Unblock" class="button button-secondary" />
            </form></li>';
		}
		echo '</ul>';
	} else {
		echo '<p>No IP addresses blocked yet.</p>';
	}
	echo '</div>'; // Closing div for blocked-ips.

	// Display blocked regions.
	echo '<div class="blocked-regions">';
	echo '<h3>Blocked Regions</h3>';
	$blocked_regions = $wpdb->get_results( "SELECT * FROM $table_name WHERE region IS NOT NULL" );
	if ( $blocked_regions ) {
		echo '<ul>';
		foreach ( $blocked_regions as $region ) {
			echo '<li>' . esc_html( $region->region ) . ' ';
			echo '<form method="post" style="display:inline;">
                <input type="hidden" name="region_id" value="' . esc_attr( $region->id ) . '">
				<input type="hidden" name="lps_nonce_blacklist" value="' . esc_attr( wp_create_nonce( 'lpsblacklist_nonce_action' ) ) . '">
                <input type="submit" name="unblock_region" value="Unblock" class="button button-secondary" />
            </form></li>';
		}
		echo '</ul>';
	} else {
		echo '<p>No regions blocked yet.</p>';
	}
	echo '</div>'; // Closing div for blocked-regions.
}
