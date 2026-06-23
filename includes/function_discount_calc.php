<?php
if(!function_exists('calculateDiscount')) {
function calculateDiscount11($amount) { //Not in use

    // For 10–50 amounts → 50% discount
    if (in_array($amount, [10, 20, 30, 40, 50, 100])) { 
        
        $discount_by = 0.00; // 0.50p
        $discount_value = 0.00;
        $discounted_amount = $amount - $discount_by;

    }
    // For 100–1000 amounts → 1% discount
    elseif (in_array($amount, [200, 500, 1000])) {

        $discount_by = 0.01;
        $discount_value = $amount * $discount_by;
        $discounted_amount = $amount - $discount_value;

    }
    // No discount
    else {

        $discount_by = 0.00;
        $discount_value = 0;
        $discounted_amount = $amount;
    }

    return [
        "discount_by"       => $discount_by,
        "discount_value"    => $discount_value,
        "discounted_amount" => $discounted_amount
    ];
}
    

    
function calculateDiscount($amount) 
{
    
    //CONFIG
    $discount_rate = 0.00; // 1%
    $min_amount = 200; //discount starts here
    $max_cap = 20000; // discount capped here
    
    //Default values
    $discount_by = 0;
    $discount_value = 0;
    $discounted_amount = $amount;

    // Apply discount only when condition is met
    if ($amount >= $min_amount) { 
        
        // Cap the amount used for discount calculation
        $discount_base = min($amount, $max_cap);
        
        $discount_by = $discount_rate;
        $discount_value = $discount_base * $discount_rate;
        $discounted_amount = $amount - $discount_value;

    }

   // Round money values
    $discount_value    = round($discount_value, 2);
    $discounted_amount = round($discounted_amount, 2);

    return [
        "discount_by"       => $discount_by,
        "discount_value"    => $discount_value,
        "discounted_amount" => $discounted_amount
    ];
}
}


?>