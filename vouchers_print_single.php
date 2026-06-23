<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helper.php';

function is_mobile_device_for_voucher_print() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    foreach (['Android', 'iPhone', 'iPad', 'iPod', 'Opera Mini', 'IEMobile', 'Mobile', 'webOS', 'BlackBerry'] as $device) {
        if (stripos($userAgent, $device) !== false) return true;
    }
    return false;
}

$voucherId = isset($_GET['title']) ? sanitize($_GET['title']) : '';
if ($voucherId === '') {
    header('Location: vouchers');
    exit;
}

$apiUrl = 'https://fms.kayxappstaroil.com/APIs/voucher_api/fetch_voucher_single.php?voucher_id=' . urlencode($voucherId);
$ch = curl_init($apiUrl);
$responseData = [];

if ($ch !== false) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode((string) $response, true) ?: [];
}

$voucher = $responseData['data'][0] ?? [];
if (empty($voucher)) {
    $_SESSION['successerrorupdated'] = 'Voucher could not be loaded for printing.';
    header('Location: vouchers');
    exit;
}

$amount = (float) ($voucher['amount'] ?? 0);
$voucherCode = (string) ($voucher['voucher_code'] ?? '');
$recipientName = (string) ($voucher['gift_recipient_name'] ?? '');
$recipientPhoto = (string) ($voucher['gift_recipient_photo'] ?? '');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <link rel="icon" href="https://staroil.services/images/alogo_light.png" type="image/x-icon">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .voucher::after {
            content: ''; /* Embed the code */
            position: absolute;
            bottom: 10px; /* Place the code at the bottom */
            right: 20px; /* Slightly offset from the edge */
            font-size: 12px; /* Smaller font for disguise */
            font-family: 'Courier New', Courier, monospace; /* Simple font */
            color: rgba(255, 255, 255, 0.2); /* Subtle color */
            letter-spacing: 5px; /* Add some spacing to make it blend in */
            z-index: 0; /* Behind other content */
            pointer-events: none; /* Prevent interaction with the code */
        }

        .voucher {
            background: linear-gradient(135deg, rgba(87, 163, 220, 0.7), rgba(143, 217, 249, 0.7)), url('images/E-VOUCHER DESIGN new (World CUP MAIN).jpg') no-repeat; /* Adjusted gradient with transparency */
            background-size: cover; /* Ensure the image covers the whole container */
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2), -5px -5px 15px rgba(255, 255, 255, 0.5);
            border-radius: 0px;
            padding: 20px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            font-family: Instrument Sans, ui-sans-serif, system-ui, sans-serif;
            overflow: hidden;
        }

        .voucher::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            font-size: 18px; /* Smaller font for disguise */
            font-family: 'Courier New', Courier, monospace; /* Simple font */
            transform: translate(-50%, -50%);
            background: url('images/E-VOUCHER DESIGN new (World CUP MAIN).jpg') no-repeat;
            background-size: cover;
            opacity: 0.8; /* Increased opacity for clearer visibility */
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .voucher * {
            position: relative;
            z-index: 1;
        }

        .btn-voucher:hover {
            background-color: #ff5a4d;
        }

        .voucher-code {
            font-family: 'Courier New', Courier, monospace;
            background-color: rgba(255, 255, 255, 0.9);
            color: black;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        
        /* Valentine Photo Section */
        
        .voucher-photo {
            position: absolute;
            bottom: 24px;
            right: 200px;
            width: 110px;
            height: 105px;
            border-radius: 50%;
            border: 4px solid red;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            background: #fff;
            z-index: 2;
            opacity: 0.9;
        }
        
        /* Image inside frame */
        .voucher-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
        }

        /* Optional heart decorations */
        .voucher-photo::after {
            content: "❤";
            position: absolute;
            bottom: -8px;
            right: -8px;
            font-size: 26px;
            color: red;
        }

        /* Gifted text */
        .gifted-to {
            position: absolute;
            bottom: 0px;
            right: 180px;
            font-family: 'Dancing Script', cursive;
            font-size: 20px;
            font-weight: 100;
            color: white;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        h2.fw-bold {
            color: #ffd600;
        }
        
        /* Valentine Ribbon */
        .vals-ribbon {
            position: absolute;
            top: 15px;
            right: -40px;
            background: linear-gradient(45deg, #fc0808f1, #ffed4d, #26ff1e);
            //background: red;
            color: #000;
            padding: 8px 50px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            transform: rotate(45deg);
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
            z-index: 3;
            letter-spacing: 1px;
            font-family: 'Dancing Script', cursive;
        }
        
        .vals-ribbon::before,
        .vals-ribbon::after {
            content: "";
            position: absolute;
            bottom: -8px;
            border-top: 8px solid #b3002d;
            border-left: 8px transparent;
            border-right: 8px transparent;
        }
        
        .vals-ribbon::before {
            left: 0;
        }
        
        .vals-ribbon::after {
            left: 0;
        }
        
        
    </style>
  
</head>

<body>
    <div class="container py-5">
      <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between mb-4">
    
    <div>
        <p class="text-primary fw-semibold mb-1">
            Voucher Print
        </p>

        <h1 class="fw-bold mb-2">
            Printable Voucher
        </h1>

        <p class="text-muted mb-0">
            Save this voucher as an image or PDF.
        </p>
    </div>

    <div class="mt-3 mt-sm-0">
        <a href="vouchers" class="btn btn-light border fw-semibold px-4">
            Back to Vouchers
        </a>
    </div>

</div>
        <!-- Voucher Section -->
        <?php
            if (is_mobile_device_for_voucher_print()) {
                echo '<div style="background: #ffcc00; padding: 10px; text-align: center; font-weight: bold;">
            📱 On Mobile? use portrait view for full voucher image.
          </div>';
            }
        ?>

        <div class="row">
            
<div class="col-md-5 mt-2">
        <div class="voucher p-4 d-flex align-items-center position-relative" id="voucher">
        
        <!-- <div class="vals-ribbon">Ghana &#9733;</div> -->

        <!-- QR Code on the left -->
        <div class="mt-4" style="flex: 0 0 100px; margin-right: 25px;">
            <div id="qrcode" style="width: 100px; height: 100px; border-radius: 15px; margin-left:-5px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
            </div>
        </div>

        <!-- Voucher details on the right -->
        <div style="flex: 1; text-align: right;">
            <p class="mt-3" style="font-size:12px; font-weight:900; margin-right:10px">
                REDEEM VOUCHER AT ANY STAROIL OUTLET
            </p>

            <h2 class="fw-bold" style="margin: 10px 0; color:#ffd600; margin-right:10px">
                &#8373;<?php echo number_format($amount);?>
            </h2>

            <div class="voucher-code" style="margin-top: 10px; font-size:14px; margin-right:10px">
                <?= $voucherCode;?>
            </div>
        </div>

        <?php if(empty($recipientName)){}else{?>
        <div class="gifted-to">
             <?php echo $recipientName;?> ❤️
        </div>
        <?php }
        if(empty($giftRecipientPhoto)){}else{
        ?>

        <div class="voucher-photo">
            <img src="<?= $giftRecipientPhoto;?>" alt="Recipient Photo" crossorigin="anonymous">
        </div>
        <?php }?>

    </div>
</div>

        </div>
        <button class="btn mt-3" onclick="downloadVoucherImage()" style="background-color:lightgrey">Save as Image</button>
        <button class="btn mt-3" onclick="downloadVoucherPDF()" style="background-color:lightgrey">Save as PDF</button>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get voucher code from PHP
        var voucherCode = "<?php echo $voucherCode; ?>";
        
        //var qrContent = "https://fms.kayxappstaroil.com/voucher/index.php?voucher=<?php echo $voucherCode; ?>";
        //var qrContent = "http://192.168.0.197/kayxappsstaroil-vx/voucher/index.php?voucher=<?php //echo $voucherCode; ?>";

        // Generate QR code
        new QRCode(document.getElementById("qrcode"), {
            text: voucherCode,    // what goes inside the QR
            width: 100,           // size
            height: 100,
            colorDark: "#000000", // dark color
            colorLight: "#ffffff",// light background
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>

<script>
    function downloadVoucherImage() {
    var voucher = document.getElementById("voucher");
    var images = voucher.querySelectorAll('img');
    var loadedCount = 0;

    if (images.length === 0) {
        captureVoucher(voucher);
        return;
    }

    images.forEach(function(img) {
        if (img.complete) {
            loadedCount++;
            if (loadedCount === images.length) captureVoucher(voucher);
        } else {
            img.onload = function() {
                loadedCount++;
                if (loadedCount === images.length) captureVoucher(voucher);
            }
        }
    });
}

function captureVoucher(voucher) {
    html2canvas(voucher, { useCORS: true, allowTaint: false }).then(function(canvas) {
        var imgData = canvas.toDataURL("image/png");
        var link = document.createElement("a");
        link.href = imgData;
        link.download = "<?php echo $voucherCode; ?>_voucher_image.png";
        link.click();
    });
}

function downloadVoucherPDF() {
    var voucher = document.getElementById("voucher");
    downloadVoucherImage(); // Ensure image is captured first

    html2canvas(voucher, { useCORS: true, allowTaint: false }).then(function(canvas) {
        var imgData = canvas.toDataURL("image/png");

        var widthPx = voucher.offsetWidth;
        var heightPx = voucher.offsetHeight;
        var widthMm = (widthPx / 96) * 25.4;
        var heightMm = (heightPx / 96) * 25.4;

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();
        pdf.addImage(imgData, 'PNG', 10, 10, widthMm, heightMm);
        pdf.save("<?php echo $voucherCode; ?>_voucher.pdf");
    });
}

</script>
</body>

</html>

