<?php require_once __DIR__ . '/includes/auth_guard.php'; ?>
<?php require_once __DIR__ . '/includes/frontend_log.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hubtel Checkout | Star Oil Fuel Voucher System</title>
  </head>
  <body>
    <script>
      localStorage.setItem("staroil:payment", "Hubtel");
      localStorage.setItem("staroil:paymentLabel", "Hubtel (MoMo/Card/Bank Transfer)");
      window.location.replace("checkout");
    </script>
    <noscript>
      <p>Continue to checkout and choose Hubtel as the payment method.</p>
      <a href="checkout">Continue to Checkout</a>
    </noscript>
  </body>
</html>
