<?php
/**
 * Plugin Name: WhatsHelp Chat Button
 * Description: The Chat button by WhatsHelp takes website visitor directly to the messaging app such as Facebook Messenger or WhatsApp and allows them to initiate a conversation with you. After that, both you and your customer can follow up the conversation anytime and anywhere!
 * Version: 1.1.0
 * Author: Whatshelp
 * Author URI: https://whatshelp.io
 */

function whatshelp_setup()
{
    //add_submenu_page('options-general.php', __('Whatshelp Chat Button', 'whatshelp'), __('Whatshelp Chat Button', 'whatshelp'), 'manage_options', 'options-whatshelp', 'whatshelp_settings');
    add_menu_page(__('WhatsHelp', 'whatshelp'), __('WhatsHelp', 'whatshelp'), 8, basename(__FILE__), 'whatshelp_settings', plugin_dir_url(__FILE__) . 'img/wh-icon.ico');
    register_setting('whatshelp', 'whatshelp-code');
}

// Display settings page
function whatshelp_settings()
{
    $safeLogoURL = htmlspecialchars(get_whatshelp_logo_url());
    $safeSiteURL = htmlspecialchars(get_whatshelp_url());

    echo <<<EOTEXT
     <a href="{$safeSiteURL}" target="_blank">
     <img src="{$safeLogoURL}" style="max-width: 250px;"></a>
EOTEXT;

    if (get_option('whatshelp-code')) {
        echo <<<EOTEXT
	<p style="font-size: 14px;">
		Check your <a href="/" target="_blank">website</a> to see if the Chat Button is present. 
		<br>
		You can always get a new code at <a href="{$safeSiteURL}" target="_blank">www.whatshelp.io/widget</a> and paste it in the form below.
	</p>
EOTEXT;
    } else {
        echo <<<EOTEXT
	<h3>Step 1: Get button code</h3>
	<p style="font-size: 14px;">
		To install Chat Button, please go to  <strong><a href="{$safeSiteURL}" target="_blank">www.whatshelp.io/widget</a></strong> and get the button code.
	</p>
	<h3>Step 2: Paste the code</h3>
	<p style="font-size: 14px;">Copy and paste button code into the form below:</p>
EOTEXT;
    }

    echo '<form action="options.php" method="POST">';
    settings_fields('whatshelp');
    do_settings_sections('whatshelp');
    echo '<textarea cols="80" rows="14" name="whatshelp-code">' . esc_attr(get_option('whatshelp-code')) . '</textarea>';
    submit_button();
    echo '</form>';
}

function get_whatshelp_url()
{
    return 'https://whatshelp.io/widget/?utm_campaign=wordpress_plugin&utm_medium=widget&utm_source=wordpress';
}

function get_whatshelp_logo_url()
{
    return plugin_dir_url(__FILE__) . 'img/whatshelp_logo.png';
}

function add_whatshelp_code()
{
    echo get_option('whatshelp-code');
}

// Add settings page and register settings with WordPress
add_action('admin_menu', 'whatshelp_setup');
// Add the code to footer
add_action('wp_footer', 'add_whatshelp_code');
