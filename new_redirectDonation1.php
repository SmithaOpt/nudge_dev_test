<?php
require_once __DIR__ . "/../init.php";

use Nudge\Model\DonationOrderNew;
use Nudge\Model\DonationOrderTxReq;
use Zoho\CRM\Entities\Lead;
use Zoho\CRM\ZohoClient;

$clientIp = $_SERVER["REMOTE_ADDR"];

$firstName = $_POST["firstName"];
$lastName = $_POST["lastName"];
$amountKey = $_POST["amount"];
$amount = 0;
if(isset($_POST["amount"]) && $_POST["amount"] != 0 && $_POST["sliderKey"] == 1) {
    if($amountKey == 1) {
        $amount = 5000;
    } else if ($amountKey == 2) {
        $amount = 10000;
    } else if ($amountKey == 3) {
        $amount = 20000;
    } else if ($amountKey == 4) {
        $amount = 30000;
    } else if ($amountKey == 5) {
        $amount = 50000;
    }
}
if(isset($_POST["amount"]) && $_POST["amount"] != 0 && $_POST["sliderKey"] == 2) {
    if($amountKey == 1) {
        $amount = 499;
    } else if ($amountKey == 2) {
        $amount = 999;
    } else if ($amountKey == 3) {
        $amount = 1999;
    } else if ($amountKey == 4) {
        $amount = 4999;
    } else if ($amountKey == 5) {
        $amount = 9999;
    }
}
$email = $_POST["email"];
$phoneNumber = $_POST["phoneNumber"];
$campaign = $_POST["campaign"];
$adgroup = $_POST["adgroup"];
$ad = $_POST["ad"];
$pa = $_POST["pa"];
$qb = $_POST["qb"];
$rc = $_POST["rc"];
$sd = $_POST["sd"];
$te = $_POST["te"];
//$panNumber = $_POST["panNumber"];

$order = new DonationOrderNew();
$order->setId($idGenerator->generateIdForClass(DonationOrderNew::class));
$order->setCreatedAt(new DateTime());
$order->setAmount($amount);
$order->setFirstName($firstName);
$order->setLastName($lastName);
$order->setEmail($email);
$order->setPhoneNumber($phoneNumber);
$order->setIsMonthly("false");
$order->setCurrency("INR");
$order->setCampaign($campaign);
$order->setAdgroup($adgroup);
$order->setAd($ad);
$order->setPa($pa);
$order->setQb($qb);
$order->setRc($rc);
$order->setSd($sd);
$order->setTe($te);
$order->setClientIp($clientIp);
$order->save();

$orderTxReq = new DonationOrderTxReq();
$orderTxReq->setId($idGenerator->generateIdForClass(DonationOrderTxReq::class));
$orderTxReq->setCreatedAt(new DateTime());
$orderTxReq->setOrderId($order->getId());
$orderTxReq->save();



// Create a Zoho client

//$ZohoClient = new ZohoClient('c53c84f2b322dc3df9b49d74659f357f'); // Make the connection to zoho api
//$moduleName = 'CustomModule6';
//$ZohoClient->setModule($moduleName); // Set the module
//
//// ...or build them manually
//$request = array(array(
//    'First Name' => $firstName,
//    'Last Name' => $lastName,
//    'Email' => $email,
//    'Mobile' => $phoneNumber,
//    'Amount' => $amount,
//    'Lead Source' => 'Website direct',
//));
//$lead = new Lead();
//$xmlStr = \Nudge\Utils::xmlfy($request, $moduleName);
//try {
//    $response = $ZohoClient->updateRecords($xmlStr, ['duplicateCheck' => '2']);
//    if (isset($response)) {
//        log($response);
//    }
//} catch (\Zoho\CRM\Exception\ZohoCRMException $e) {
//    $response = $ZohoClient->insertRecords($xmlStr, ['duplicateCheck' => '2']);
//    if (isset($response)) {
//        log($response);
//    }
//}

try {
    $orderId = $order->getId();
    $orderTxReqId = $orderTxReq->getId();

    $em = new \Nudge\Email();
    $em->addTo(EMAIL_LOGS)
        ->setFrom(EMAIL_FROM)
        ->setSubject(EMAIL_SUBJECT_PREFIX . "Donation initiated : " . $order->getAmount())
        ->setHtml(<<<EOM
<h3>Donation initiated<h3>
<table border=1>
<tr>
    <td>Name</td><td><b>$firstName</b></td>
</tr>
<tr>
    <td>Amount:</td><td><b>$amount</b></td>
</tr>
<tr>
    <td>Email:</td><td><b>$email</b></td>
</tr>
<tr>
    <td>Phone:</td><td><b>$phoneNumber</b></td>
</tr>
<tr>
    <td>Donation Order Id:</td><td><b>$orderId</b></td>
</tr>
<tr>
    <td>Transaction Req Id:</td><td><b>$orderTxReqId</b></td>
</tr>
</table>
EOM
);

    $emailResponse = $emailer->send($em);
} catch (\SendGrid\Exception $e) {
//    echo $e->getCode();
//    foreach ($e->getErrors() as $er) {
//        echo $er;
//    }
}

