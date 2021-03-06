<?php

require('config.php');

$conn = mysqli_connect($host, $username, $password, $dbname);

session_start();

require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;

$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false)
{
    $api = new Api($keyId, $keySecret);

    try
    {
        // Please note that the razorpay order ID must
        // come from a trusted source (session here, but
        // could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    }
    catch(SignatureVerificationError $e)
    {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true)
{ 
    $razorpay_order_id = $_SESSION['razorpay_order_id'];
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $email = $_SESSION['email']; 
    $price = $_SESSION['price'] / 100;
    $message = $_SESSION['message'];

    $sql = "INSERT INTO `Payment Details` (`Order ID`, `Razorpay Payment ID`, `Status`, `Email ID`, `Amount`, `Comments`) VALUES ('$razorpay_order_id', '$razorpay_payment_id', 'success', '$email', '$price', '$message')";

    mysqli_query($conn,$sql);

    $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>
             <p>Email ID:  $email</p>
             <p>Amount:  $price</p>
             <p>Thanks for your Support</p>";
}
else
{
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
