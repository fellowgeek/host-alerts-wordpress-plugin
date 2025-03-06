<?php

/*
Plugin Name:  Host Alerts
Plugin URI:   https://justshare.me
Description:  This plugin sends a Host Alerts webhook with the host code whenever someone logs into the WordPress admin dashboard.
Version:      1.0
Author:       Erfan Reed
Author URI:   https://justshare.me
License:      MIT
License URI:  https://github.com/fellowgeek/host-alerts-wordpress-plugin/blob/main/LICENSE
Text Domain:  host-alerts
Domain Path:  /languages
*/


// 1. Add settings page to the WordPress admin
add_action('admin_menu', 'host_alerts_webhook_menu');

function host_alerts_webhook_menu() {
    add_options_page(
        'Host Alerts Settings',
        'Host Alerts Webhook',
        'manage_options', // Capability required to access the settings
        'host-alerts-webhook', // Unique slug for the settings page
        'host_alerts_webhook_settings_page'
    );
}

function host_alerts_webhook_settings_page() {
    ?>
    <div class="wrap">
        <h1>Host Alerts Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('host_alerts_webhook_settings'); // Settings group name
            do_settings_sections('host-alerts-webhook'); // Settings page slug
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 2. Register the setting
add_action('admin_init', 'host_alerts_webhook_settings_init');

function host_alerts_webhook_settings_init() {
    register_setting(
        'host_alerts_webhook_settings', // Settings group name
        'host_alerts_webhook_code', // Setting name (for the code)
        'sanitize_text_field' // Sanitize the input
    );

    add_settings_section(
        'host_alerts_webhook_section', // Section ID
        'Host Code', // Section title
        'host_alerts_webhook_section_callback', // Callback function to display section description (optional)
        'host-alerts-webhook' // Settings page slug
    );

    add_settings_field(
        'host_alerts_webhook_code', // Field ID
        'Code', // Field title
        'host_alerts_webhook_code_callback', // Callback function to display the field
        'host-alerts-webhook', // Settings page slug
        'host_alerts_webhook_section' // Section ID
    );
}

function host_alerts_webhook_section_callback() {
    echo 'Enter the host code provided by the Host Alerts app.';
}

function host_alerts_webhook_code_callback() {
    $code = strtoupper(get_option('host_alerts_webhook_code'));
    echo '<input type="text" name="host_alerts_webhook_code" maxlength="8" value="' . esc_attr($code) . '" class="regular-text" />';
}


/**
 * Function to send a webhook on admin login.
 *
 * @param string $user_login The username of the logged-in user.
 * @param WP_User $user The WP_User object of the logged-in user.
 */
function send_host_alerts_webhook( $user_login, $user ) {

    $code = strtoupper(get_option('host_alerts_webhook_code'));
    if (empty($code)) {
        return;
    }

    $ip = '';
	if (isset($_SERVER['REMOTE_ADDR']) == true) {
		$ip = '&ip=' . sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
	}

    // 1. Define your webhook URL:
    $webhook_url = 'https://justshare.me/trigger/alert/?code=' . $code . '&user='. $user_login . $ip; // Replace with your actual URL

    // 4. Send the webhook using wp_remote_post():
    wp_remote_get($webhook_url);
}
add_action( 'wp_login', 'send_host_alerts_webhook', 10, 2 ); // Priority 10, 2 arguments