$txId = $orderTxReq->getId();
$isCaptchaValid = true || $recaptcha->validate($_POST, $clientIp);
?>


<?php 
//juspay order create
$ch = curl_init('https://api.juspay.in/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
#You should use your API key here. This API is a test Key wont work in production.                 
curl_setopt($ch, CURLOPT_USERPWD, '01117A16EE01429E94EB5B31577A4DE4');
#Return Url
$return_url = "https://www.thenudge.org/";
curl_setopt($ch, CURLOPT_POSTFIELDS, array('customer_id' => $orderTxReqId , 'customer_email' => $email , 'amount' => $amount , 'customer_phone' => $phoneNumber , 'order_id' => $orderId , 'return_url' => $return_url ));
			 					 
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);                    
curl_setopt($ch,CURLOPT_TIMEOUT, 15); 
$response = curl_exec($ch); 
$parseJ_juspay =json_decode($response,true);

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>The/Nudge Foundation</title>
<meta name="description" content="The/Nudge Foundation, a non-profit startup, is an initiative of some of India’s brightest minds come together to tackle the greatest human development - poverty">
<meta name="robots" content="index, follow">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:type" content="business.business">
<meta property="og:title" content="The/Nudge Foundation">
<meta property="og:url" content="https://www.thenudge.org">
<meta property="og:image" content="https://www.thenudge.orghttps://www.thenudge.org/images/thenudge.jpg">
<meta property="business:contact_data:locality" content="Bangalore">
<meta property="business:contact_data:region" content="Karnataka">
<meta property="business:contact_data:country_name" content="India">
<meta name="twitter:card" content="app">
<meta name="twitter:site" content="@thenudge_in">
<!--    <meta name="viewport" content="width=1440,user-scalable=n">-->
<link rel="icon" href="https://www.thenudge.org/images/favicon.ico">
<link href="//fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
<link href="//fonts.googleapis.com/css?family=Yellowtail" rel="stylesheet" type="text/css">
<link href="//fonts.googleapis.com/css?family=Raleway" rel="stylesheet" type="text/css">
<link href='//fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Oxygen:400,300,700' rel='stylesheet' type='text/css'>
<!--    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">-->
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<!-- Owl Carousel -->
<link rel="stylesheet" href="https://www.thenudge.org/css/owl.carousel.min.css">
<link rel="stylesheet" href="https://www.thenudge.org/css/owl.theme.default.min.css">
<!--    <title>The/Nudge</title>-->
<!--    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
<!-- Bootstrap -->
<link href="https://www.thenudge.org/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<link href="https://www.thenudge.org/css/bootstrap.min.css" rel="stylesheet">
<link href="https://www.thenudge.org/css/bootstrap-slider.min.css" type="text/css" rel="stylesheet">
<link href="https://www.thenudge.org/css/styles.css" rel="stylesheet">
<!-- Bootstrap Slider -->

 <!--Add the pay.js and its depedency Jquery-->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"> </script>
    <script type="text/javascript" src="https://api.juspay.in/pay.js"></script>

</head>
<body class="donate" xmlns="http://www.w3.org/1999/html">
<nav class="navbar navbar-default navbar-fixed-top nudge-top-nav-small">
  <div class="nav-bg-small"></div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6 col-sm-12">
        <div class="logo v-offset-15"> <a href="/"> <img src="https://www.thenudge.org/images/logo_white.png" alt="Nudge"> </a> </div>
      </div>
    </div>
  </div>
