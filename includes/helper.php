<?php
function sanitize($dirty, $type = 'string')
{
    $clean = trim($dirty);

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
            // only block HTML tags
            if (preg_match('/<[^>]*>/', $clean)) {
                throw new Exception("HTML tags are not allowed.");
            }
            break;
    }

    return $clean;
}
?>