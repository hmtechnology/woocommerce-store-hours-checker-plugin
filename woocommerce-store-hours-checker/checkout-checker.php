<?php

// Add style to error message
function add_custom_checkout_error_style() {
    $background_color = get_option('custom_css_background_color', '');
    $border_color = get_option('custom_css_border_color', '');
    $text_color = get_option('custom_css_color_text', '');
	
    echo '<style>
        .woocommerce-notices-wrapper {
            background-color: ' . esc_attr($border_color) . ';
            padding: 10px;
            border: 1px solid ' . esc_attr($border_color) . ';
            border-radius: 4px;
            margin: 20px 0;
        }

        .woocommerce-notices-wrapper .woocommerce-error li {
            color: ' . esc_attr($text_color) . ';
            list-style-type: none;
        }

        .woocommerce-notices-wrapper .woocommerce-error {
            background: ' . esc_attr($background_color) . ';
            border-top: 0 !important;
        }
		
	.woocommerce-notices-wrapper .woocommerce-error li {
            list-style-type: none;
        }

        .woocommerce-notices-wrapper .woocommerce-error li::before {
            content: "\274C ";
            font-size: 16px;
            margin-right: 5px;
        }

        .woocommerce-notices-wrapper .woocommerce-error[role="alert"]::before {
            content: none !important;
        }
    </style>';
}

// Add modal popup 
function add_custom_checkout_popup() {
    $custom_error_message = get_option('custom_store_closed_message', 'The store is closed outside of the opening hours.');
    if (empty($custom_error_message)) {
        $custom_error_message = 'The store is closed outside of the opening hours.';
    }
    $background_color = get_option('custom_css_background_color', '');
    $border_color = get_option('custom_css_border_color', '');
    $text_color = get_option('custom_css_color_text', '');
?>
    <div id="custom-checkout-popup" class="custom-checkout-popup">
        <div class="popup-content">
            <p><?php echo esc_html($custom_error_message); ?></p>
            <button id="close-popup">OK</button>
        </div>
    </div>
    <style>
        .custom-checkout-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            text-align: center;
            padding: 40px;
    	    background-color: <?php echo esc_attr($background_color); ?>;
	    border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            border: 25px solid <?php echo esc_attr($border_color); ?>;
        }

        .popup-content p {
    	    color: <?php echo esc_attr($text_color); ?>;
	    font-size: 1.2em;
        }

        .popup-content p:before {
            content: "\274C ";
            font-size: 20px;
            margin-right: 5px;
        }
		
	#close-popup {
            background-color: #111;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            if ($('#custom-checkout-popup').length) {
                $('#custom-checkout-popup').fadeIn();
            }

            $('#close-popup').on('click', function() {
                $('#custom-checkout-popup').fadeOut();
            });

            $('.custom-checkout-popup').on('click', function(e) {
                if (e.target === this) {
                    $('#custom-checkout-popup').fadeOut();
                }
            });
        });
    </script>
    <?php
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
        add_action('wp_footer', 'add_custom_checkout_popup');
		remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        add_action('wp_head', 'add_custom_checkout_error_style');
    }
}

add_action('wp', 'disable_checkout_outside_store_hours');
