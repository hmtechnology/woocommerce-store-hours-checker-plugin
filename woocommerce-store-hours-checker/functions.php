<?php

// Function to completely reset store hours
function reset_store_hours() {
    update_option('store_hours', array()); // Set store_hours to an empty array
}

// Function to check if the store is open
function is_store_open() {
    $current_day = strtolower(date('l'));
    $current_time = date('H:i');

    $store_hours = get_option('store_hours', array());

	if (empty($store_hours)) {
		return true; // The store is considered open if there are no configured hours.
	}
	
    if (isset($store_hours[$current_day])) {
        foreach ($store_hours[$current_day] as $time_range) {
            if ($time_range['type'] === '24h') {
                return true; // Store is open 24 hours
            } elseif ($time_range['type'] === 'closed') {
                return false; // Store is closed
            } elseif ($time_range['type'] === 'open') {
                $open_time = strtotime($time_range['open']);
                $close_time = strtotime($time_range['close']);
                $current_time = strtotime($current_time);

                if ($current_time >= $open_time && $current_time <= $close_time) {
                    return true;
                }
            }
        }
    }

    return false;
}


// Enqueue necessary scripts and styles for the WordPress admin panel.
function enqueue_admin_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');
