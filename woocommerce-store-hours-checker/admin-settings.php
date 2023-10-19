<?php

// Add a button to reset store hours only on the 'store-hours-settings' page
function add_reset_store_hours_button() {
    global $pagenow; // Get the current page name

    // Check if you are on the 'store-hours-settings' page
    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'store-hours-settings') {
        echo '<form method="post">';
        echo '<input type="hidden" name="reset_store_hours" value="1">';
        echo '<input type="submit" class="button button-secondary" value="Reset Store Hours">';
        echo '</form>';

        if (isset($_POST['reset_store_hours'])) {
            reset_store_hours(); // Call the function to reset store hours
        }
    }
}

add_action('admin_notices', 'add_reset_store_hours_button');

// Add store hours status in admin notice
function add_store_hours_status() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_store-hours-settings') {
    echo '<div class="store-hours-status ' . (is_store_open() ? 'open' : 'closed') . '">';
    echo 'Store Status: ' . (is_store_open() ? 'OPEN' : 'CLOSED');
    echo '</div>';
    }
}

add_action('admin_notices', 'custom_store_hours_status_style');
add_action('admin_notices', 'add_store_hours_status');

// Add custom styles to store hours status
function custom_store_hours_status_style() {
    echo '<style>
        .store-hours-status {
            display: inline-block;
            padding: 10px 15px;
            font-size: 16px;
            margin: 10px 0;
        }

        .store-hours-status.open {
            background-color: #4CAF50;
            color: white;
        }

        .store-hours-status.closed {
            background-color: #F44336;
            color: white;
        }
    </style>';
}

// Define a function to add a menu and submenu in the administration panel
function custom_store_hours_menu() {
    add_menu_page('WooCoomerce Store Hours', 'WooCoomerce Store Hours', 'manage_options', 'store-hours-settings', 'store_hours_page');
    add_submenu_page('store-hours-settings', 'Store Hours Settings', 'Settings', 'manage_options', 'store-hours-settings-settings', 'store_hours_settings_page');
}
add_action('admin_menu', 'custom_store_hours_menu');

// Generate time options for a select input field
function generate_time_options($selected_time = '') {
    $options = '';
    for ($hour = 0; $hour < 24; $hour++) {
        for ($minute = 0; $minute < 60; $minute += 30) {
            $time = sprintf('%02d:%02d', $hour, $minute);
            $selected = ($time === $selected_time) ? 'selected' : '';
            $options .= '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
        }
    }
    return $options;
}

// Function to display the store hours configuration page
function store_hours_page() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_store_hours'])) {
        $store_hours = array();
        $days_of_week = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days_of_week as $day) {
            $store_hours[$day] = array();

            $selected_option = sanitize_text_field($_POST[$day . '_hours']);

            if ($selected_option === '24h') {
                $store_hours[$day][] = array('type' => '24h');
            } elseif ($selected_option === 'closed') {
                $store_hours[$day][] = array('type' => 'closed');
            } else {
                // Remove specific times if '24h' or 'closed' is selected.
                $store_hours[$day] = array();

                // Process specific hours only if 'specific' is selected
                $index = 0;
                while (isset($_POST[$day . '_open_' . $index])) {
                    $open = sanitize_text_field($_POST[$day . '_open_' . $index]);
                    $close = sanitize_text_field($_POST[$day . '_close_' . $index]);
                    if (!empty($open) && !empty($close)) {
                        $store_hours[$day][] = array('type' => 'open', 'open' => $open, 'close' => $close);
                    }
                    $index++;
                }
            }
        }

        update_option('store_hours', $store_hours);
    }

    if (isset($_POST['timezone'])) {
        update_option('store_timezone', $_POST['timezone']);
    }

    $store_hours = get_option('store_hours', array());
    $store_timezone = get_option('store_timezone', 'Europe/Rome');
    ?>


    <div class="wrap">
        <h2>Store Hours Settings</h2>
        <form method="post">
            <h3>Timezone Configuration</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Timezone</th>
                    <td>
                        <select name="timezone">
                            <?php
                            $timezones = timezone_identifiers_list();
                            foreach ($timezones as $tz) {
                                echo '<option value="' . $tz . '" ' . selected($store_timezone, $tz, false) . '>' . $tz . '</option>';
                            }
                            ?>
                        </select>
                        <p>Select the store timezone.</p>
                    </td>
                </tr>
            </table>
            <h3>Store Hours Configuration</h3>
            <table class="form-table">
                <?php
                $days_of_week = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days_of_week as $day) {
                    echo '<tr valign="top">
                            <th scope="row">' . ucfirst($day) . ' Hours</th>
                            <td>
                                <label><input type="radio" name="' . $day . '_hours" value="24h" ' . checked(isset($store_hours[$day][0]['type']) && $store_hours[$day][0]['type'] === '24h', true, false) . ' class="store-hours-radio"> Open 24h</label><br>
<label><input type="radio" name="' . $day . '_hours" value="closed" ' . checked(isset($store_hours[$day][0]['type']) && $store_hours[$day][0]['type'] === 'closed', true, false) . ' class="store-hours-radio"> Closed</label><br>
<label><input type="radio" name="' . $day . '_hours" value="specific" ' . checked(isset($store_hours[$day][0]['type']) && $store_hours[$day][0]['type'] === 'open', true, false) . ' class="store-hours-radio"> Specific Hours</label><br>';

                    $index = 0;
                if (!isset($store_hours[$day][$index]) || in_array($store_hours[$day][$index]['type'], ['24h', 'closed'])) {                        
					// Default opening and closing time
                        echo '<label for="' . $day . '_open_' . $index . '">Opening: </label><select name="' . $day . '_open_' . $index . '" class="specific-hours-select" disabled>' . generate_time_options() . '</select>';
                        echo '<label for="' . $day . '_close_' . $index . '">Closing: </label><select name="' . $day . '_close_' . $index . '" class="specific-hours-select" disabled>' . generate_time_options() . '</select><br>';
                    } else {
                        // Display existing opening and closing times
                        foreach ($store_hours[$day] as $time_slot) {
                            if ($time_slot['type'] === 'open') {
                                echo '<label for="' . $day . '_open_' . $index . '">Opening: </label><select name="' . $day . '_open_' . $index . '" class="specific-hours-select" disabled>' . generate_time_options($time_slot['open']) . '</select>';
                                echo '<label for="' . $day . '_close_' . $index . '">Closing: </label><select name="' . $day . '_close_' . $index . '" class="specific-hours-select" disabled>' . generate_time_options($time_slot['close']) . '</select>';
                                echo '<button type="button" class="delete-time-slot-button" data-day="' . $day . '" data-index="' . $index . '">Delete</button><br>';
								$index++;
                            }
                        }
                    }

                    // Add a button to add another time slot
                    echo '<button type="button" class="add-time-slot-button" data-day="' . $day . '">Add Another Opening Time</button>';
                    echo '</td></tr>';
                }
                ?>
            </table>
            <input type="hidden" name="submit_store_hours" value="1">
            <?php submit_button(); ?>
        </form>
    </div>

