<?php
/*
Plugin Name: Floating button for VANNi
Description: Display a floating button with "Get a Quote" on all pages except the homepage.
Version: 1.2
Author: Chavindu Nuwanpriya
*/

// Enqueue necessary styles and scripts
function floating_button_styles() {
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');

    // Plugin styles
    wp_enqueue_style('floating-button-styles', plugin_dir_url(__FILE__) . 'css/styles.css');
}

add_action('wp_enqueue_scripts', 'floating_button_styles');

// Add the floating button to all pages except the homepage
function add_floating_button() {
    $options = get_option('floating_button_settings');

    if (!is_front_page()) {
        ?>
        <div class="floating-button" style="bottom: <?php echo esc_attr($options['button_position_bottom']); ?>px; right: <?php echo esc_attr($options['button_position_right']); ?>px;">
            <a href="<?php echo esc_url($options['button_hyperlink']); ?>" class="quote-button"
               style="background-color: <?php echo esc_attr($options['button_color']); ?>; color: <?php echo esc_attr($options['text_color']); ?>">
                <i class="fas fa-file-invoice-dollar"></i> Get a Quote
            </a>
        </div>
        <?php
    }
}

add_action('wp_footer', 'add_floating_button');

// Add settings link on the plugin page
function floating_button_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=floating_button_settings">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'floating_button_add_settings_link');

// Add a top-level menu for Floating Button Settings
function floating_button_add_menu() {
    // Check if 'bitlab' exists
    $bitlab_menu_exists = false;

    global $menu;
    foreach ($menu as $item) {
        if ($item[2] === 'bitlab') {
            $bitlab_menu_exists = true;
            break;
        }
    }

    // Use 'floating_button_settings' as a unique menu slug
    $parent_slug = $bitlab_menu_exists ? 'bitlab' : 'options-general.php';

    add_submenu_page(
        $parent_slug,
        'Floating Button Settings',
        'Floating Button',
        'manage_options',
        'floating_button_settings',
        'render_floating_button_settings'
    );
}

add_action('admin_menu', 'floating_button_add_menu');

// Render BitLab menu
function render_bitlab_menu() {
    ?>
    <div class="wrap">
        <h1>BitLab Dashboard</h1>
        <p>Welcome to the BitLab Dashboard. Explore the available options.</p>
    </div>
    <?php
}

// Render settings page
function render_floating_button_settings() {
    // Render the 'Floating Button Settings' page
    ?>
    <div class="wrap">
        <h1>Floating Button Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('floating_button_settings_group');
            do_settings_sections('floating_button_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function floating_button_register_settings() {
    register_setting(
        'floating_button_settings_group',
        'floating_button_settings',
        'sanitize_floating_button_settings'
    );

    add_settings_section(
        'floating_button_general_section',
        'General Settings',
        'floating_button_general_section_callback',
        'floating_button_settings'
    );

    add_settings_field(
        'button_hyperlink',
        'Button Hyperlink',
        'button_hyperlink_callback',
        'floating_button_settings',
        'floating_button_general_section'
    );

    add_settings_field(
        'button_colors',
        'Button Colors',
        'button_colors_callback',
        'floating_button_settings',
        'floating_button_general_section'
    );

    add_settings_field(
        'button_position',
        'Button Position',
        'button_position_callback',
        'floating_button_settings',
        'floating_button_general_section'
    );
}

add_action('admin_init', 'floating_button_register_settings');

// Sanitize settings
function sanitize_floating_button_settings($input) {
    $input['button_hyperlink'] = esc_url_raw($input['button_hyperlink']);
    $input['button_color'] = sanitize_hex_color($input['button_color']);
    $input['button_hover_color'] = sanitize_hex_color($input['button_hover_color']);
    $input['text_color'] = sanitize_hex_color($input['text_color']);
    $input['button_position_bottom'] = absint($input['button_position_bottom']);
    $input['button_position_right'] = absint($input['button_position_right']);

    return $input;
}

// Section callback
function floating_button_general_section_callback() {
    echo '<p>Configure general settings for the floating button.</p>';
}

// Hyperlink field callback with dropdown of pages
function button_hyperlink_callback() {
    $options = get_option('floating_button_settings');
    $pages = get_pages();
    ?>
    <select name="floating_button_settings[button_hyperlink]">
        <option value="">Select a Page</option>
        <?php
        foreach ($pages as $page) {
            $selected = selected($options['button_hyperlink'], get_page_link($page->ID), false);
            echo "<option value='" . esc_url(get_page_link($page->ID)) . "' $selected>" . esc_html($page->post_title) . "</option>";
        }
        ?>
    </select>
    <?php
}

// Colors field callback with color pickers
function button_colors_callback() {
    $options = get_option('floating_button_settings');
    ?>
    <label for="button_color">Active Background Color:</label>
    <input type="text" name="floating_button_settings[button_color]" class="color-picker" value="<?php echo esc_attr($options['button_color']); ?>" />

    <label for="button_hover_color">Hover Background Color:</label>
    <input type="text" name="floating_button_settings[button_hover_color]" class="color-picker" value="<?php echo esc_attr($options['button_hover_color']); ?>" />

    <label for="text_color">Text Color:</label>
    <input type="text" name="floating_button_settings[text_color]" class="color-picker" value="<?php echo esc_attr($options['text_color']); ?>" />
    <?php
}

// Position field callback with two fields
function button_position_callback() {
    $options = get_option('floating_button_settings');
    ?>
    <label for="button_position_bottom">Bottom (px):</label>
    <input type="number" name="floating_button_settings[button_position_bottom]" value="<?php echo esc_attr($options['button_position_bottom']); ?>" />

    <label for="button_position_right">Right (px):</label>
    <input type="number" name="floating_button_settings[button_position_right]" value="<?php echo esc_attr($options['button_position_right']); ?>" />
    <?php
}

// Enqueue color picker script
function floating_button_color_picker_script() {
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');

    // Add the script to initialize the color picker
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.color-picker').wpColorPicker();
        });

        $(document).on('widget-added widget-updated', function(){
            $('.color-picker').wpColorPicker();
        });
    </script>
    <?php
}

add_action('admin_enqueue_scripts', 'floating_button_color_picker_script');


