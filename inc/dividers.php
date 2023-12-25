<?php
add_filter('breakdance_shape_dividers', function ($dividers) {
    // Define the directory where the SVG dividers are stored
    $dividers_dir = THEME_DIR . "/assets/dividers";

    // Check if the directory exists
    if (is_dir($dividers_dir)) {
        // Get all svg files from the directory
        $divider_files = glob($dividers_dir . "/*.svg");

        // Loop through each file and add its content to the dividers array
        foreach ($divider_files as $file) {
            // Get the file content
            $svg_content = file_get_contents($file);

            // Prepare the divider name: use file name, replace hyphens and underscores with spaces
            $divider_name = basename($file, '.svg'); // Remove the '.svg' extension
            $divider_name = str_replace(array('-', '_'), ' ', $divider_name); // Replace '-' and '_' with spaces

            // Create the divider array
            $divider = [
                'text' => ucwords($divider_name), // Capitalize each word for better display
                'value' => $svg_content
            ];

            // Add the divider to the dividers array
            $dividers[] = $divider;
        }
    }

    // Return the modified dividers array
    return $dividers;
});

?>