<script>
// Function to generate time options for a select input
function generate_time_options(selectedTime) {
    var options = '';
    for (var hour = 0; hour < 24; hour++) {
        for (var minute = 0; minute < 60; minute += 30) {
            var time = ('0' + hour).slice(-2) + ':' + ('0' + minute).slice(-2);
            var selected = selectedTime === time ? 'selected' : '';
            options += '<option value="' + time + '" ' + selected + '>' + time + '</option>';
        }
    }
    return options;
}

document.addEventListener('DOMContentLoaded', function () {
    // Function to toggle specific hours fields based on selected radio button
    function toggleSpecificHoursFields(day) {
        const specificHoursFields = document.querySelectorAll('[name^="' + day + '_open_"], [name^="' + day + '_close_"]');
        const specificHoursRadio = document.querySelector('[name="' + day + '_hours"][value="specific"]');

        specificHoursFields.forEach((field) => {
            field.disabled = !specificHoursRadio.checked;
        });
    }

    // Add a change listener to each radio button
    const storeHoursRadios = document.querySelectorAll('.store-hours-radio');

    storeHoursRadios.forEach((radio) => {
        const day = radio.name.split('_')[0];
        radio.addEventListener('change', () => {
            toggleSpecificHoursFields(day);
        });
    });

    // Initially, call the function to ensure fields are set correctly on page load
    storeHoursRadios.forEach((radio) => {
        const day = radio.name.split('_')[0];
        toggleSpecificHoursFields(day);
    });


// Add another time slot when the button is clicked
var addTimeSlotButtons = document.querySelectorAll('.add-time-slot-button');
addTimeSlotButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        var day = this.getAttribute('data-day');
        var timeSlotContainer = this.parentElement;
        var selectFields = timeSlotContainer.querySelectorAll('select');
        var index = selectFields.length / 2;

        // Limit the number of time slots to two
        if (index >= 2) {
            // Disable the "Add" button when the maximum limit is reached
            button.disabled = true;
            return;
        }

        // Create a new time slot container
        var timeSlot = document.createElement('div');

        // Create "Opening" label and select field
        var openingLabel = document.createElement('label');
        openingLabel.textContent = 'Opening: ';
        timeSlot.appendChild(openingLabel);

        var openSelect = document.createElement('select');
        openSelect.name = day + '_open_' + index;
        openSelect.innerHTML = generate_time_options();
        timeSlot.appendChild(openSelect);

        // Create "Closing" label and select field
        var closingLabel = document.createElement('label');
        closingLabel.textContent = 'Closing: ';
        timeSlot.appendChild(closingLabel);

        var closeSelect = document.createElement('select');
        closeSelect.name = day + '_close_' + index;
        closeSelect.innerHTML = generate_time_options();
        timeSlot.appendChild(closeSelect);

        // Add a "Remove" button
        var removeButton = document.createElement('button');
        removeButton.textContent = 'Remove';
        removeButton.type = 'button';
        removeButton.addEventListener('click', function () {
            timeSlotContainer.removeChild(timeSlot);

            // Re-enable the "Add" button after removing a slot
            button.disabled = false;
        });
        timeSlot.appendChild(removeButton);

        // Insert the new time slot container above the "Add" button
        timeSlotContainer.insertBefore(timeSlot, this);
    });
});