</nav>
<div class="jumbotron tile tile-6 ">
  <div class="container-fluid donate-form-section">
    <div class="row">
      <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 donation-head-text pull-right">
        <h4 class = "hidden">we have 1000s of students like Mallige who are hopeful to get their first job and support their families. pledge your support to take one such family out of poverty. </h4>
      </div>
      <div class="col-md-6 col-lg-6 col-xs-12 col-sm-12 hidden-xs hidden-sm  left">
        <div class="col-xs-12 hidden-sm hidden-md hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0" src="https://www.thenudge.org/images/donate-xs.jpg" /> <!--Mobile--> 
        </div>
        <div class="hidden-xs col-sm-12 hidden-md hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0" src="https://www.thenudge.org/images/donate-sm.jpg" /> <!--Tab--> 
        </div>
        <div class="hidden-xs hidden-sm col-md-12 hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0" src="https://www.thenudge.org/images/donate-md.jpg" /> <!--Desktop--> 
        </div>
        <div class="hidden-xs hidden-sm hidden-md col-lg-12" style="padding: 0"> <img class="img-responsive" style="margin: 0" src="https://www.thenudge.org/images/donate-lg.jpg" /> <!--Large--> 
        </div>
        <!--                </div>--> 
      </div>
      <div class="col-md-6 col-xs-12 right">
          <div class="panel-group" id="accordion"> 
            <!-- panel3 -->
            <div class="panel panel-default" id="panel1">
              <div class="panel-heading" id="panel3heading" style="color: #ffce00;background-color: #333;">
                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="" aria-expanded="true">Payment</a></h4>
              </div>
              <div id="collapseThree" class="panel-collapse collapse in" aria-expanded="true">
                <div class="panel-body">
                  <div class="row">
                    <div class="col-md-11">
                            <form class="juspay_inline_form" id="payment_form" name="paymentForm" style="display:none;">
                                <input type="hidden" class="merchant_id" value="optimosys">
                                <input type="hidden" class="order_id" value="<?php echo $orderId  ?>"/>
                                <input type="hidden" class="payment_method_type" value="WALLET"/>
                                <select class="payment_method">
                                    <option value="PAYTM" label="PayTM Wallet">PayTM Wallet</option>
                                </select>
                                <button type="submit" class="make_payment">Pay</button>
                            </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- panel3 --> 
            
          </div>
          <!--/panel-according accordion -->
          

      </div>
      <div class="hidden-md hidden-lg col-xs-12 col-sm-12 right"> 
        <!--                <div class="row first  image-donate">-->
        <div class="col-xs-12 hidden-sm hidden-md hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0 auto" src="https://www.thenudge.org/images/donate-xs.jpg" /> <!--Mobile--> 
        </div>
        <div class="hidden-xs col-sm-12 hidden-md hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0 auto" src="https://www.thenudge.org/images/donate-sm.jpg" /> <!--Tab--> 
        </div>
        <div class="hidden-xs hidden-sm col-md-12 hidden-lg" style="padding: 0"> <img class="img-responsive" style="margin: 0 auto" src="https://www.thenudge.org/images/donate-md.jpg" /> <!--Desktop--> 
        </div>
        <div class="hidden-xs hidden-sm hidden-md col-lg-12" style="padding: 0"> <img class="img-responsive" style="margin: 0 auto" src="https://www.thenudge.org/images/donate-lg.jpg" /> <!--Large--> 
        </div>
      </div>
    </div>
    <div class="row col-md-6">
      <p style="font-size: 15px; margin-top: 15px" >donations are exempted from tax. <br/>
        <span style="font-size: 12px;"><a href="/files/NLF-12AA.pdf" download="12AA.pdf">12AA certificate</a> | <a
                        href="/files/NLF-80G.pdf" download="80G.pdf">80G certificate</a></span> </p>
    </div>
  </div>
</div>
<footer class="footer-donate" style="height: 80px">
  <div class="bottom-text">© 2017 Nudge Lifeskills Foundation<br/>
    <span>Nudgeville, C9, 1st C Main Road, Sector 6, HSR Layout, Bangalore - 560102</span></div>
</footer>
<script type="text/javascript"
src='https://crm.zoho.com/crm/javascript/zcga.js'> </script> 
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75025928-1', 'auto');
  ga('send', 'pageview');
  </script> 
<!-- Zoho SalesIQ Code --> 
<!--<script type='text/javascript'>--> 
<!--var $zoho= $zoho || {salesiq:{values:{},ready:function(){$zoho.salesiq.floatbutton.visible('hide');}}}; var d=document; s=d.createElement('script'); s.type='text/javascript'; s.defer=true; s.src='https://salesiq.zoho.com/thenudgefoundation/float.ls?embedname=thenudgefoundation'; t=d.getElementsByTagName('script')[0]; t.parentNode.insertBefore(s,t);--> 
<!--</script>--> 

<!-- End Zoho SalesIQ Code --><script src="https://www.thenudge.org/js/jquery.min.js" type="text/javascript"></script> 
<script src="https://www.thenudge.org/js/jquery.validate.min.js" type="text/javascript"></script> 
<script src="https://www.thenudge.org/js/bootstrap.min.js" type="text/javascript"></script> 
<!--<script src="https://www.thenudge.org/js/app.js" type="text/javascript"></script>--> 
<script src='https://www.google.com/recaptcha/api.js'></script> 
<script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-75025928-1', 'auto');
        ga('send', 'pageview');

    </script> 
<script src="https://www.thenudge.org/js/jquery.min.js" type="text/javascript"></script> 
<script src="https://www.thenudge.org/js/bootstrap.min.js" type="text/javascript"></script> 
<script src="https://www.thenudge.org/js/owl.carousel.js"></script> 
<!--<script type="text/javascript"--> 
<!--        src="https://api.juspay.in/pay-v2.js"></script>--> 
<script src="https://www.thenudge.org/js/bootstrap-slider.min.js" type="text/javascript"></script> 

<!--Call Juspay.setup with your own success and error handler.--> 
<script type="text/javascript">
	Juspay.Setup({
		payment_form: "#payment_form",
		success_handler: function(status) {
			//redirect to success page
			window.location = "https://www.thenudge.org/new_success.php";
		},
		error_handler: function(error_code, error_message, bank_error_code, bank_error_message, gateway_id) {
			//redirect to failure page
			window.location = "https://www.thenudge.org/new_failure.php";
		}

	});
	
	$(document).ready(function(e) {
            $('#payment_form').submit();
        });
	
</script> 


    
</body>
</html>
