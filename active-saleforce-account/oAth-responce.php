<?php


$actual_link = "http://$_SERVER[HTTP_HOST]";


$token_url = LOGIN_URI . "/services/oauth2/token";

$code = $_GET['code'];
if (!isset($code) || $code == "") {
    die("Error - code parameter missing from request!");
}
header( 'Location: '.$actual_link.'/wp-admin/admin.php?page=my-custom-saleforce-account&accesstoken='.$code) ;

?>