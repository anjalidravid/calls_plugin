<?php
/*
Plugin Name: Calls Plugin
Description: A plugin to manage Calls custom post type with custom meta fields and an admin menu page.
Version: 1.0
Author: Your Name
*/

// Hook for plugin activation to create custom post type and meta fields
register_activation_hook(__FILE__, 'calls_plugin_activate');

function calls_plugin_activate() {
    create_calls_post_type();
    flush_rewrite_rules(); // To avoid 404 errors
}

// Custom Post Type 'Calls'
function create_calls_post_type() {
    register_post_type('calls',
        array(
            'labels' => array(
                'name' => __('Calls'),
                'singular_name' => __('Call')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'show_in_menu' => true,
        )
    );
}
add_action('init', 'create_calls_post_type');

// Custom Meta Fields for 'Calls'
function add_calls_meta_boxes() {
    add_meta_box(
        'calls_meta_box', // ID
        'Call Details',   // Title
        'render_calls_meta_box', // Callback
        'calls',          // Post type
        'normal',         // Context
        'high'            // Priority
    );
}

add_action('add_meta_boxes', 'add_calls_meta_boxes');

function render_calls_meta_box($post) {
    // Retrieve the current values
    $segment = get_post_meta($post->ID, '_select_segment', true);
    $stock = get_post_meta($post->ID, '_select_stock', true);
    ?>
    <label for="select_segment">Select Segment</label>
    <select name="select_segment" id="select_segment">
        <option value="Equity" <?php selected($segment, 'Equity'); ?>>Equity</option>
        <option value="Commodity" <?php selected($segment, 'Commodity'); ?>>Commodity</option>
    </select>

    <br><br>

    <label for="select_stock">Select Stock</label>
    <select name="select_stock" id="select_stock">
        <option value="equity1" <?php selected($stock, 'equity1'); ?>>Equity 1</option>
        <option value="equity2" <?php selected($stock, 'equity2'); ?>>Equity 2</option>
        <option value="commodity1" <?php selected($stock, 'commodity1'); ?>>Commodity 1</option>
        <option value="commodity2" <?php selected($stock, 'commodity2'); ?>>Commodity 2</option>
    </select>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var segmentSelect = document.getElementById('select_segment');
            var stockSelect = document.getElementById('select_stock');
            function updateStockOptions() {
                var equityOptions = ['equity1', 'equity2'];
                var commodityOptions = ['commodity1', 'commodity2'];
                stockSelect.innerHTML = '';
                var options = segmentSelect.value === 'Equity' ? equityOptions : commodityOptions;
                options.forEach(function(option) {
                    var opt = document.createElement('option');
                    opt.value = option;
                    opt.text = option.charAt(0).toUpperCase() + option.slice(1);
                    stockSelect.appendChild(opt);
                });
            }
            updateStockOptions();
            segmentSelect.addEventListener('change', updateStockOptions);
        });
    </script>
    <?php
}

function save_calls_meta($post_id) {
    if (isset($_POST['select_segment'])) {
        update_post_meta($post_id, '_select_segment', sanitize_text_field($_POST['select_segment']));
    }
    if (isset($_POST['select_stock'])) {
        update_post_meta($post_id, '_select_stock', sanitize_text_field($_POST['select_stock']));
    }
}
add_action('save_post', 'save_calls_meta');

// Custom Admin Menu Page
function add_calls_admin_menu() {
    add_menu_page(
        'Calls',          // Page title
        'Calls',          // Menu title
        'manage_options', // Capability
        'calls-list',     // Menu slug
        'render_calls_admin_page' // Callback function
    );
}
add_action('admin_menu', 'add_calls_admin_menu');

function render_calls_admin_page() {
    $args = array(
        'post_type' => 'calls',
        'posts_per_page' => -1
    );
    $calls = new WP_Query($args);
    echo '<h2>Calls List</h2>';
    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>Title</th><th>Segment</th><th>Stock</th></tr></thead>';
    echo '<tbody>';
    if ($calls->have_posts()) {
        while ($calls->have_posts()) {
            $calls->the_post();
            $segment = get_post_meta(get_the_ID(), '_select_segment', true);
            $stock = get_post_meta(get_the_ID(), '_select_stock', true);
            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . esc_html($segment) . '</td>';
            echo '<td>' . esc_html($stock) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No Calls found.</td></tr>';
    }
    echo '</tbody></table>';
}

