<?php

function sanitizePassword($password) {
    // Trim unnecessary spaces from the password
    $password = trim($password);

    // Check for minimum length
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }

    // Check for maximum length (optional)
    if (strlen($password) > 20) {
        return 'Password must not be longer than 20 characters.';
    }

    // Password strength check using regular expressions
    $uppercase = preg_match('/[A-Z]/', $password);
    $lowercase = preg_match('/[a-z]/', $password);
    $number = preg_match('/[0-9]/', $password);
    $specialChar = preg_match('/[\W_]/', $password);  // Non-alphanumeric character

    if (!$uppercase || !$lowercase || !$number || !$specialChar) {
        return 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }

    // If password passes all checks, return the sanitized password
    return $password;
}

?>
