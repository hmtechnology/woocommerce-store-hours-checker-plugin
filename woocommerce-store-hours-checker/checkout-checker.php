<?php

// Add style to error message
function add_custom_checkout_error_style() {
    $background_color = get_option('custom_css_background_color', '');
    $border_color = get_option('custom_css_border_color', '');

    echo '<style>
        .woocommerce .woocommerce-notices-wrapper {
            background-color: ' . esc_attr($background_color) . ';
            padding: 10px;
            border: 1px solid ' . esc_attr($border_color) . ';
            border-radius: 4px;
            margin: 20px 0;
        }

        .woocommerce .woocommerce-notices-wrapper .woocommerce-error li {
            list-style-type: none;
        }

        .woocommerce .woocommerce-notices-wrapper .woocommerce-error li::before {
            content: "\274C ";
            font-size: 16px;
            margin-right: 5px;
        }

        .woocommerce .woocommerce-error[role="alert"]::before {
            content: none !important;
        }

        .woocommerce .woocommerce-error {
            border-top: 0 !important;
        }
    </style>';
}

// Disable checkout when store is closed
function disable_checkout_outside_store_hours() {
    if (!is_cart() && !is_checkout()) return;

    if (!is_store_open()) {
        $custom_error_message = get_option('custom_store_closed_message', 'The store is closed outside of the opening hours.');
        if (empty($custom_error_message)) {
            $custom_error_message = 'The store is closed outside of the opening hours.';
        }
		wc_add_notice($custom_error_message, 'error');
		remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        add_action('wp_head', 'add_custom_checkout_error_style');
    }
}

add_action('wp', 'disable_checkout_outside_store_hours');