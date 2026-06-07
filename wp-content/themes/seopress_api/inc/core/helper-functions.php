<?php
// Step 1: Retrieve data from GeoNames API and save it to options
function fetch_and_save_geonames_data() {
    $response = wp_remote_get('https://api.geonames.org/postalCodeSearchJSON?placename=Wien&country=AT&maxRows=100&username=rocco_007&lang=de');

    if (!is_wp_error($response)) {
        $data = wp_remote_retrieve_body($response);
        update_option('geonames_data', $data);
    }
}
// Hook the function to an appropriate action, like admin_init
add_action('admin_init', 'fetch_and_save_geonames_data');

// Step 2: Create options page
function geonames_options_page() {
    add_options_page('GeoNames Settings', 'GeoNames Settings', 'manage_options', 'geonames-settings', 'geonames_options_page_content');
}
add_action('admin_menu', 'geonames_options_page');

// Step 3: Display data on options page
function geonames_options_page_content() {
    $geonames_data = get_option('geonames_data');
    if ($geonames_data) {
        $geonames_data = json_decode($geonames_data);
        ?>
        <div class="wrap">
            <h2>GeoNames Data</h2>
            <p>This is the data retrieved from GeoNames API:</p>
            <pre><?php print_r($geonames_data); ?></pre>
        </div>
        <?php
    } else {
        echo "<p>No GeoNames data available.</p>";
    }
}