// Add a "Delete" button for each specific time interval
var deleteTimeSlotButtons = document.querySelectorAll('.delete-time-slot-button');
deleteTimeSlotButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        var day = this.getAttribute('data-day');
        var index = this.getAttribute('data-index');
        var timeSlotContainer = this.parentElement;

        // Check if the index is greater than zero before proceeding with removal
        if (index > 0) {
            // Remove the specific time interval and the related opening and closing fields
            timeSlotContainer.removeChild(this); // Delete button
            timeSlotContainer.removeChild(timeSlotContainer.querySelector('[name="' + day + '_open_' + index + '"]')); // Campo di apertura
            timeSlotContainer.removeChild(timeSlotContainer.querySelector('[name="' + day + '_close_' + index + '"]')); // Campo di chiusura
            timeSlotContainer.removeChild(timeSlotContainer.querySelector('label[for="' + day + '_open_' + index + '"]')); // Etichetta di apertura
            timeSlotContainer.removeChild(timeSlotContainer.querySelector('label[for="' + day + '_close_' + index + '"]')); // Etichetta di chiusura
        }

        // Re-enable the 'Add' button after the removal of an interval
        var addTimeSlotButton = timeSlotContainer.querySelector('.add-time-slot-button[data-day="' + day + '"]');
        if (addTimeSlotButton) {
            addTimeSlotButton.disabled = false;
        }
    });
});

// Hide the 'Delete' button for the first slot
var firstDeleteButton = document.querySelector('.delete-time-slot-button[data-index="0"]');
if (firstDeleteButton) {
    firstDeleteButton.style.display = 'none';
}

});
</script>

<?php
}

// Function to display the custom message settings page
function store_hours_settings_page() {
    $custom_error_message = get_option('custom_store_closed_message', 'The store is closed outside of the opening hours');
    ?>
    <div class="wrap">
        <h2>Store Hours Settings</h2>
        <h3>Custom Store Closed Message</h3>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Custom Error Message</th>
                    <td>
                        <input type="text" name="custom_store_closed_message" value="<?php echo esc_attr($custom_error_message); ?>" style="width: 350px;"/>
                        <p>Enter a custom error message to display when the store is closed.</p>
                    </td>
                </tr>
            </table>
			<h3>Custom CSS Styles</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Background Color</th>
					<td>
						<?php
						$background_color = esc_attr(get_option('custom_css_background_color', '#f7f7f7'));
						?>
						<input type="text" name="custom_css_background_color" value="<?php echo $background_color; ?>" class="color-field" />
						<p>Select the background color for the error message.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Border Color</th>
					<td>
						<?php
						$border_color = esc_attr(get_option('custom_css_border_color', '#ff3333'));
						?>
						<input type="text" name="custom_css_border_color" value="<?php echo $border_color; ?>" class="color-field" />
						<p>Select the border color for the error message.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Text Color</th>
					<td>
						<?php
						$text_color = esc_attr(get_option('custom_css_color_text', '#333333'));
						?>
						<input type="text" name="custom_css_color_text" value="<?php echo $text_color; ?>" class="color-field" />
						<p>Choose the text color for the error message.</p>
					</td>
				</tr>
			</table>
            <input type="hidden" name="submit_custom_message" value="1">
            <?php submit_button(); ?>
        </form>
    </div>

	<script>
		jQuery(document).ready(function($) {
			$('.color-field').wpColorPicker();
		});
	</script>
    <?php
}

// Update the custom message and error message background color only if they are not empty.
if (isset($_POST['submit_custom_message'])) {
    $custom_message = sanitize_text_field($_POST['custom_store_closed_message']);
    if (empty($custom_message)) {
        $custom_message = 'The store is closed outside of the opening hours.';
    }
    update_option('custom_store_closed_message', $custom_message);

    $background_color = sanitize_text_field($_POST['custom_css_background_color']);
    update_option('custom_css_background_color', $background_color);

    $border_color = sanitize_text_field($_POST['custom_css_border_color']);
    update_option('custom_css_border_color', $border_color);

    $text_color = sanitize_text_field($_POST['custom_css_color_text']);
    update_option('custom_css_color_text', $text_color);
}
