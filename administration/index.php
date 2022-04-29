<?php
	ini_set('max_file_uploads', 30);
	
	header("X-Frame-Options: DENY"); 
	header("X-XSS-Protection: 1; mode=block"); 
	header("Strict-Transport-Security: max-age=30");
	header("Referrer-Policy: same-origin");
	
	error_reporting(E_ALL);
	
	set_time_limit(0); 
	session_start(); 
	session_regenerate_id();

	include("../resources/PHP/Class.ImageSanitize.php");
	include("../resources/PHP/Class.Shop.php");
	include("../core/Cryptography.php");
	
	$shop  		  = new Shop();
	$cryptography = new Cryptography();

	if(isset($_SESSION['token'])) {
		$token = $_SESSION['token'];
	} else {
		$token = $cryptography->getToken();
		$_SESSION['token'] = $token;
	}
	
	$_SESSION['admin-uuid'] = $cryptography->uniqueID();
	
	if(!isset($_SESSION['admin-uuid']) || empty($_SESSION['admin-uuid'])) {
		echo 'Could not initialize a session. Possible reasons: session data might be full or not possible to create a session. For security reasons the administration panel cannot be loaded. Exiting.';
		exit;
	}
	
	// create a new admin token
	if(!isset($_SESSION['uuid'])) {
		$token  = $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$token .= $cryptography->uniqueID();
		$_SESSION['uuid'] = $token;		
	} else {
		$token = $_SESSION['uuid'];
	}

	// configuration files are stored in the /server/ folder.
	$serverconfig_csv = [
		'currencies.conf.csv',
		'messages.conf.csv',
		'orders.conf.csv',
		'shipping.conf.csv',
		'shop.conf.csv',
		'site.conf.csv',
		'tax.conf.csv',
		'payment.conf.csv'];
	
	$serverconfig_json = [
		'currencies.conf.json',
		'messages.conf.json',
		'orders.conf.json',
		'shipping.conf.json',
		'shop.conf.json',
		'site.conf.json',
		'tax.conf.json',
		'payment.conf.json'];

?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="resources/admin.css">
</head>
<body>
<nav>
	<div><h2><a href="./">Administration</a></h2> <h2><a href="products/">Products</a> <h2><a href="upload-csv/">Upload CSV</a></h2>  <h2><a href="upload-json/">Upload JSON</a></h2> <h2><a href="downloads/">Downloads</a></h2> <h2><a href="upload-images/">Upload Images</a></h2> <h2><a href="lightbox/">Lightbox</a></h2> <h2><a href="logs/">Logs</a></div>
</nav>

<hr />
<div id="admin-message">
Welcome to the administration panel.
This part of the page should be placed behind a password protected area. No warranty given, use at your own discretion.
</div>

