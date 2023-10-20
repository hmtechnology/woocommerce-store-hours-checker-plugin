<?php

// Function to completely reset store hours
function reset_store_hours() {
    update_option('store_hours', array()); // Set store_hours to an empty array
}

// Function to completely closure dates
function reset_closure_dates() {
    update_option('closure_dates', array()); // Set closure_dates to an empty array
}

// Function to get if store is open or closed
function get_store_hours_status() {
    return is_store_open() ? 'Open' : 'Closed';
}

// Function to refresh the page after saving store hours 
function add_refresh_on_save() {
    if (isset($_POST['submit_store_hours'])) {
        echo '<script>window.location.reload();</script>';
    }
}

add_action('admin_notices', 'add_refresh_on_save');

// Function to check if the store is open
function is_store_open() {
    $current_day = strtolower(date('l'));
    $current_time = strtotime(date('H:i'));

    $store_hours = get_option('store_hours', array());
    $closure_dates = get_option('closure_dates', array());

    $current_date = date('Y-m-d');

    // Check if the current date is in the closure dates array
    if (in_array($current_date, $closure_dates)) {
        return false; // Store is closed on the current date
    }

    // If no specific store hours are defined for the current day, assume the store is open
    if (empty($store_hours) || empty($store_hours[$current_day])) {
        return true; // Store is considered open by default
    }

    // Check specific opening hours if defined for the current day
    foreach ($store_hours[$current_day] as $time_range) {
        if ($time_range['type'] === '24h') {
            return true; // Store is open 24 hours
        } elseif ($time_range['type'] === 'closed') {
            return false; // Store is closed
        } elseif ($time_range['type'] === 'open') {
            $open_time = strtotime($time_range['open']);
            $close_time = strtotime($time_range['close']);

            if ($current_time >= $open_time && $current_time <= $close_time) {
                return true; // Store is open during specified hours
            }
        }
    }

    // If no matching conditions are found, assume the store is closed
    return false;
}

// Enqueue necessary scripts and styles for the WordPress admin panel.
function enqueue_admin_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');
