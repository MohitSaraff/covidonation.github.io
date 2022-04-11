<?php

require('config.php');
require('razorpay-php/Razorpay.php');
session_start();

// Create the Razorpay Order

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret);

//
// We create an razorpay order using orders api
// Docs: https://docs.razorpay.com/docs/orders
//
$price = $_POST['amount'] * 100;
$_SESSION['price'] = $price;
$cust_name = $_POST['name'];
$cust_email = $_POST['email'];
$_SESSION['email'] = $cust_email;
$cust_mobile = $_POST['mobile'];
$messag = $_POST['message'];
$_SESSION['message'] = $messag;
$oid = rand(1001,9990);
$orderData = [
    'receipt'         => 3456,
    'amount'          => $price, // 2000 rupees in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // auto capture
];

$razorpayOrder = $api->order->create($orderData);

$razorpayOrderId = $razorpayOrder['id'];

$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];

if ($displayCurrency !== 'INR')
{
    $url = "https://api.fixer.io/latest?symbols=$displayCurrency&base=INR";
    $exchange = json_decode(file_get_contents($url), true);

    $displayAmount = $exchange['rates'][$displayCurrency] * $amount / 100;
}


$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => "Donation",
    "description"       => "We Accept Covid Relief Funds",
    "image"             => "https://kingcounty.gov/~/media/depts/emergency-management/images/donations/money.ashx?la=en&w=15rem&as=1",
    "prefill"           => [
    "name"              => $cust_name,
    "email"             => $cust_email,
    "contact"           => $cust_mobile,
    ],
    "notes"             => [
    "address"           => $messag,
    "merchant_order_id" => "12312321",
    ],
    "theme"             => [
    "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

if ($displayCurrency !== 'INR')
{
    $data['display_currency']  = $displayCurrency;
    $data['display_amount']    = $displayAmount;
}

$json = json_encode($data);
?>


<form action="verify.php" method="POST">
  <script
    src="https://checkout.razorpay.com/v1/checkout.js"
    data-key="<?php echo $data['key']?>"
    data-amount="<?php echo $data['amount']?>"
    data-currency="INR"
    data-name="<?php echo $data['name']?>"
    data-image="<?php echo $data['image']?>"
    data-description="<?php echo $data['description']?>"
    data-prefill.name="<?php echo $data['prefill']['name']?>"
    data-prefill.email="<?php echo $data['prefill']['email']?>"
    data-prefill.contact="<?php echo $data['prefill']['contact']?>"
    data-notes.shopping_order_id="<?php echo $oid?>"
    data-order_id="<?php echo $data['order_id']?>"
    <?php if ($displayCurrency !== 'INR') { ?> data-display_amount="<?php echo $data['display_amount']?>" <?php } ?>
    <?php if ($displayCurrency !== 'INR') { ?> data-display_currency="<?php echo $data['display_currency']?>" <?php } ?>
  >
  </script>
  <!-- Any extra fields to be submitted with the form but not sent to Razorpay -->
  <input type="hidden" name="shopping_order_id" value="<?php echo $oid?>">
</form>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
   //jQuery window.onload function automatically runs when the browser loads
    $(window).on('load', function() {
     //we can select the button with its class and then click it
     jQuery('.razorpay-payment-button').click();
    });
  </script>