<hr />
<?php

	if(isset($_POST['upload_json'])) {
		
		if($_SESSION['uuid'] != $_POST['token']) {
			echo '<div class=\"alertmessage\">Token is incorrect.</div>';
			exit;
		}
		
		echo "<hr /><div class=\"message\">";
		
		$j=1;
		$count = count($_FILES['json_file']['name']);

		for ($i = 0; $i < $count; $i++) {
				
			if($_FILES['json_file']['error'][$i] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['json_file']['tmp_name'][$i])) { 
			
					if($_FILES['json_file']['type'][$i] != 'application/json') {
						echo "<div class=\"alertmessage\">File is not a JSON file.</div>";
						exit;
					}
			
					// Sanitize filename and prevent directory traversal
					$uploaded_file = $_FILES['json_file']['tmp_name'][$i];
					$file = file_get_contents($uploaded_file); 
					$name = $_FILES['json_file']['name'][$i];
					
					$upload = $shop->convert($file,'json_to_csv_admin',$name,'../inventory/backups/');

					echo $j ."<div class=\"message\">Successfully upload ".$shop->sanitize($_FILES['json_file']['name'][$i],'table')." JSON and converted to CSV.</div>";
				
				} else {
					
				echo $shop->sanitize($_FILES['json_file']['error'][$i],'table');
			}
			$j++;
		}
		echo '</div>';
		
		unset($_SESSION['uuid']);
	}

	if(isset($_POST['upload_csv'])) {

		if($_SESSION['uuid'] != $_POST['token']) {
			echo 'Token is incorrect.';
			exit;
		}
		
		echo "<hr /><div class=\"message\">";
		$count = count($_FILES['csv_file']['name']);
		
		$j=1;
		
		for ($i = 0; $i < $count; $i++) {
				
			if($_FILES['csv_file']['error'][$i] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['csv_file']['tmp_name'][$i])) { 
				if($_FILES['csv_file']['type'][$i] !=  'text/csv') {
					echo "<div class=\"alertmessage\">File is not a CSV! the mime type should be text/csv.</div>";
					exit;
				}
			
				// $file = file_get_contents($_FILES['csv_file']['tmp_name'][$i]);  
				
				$file = iconv('windows-1252', 'utf-8', file_get_contents($_FILES['csv_file']['tmp_name'][$i]));
				
				$showfile =  $shop->convert($file,'csv_to_json',$_FILES['csv_file']['name'][$i],'../inventory/backups/');
				$f = str_replace('.csv','',$_FILES['csv_file']['name'][$i]);
				
				if(in_array($_FILES['csv_file']['name'][$i],$serverconfig_csv)) {
					$server_path = '../server/config/';
					} else {
					$server_path = '../inventory/';
				}

				@chmod($server_path.$shop->sanitize($f,'alphanum').'.json',0777);
				@chmod($server_path.'/csv/'.$shop->sanitize($_FILES['csv_file']['name'][$i]),0777);

				$json_upload = $shop->storedata($server_path.$shop->sanitize($f,'table').'.json',$showfile,'json'); 
				$csv_upload = $shop->storedata($server_path.'/csv/'.$shop->sanitize($f,'table').'.csv',$file,'csv'); 

					if($json_upload != true) {
						
						echo "<div class=\"alertmessage\">JSON file could not be stored. Please make sure the /inventory/ directory has adequate writing permissions.</div>";
						
						} elseif($csv_upload != true) {
							
						echo "<div class=\"alertmessage\">a CSV copy file could not be stored. Please make sure the /inventory/csv/ directory has adequate writing permissions.</div>";
						
						} else {
							
						echo "<div class=\"message\">".$j.": Successfully upload ".$shop->sanitize(str_ireplace('.csv','',$_FILES['csv_file']['name'][$i]),'alphanum').".csv and converted to JSON.</div>";
						@chmod($server_path.$shop->sanitize($f,'alphanum').'.json',0755);
						@chmod($server_path.'/csv/'.$shop->sanitize($_FILES['csv_file']['name'][$i]),0755);
					}

				} else {
					
				echo $shop->sanitize($_FILES['csv_file']['error'][$i],'table');
			}
			$j++;
		}
		echo '</div>';
		
		unset($_SESSION['uuid']);
	}
	

	if(isset($_POST['upload'])) {
	
		if($_SESSION['uuid'] != $_POST['token']) {
			echo '<div class=\"alertmessage\">Token is incorrect.</div>';
			exit;
		}
		
		if($_POST['upload'] == 1) {
			
			$createdir = true;
			if(isset($_POST['destination'])) {
				
				if($_POST['destination'] != '') {

						$destination  = '../resources/images/';
						$catfolder = strtolower($shop->sanitize($_POST['destination'],'dir'));
						
						if(strstr($catfolder,'../') || strstr($catfolder,'./'))  {
							echo "<div class=\"alertmessage\">Directory traversal is not allowed.</div>".PHP_EOL;
							exit;
						} else {
							
							$destination .= strtolower($shop->sanitize($_POST['destination'],'dir'));
				
							if (!is_dir($destination)) {
								$createdir = mkdir($destination, 0777, true);
								if($createdir == true) {
									echo "<div class=\"message\">Directory did not exist, OpenShop created the new directory. (Be mindful that OpenShop does not allow special characters in directory names, including spaces).<br/>The new directory is named: ".$shop->sanitize($destination,'encode')."</div>".PHP_EOL;
									$createdir = true;
								} 
							}
						}

				$disallowed = ['./','\\','../',':',';'];
				
				$countimages = count($_FILES['files']['name']);
				
					if($countimages >=1) {
						
						for($g=0;$g<$countimages;$g++) {
								
								for($f=0;$f<count($disallowed);$f++) {

										if(strstr($_FILES['files']['name'][$g],$disallowed[$f]))  {
											echo "<div class=\"alertmessage\">Image name contains illegal characters, directory traversal is not allowed.</div>".PHP_EOL;
											exit;
										}
								}

								if($_FILES['files']['error'][0] != 1) {
											if($createdir) { 
												move_uploaded_file($_FILES['files']['tmp_name'][$g], strtolower($destination).'/'.$shop->sanitize($_FILES['files']['name'][$g],'image')) or die('error: could not upload image.'); 
												echo "<div class=\"message\">Image successfully uploaded.</div>";
											}					
										} else {
											
								}
						}
					}
				}
			}							
		}	
		unset($_SESSION['uuid']);		
	} 
?>
</div>

</body>
</html>
