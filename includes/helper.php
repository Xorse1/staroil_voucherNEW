<?php
function sanitize($dirty, $type = 'string') {
    $clean = trim($dirty); // Remove surrounding whitespace

    // Optional: strip HTML tags except safe ones
    // Uncomment below if you want to allow specific tags only
    // $clean = strip_tags($clean, '<p><a><br><b><i><ul><li>');

    // Type-specific validation
    switch ($type) {
        case 'email':
            if (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address.");
            }
            break;
        case 'int':
            if (!filter_var($clean, FILTER_VALIDATE_INT)) {
                throw new Exception("Invalid integer.");
            }
            break;
        case 'url':
            if (!filter_var($clean, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid URL.");
            }
            break;
        case 'alphanum':
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $clean)) {
                throw new Exception("Only letters and numbers allowed.");
            }
            break;
        case 'string':
        default:
            // Basic sanitization for strings
            if (preg_match('/[<>\'\"`]/', $clean)) {
                throw new Exception("Suspicious characters detected.");
            }
    }

    // Final HTML encoding to prevent XSS in output
    return htmlentities($clean, ENT_QUOTES, "UTF-8");
}

?>