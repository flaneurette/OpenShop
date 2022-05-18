<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=0.73">
		<title>OpenShop webshop</title>
		<meta charset="utf-8">
		<meta name="description" content="OpenShop webshop is your place to shop!">
		<meta name="author" content="OpenShop">
		<meta name="Pragma" content="no-cache">
		<meta name="Cache-Control" content="no-cache">
		<meta name="Expires" content="-1">
		<meta name="revisit-after" content="3 days">

		<link rel="stylesheet" href="resources/style/themes/default/admin.css?rev=3" type="text/css" />
	</head>
<body>

<div id="wrapper">

<?php

	error_reporting(E_ALL);
	
	session_start();

	/***
	/* There is no need in editing this file.
	*/

	include("resources/PHP/Class.Shop.php");
	include("core/Cryptography.php");
	include("core/StoreData.php");
	
	$shop = new Shop;
	$cryptography 		= new Cryptography;
	$sanitizer 	  	= new Sanitizer;
	$backups 	 	= new Backup;
	$storagecontainer 	= new StoreData;
	
	$versioning = PHP_VERSION_ID;
	$error = [];
	
	$host = $_SERVER['HTTP_HOST'];

	if(isset($_SERVER["PHP_SELF"])) {
		$path = $_SERVER["PHP_SELF"];
		if(stristr($path,'/')) {
			$path_pieces = explode('/',$path);
				if(count($path_pieces) >=2) {
					if(count($path_pieces)-2 != '') {
						$shopfolder = $path_pieces[count($path_pieces)-2];
					}
				}
			} else {
			$shopfolder = 'shop';
		}
	} elseif(isset($_SERVER["REQUEST_URI"])) {
		$path = $_SERVER["REQUEST_URI"];
		if(stristr($path,'/')) {
			$path_pieces = explode('/',$path);
				if(count($path_pieces) >=2) {
					if(count($path_pieces)-2 != '') {
						$shopfolder = $path_pieces[count($path_pieces)-2];
					}
				}
			} else {
			$shopfolder = 'shop';
		}		
	} else {
		// could not determine shop folder, defaulting.
		$shopfolder = 'shop';
	}

	/***
	/* Installer deletion.
	*/
	
	if(isset($_GET['delete'])) {
		if($_SESSION['nonce'] == $_GET['delete']) {
			header("Location: index.php",302);
			@unlink("install.php");
			@unlink("fresh.php");
			exit;
		} else {
			echo '<div class="installer-message">';
			echo 'Nonce was incorrect, could not delete file. Please delete it manually.';
			echo '</div>';
			exit;
		}
	} 

	/***
	/* Create security nonce, against CSRF.
	*/
	
	if(isset($_SESSION['nonce'])) {
		$nonce = $sanitizer->sanitize($_SESSION['nonce'],'alphanum');
		} else {
		$nonce = $cryptography->pseudoNonce();
		$_SESSION['nonce'] = $sanitizer->sanitize($nonce,'alphanum');
	}

	$session = fopen("administration/session.ses", "r") or die("<div class=\"installer-message\">Unable to open administration/session.ses. Cannot continue installation. Please make this file readable by chmodding it to at least 0755.</div>");
	$sdata 	 = fread($session,10);
	if(strlen($sdata) >= 10) {
				fclose($session);
				die("<div class=\"installer-message\">Unable to continue installation. Reason: installer has been run before, or is in use by another user. For security reasons, we cannot have more than one installer running at once or an installation that has already been completed. If this is in error, empty <a href=\"administration/session.ses\">session.ses</a> manually and run the installer again.</div>");
	}
			
	/***
	/* Check if required PHP version is present.
	*/
	
	if(!defined('PHP_VERSION_ID') || $versioning  < 50400) {
		array_push($error,'PHP version 5.4 or above is required, cannot install OpenShop.');
	}

	/***
	/* Check if fopen is enabled in PHP, required for installation.
	*/
	
	if(function_exists('ini_get')) {
		if(!ini_get('allow_url_fopen') ) {
			die('<div class="installer-message">Please set "allow_url_fopen" to "On" in PHP.ini, for adequate stream support. OpenShop does NOT work without it.</div>');
		} 
	}

	if(!is_writable("inventory/backups/")) {
		if(chmod("inventory/backups/",0777) == false) {
			array_push($error, "Could not chmod the /inventory/backups/ directory. Please chmod the /inventory/backups/ folder manually to 0777.");
		}
	}
	
	if(!is_writable("server/config/site.conf.json")) {
		if(chmod("server/config/site.conf.json",0777) == false) {
			array_push($error, "Could not chmod the inventory. Please chmod the /inventory/ folder manually to 0777.");
		}
	}

	if(!is_writable("server/config/paypal.json")) {
		if(chmod("server/config/paypal.json",0777) == false) {
			array_push($error, "Could not chmod the paypal directory. Please chmod the /server/config/paypal.json folder manually to 0777.");
		}
	}

	/***
	/* Check if required files are missing.
	*/
	
	if(!file_exists('server/config/site.conf.json')) {
		array_push($error, "OpenShop software package is incomplete or has missing files: server/config/site.conf.json. Please clone or download again.");
	}
	
	if(!file_exists('server/config/paypal.json')) {
		array_push($error, "OpenShop software package is incomplete or has missing files: /server/config/paypal.json. Please clone or download again.");
	}
	
	if(!is_writable("resources/images/")) {
		if(chmod("resources/images/",0777) == false) {
			array_push($error, "Could not chmod resources/images/. Please chmod the directory manually to 0777.");			
		}
	}

	if(!is_writable("resources/images/products/")) {
		if(chmod("resources/images/products/",0777) == false) {
			array_push($error, "Could not chmod resources/images/products/. Please chmod the directory manually to 0777.");			
		}
	}
	
	if(!is_writable("resources/images/category/")) {
		if(chmod("resources/images/category/",0777) == false) {
			array_push($error, "Could not chmod resources/images/category/. Please chmod the directory manually to 0777.");			
		}
	}	

	if(!is_writable("administration/.htpasswd")) {

		if(chmod("administration/",0777) == false) {
			array_push($error, "Could not chmod the administration directory. Please chmod the /administration/ folder manually to 0777.");
		}
		
		if(chmod("administration/.htpasswd",0777) == false) {
			array_push($error, "Could not chmod the .htpasswd file. Please chmod the file manually to 0777.");
		}
	}
	
						
	$httest = fopen("administration/.htpasswd", "w");
	
	if($httest == FALSE || $httest == false) {
		array_push($error, "Could not open .htpassword for writing. In Apache, the folder should be chowned to www-data:www-data.");
	}

	/***
	/* Check if required functions and extensions are missing.
	*/
	
	if(!function_exists('file_get_contents')) {
		
		array_push($error,'file_get_contents function does not work, please "allow_url_fopen" for stream support. OpenShop does NOT work without it.');
		
		if(function_exists('stream_get_wrappers')) {
			$wrappers = stream_get_wrappers();
			if(count($wrappers) > 0) {
				$sr = implode(",",stream_get_wrappers());
				array_push($error,'The current supported streamwrappers are: '.$sr);
				} else {
				array_push($error,'There appears to be no support for streamwrappers, please "allow_url_fopen" for stream support.');
			}
		}
	}
	
	if(!function_exists('mb_convert_encoding')) {
		array_push($error,'mb_convert_encoding function does not work, please install the "mbstring" library for multibyte support. OpenShop might not work without it.');
	}

	if(!function_exists('mail')) {
		array_push($error,'MAIL extension is not working properly.');
	}

	if(!function_exists('json_decode')) {
		array_push($error,'JSON extension is not working properly.');
	}

	if(!function_exists('random_bytes')) {
		array_push($error,'random_bytes function does not exist.');
	}

	if(!function_exists('openssl_random_pseudo_bytes')) {
		array_push($error,'Openssl_random_pseudo_bytes function does not exist.');
	}
	
	if(!function_exists('openssl_encrypt')) {
		array_push($error,'OpenSSL is not supported or enabled on this PHP instance.');
	}
	
	if(!function_exists('openssl_decrypt')) {
		array_push($error,'Openssl_decrypt function does not exist.');
    }

	/***
	/* Error reporting.
	*/

	if(count($error) >= 1) {
		
		echo '<h1>Installation failed.</h1>' . PHP_EOL . PHP_EOL;
		
		echo "<div class=\"installer-message\">";
		echo "Please chown the shop folder to www-data:www-data or the user OpenShop is running under.<br /><br />";
		echo "<code>chown -R www-data:www-data shopfolder</code><br /><br />";
		echo "Usually, this will fix installer issues. It chowning does not work, proceed to the below instructions:";
		echo "</div>";
		
		echo "Or:";
	
			echo '<pre>';
					echo "<div class=\"installer-message\">";
					
						$i=1;
						
						foreach($error as $e) {
							echo $i . ": " . strip_tags($e) . PHP_EOL;
							$i++;
						}
						
					echo "</div>";
				
			echo '</pre>';
			
			exit;

	} elseif(isset($_POST['setup']) == 1) {
		
			/***
			/* Check if the installer already has been run before, or is running by another user. If so, exit the installer and warn user.
			*/

			if(!is_writable("administration/session.ses")) {
				chmod("administration/session.ses",0777);
			}

			$session = fopen("administration/session.ses", "rw+") or die("<div class=\"installer-message\">Unable to open administration/session.ses. Cannot continue installation.</div>");
			$tmp_nonce = $cryptography->getToken();
			
			$ip = $sanitizer->sanitize($_SERVER['REMOTE_ADDR'],'field');
			$install_nonce = sha1($ip . PHP_VERSION_ID . $tmp_nonce);
			
			$sdata 	 = fread($session,10);
			
			if(strlen($sdata) >= 10) {
				fclose($session);
				die("<div class=\"installer-message\">Unable to continue installation. Reason: installer has been run before, or is in use by another user. For security reasons, we cannot have more than one installer running at once or an installation that has already been completed. If this is in error, empty <a href=\"administration/session.ses\">session.ses</a> manually and run the installer again.</div>");
				} else {
				$tmp_nonce = $cryptography->getToken();
				$install_nonce = 'OpenShop-INSTALL-ID:' . sha1($_SERVER['REMOTE_ADDR'] . PHP_VERSION_ID . $tmp_nonce) . '-IP:' . sha1($ip  . PHP_VERSION_ID . $tmp_nonce);
				fwrite($session, $install_nonce);
				fclose($session);
			}
			
				if(isset($_SESSION['nonce'])) {
					
					$nonce = $sanitizer->sanitize($_SESSION['nonce'],'alphanum');
					
					if($_SESSION['nonce'] != $sanitizer->sanitize($_POST['nonce'])) {
						echo '<div class="installer-message">Security Nonce is expired or missing.</div>';
						exit;	
					}
						
				} else {
					echo '<div class="installer-message">Security Nonce is expired or missing.</div>';
					exit;					
				}

				if(!isset($_POST['admin_username'])) {
					echo '<div class="installer-message">Username cannot be empty, setup could not continue.</div>';
					exit;
				}
				
				if(!isset($_POST['admin_password'])) {
					echo '<div class="installer-message">Password cannot be empty, setup could not continue.</div>';
					exit;
				}
				
				if(!isset($_POST['admin_website'])) {
					echo '<div class="installer-message">Website cannot be empty, setup could not continue.</div>';
					exit;					
				}
				
				if(!isset($_POST['admin_email'])) {
					echo '<div class="installer-message">Admin e-mail cannot be empty, setup could not continue.</div>';
					exit;					
				}	
				
				if(!isset($_POST['admin_paypal_email'])) {
					echo '<div class="installer-message">Admin e-mail cannot be empty, setup could not continue.</div>';
					exit;					
				}				
				
				if(!isset($_POST['admin_ip'])) {
					echo '<div class="installer-message">IP cannot be empty, setup could not continue.</div>';
					exit;				
				}
				
				if(!isset($_POST['admin_currency'])) {
					echo '<div class="installer-message">Currency cannot be empty, setup could not continue.</div>';
					exit;				
				}

				if(!isset($_POST['shop_folder'])) {
					echo '<div class="installer-message">Shop folder cannot be empty, setup could not continue.</div>';
					exit;				
					} else {
					$ts_shop_folder = $sanitizer->sanitize($_POST['shop_folder'],'alpha');
				}


				/***
				/* Determiine Apache version htaccess.
				*/

				function htaccess_version() {

					$server_http_version = apache_get_version();

					$server_error_code 	= '';
					$htaccess_version 	= '1';

					if($server_http_version != false) {

					if(stristr($server_http_version,'prod') || !stristr($server_http_version,'/')) {
						$server_error_code = 'Server is set to production, cannot determine Apache version.';
						} else {
						$tmp_version = explode('/',$server_http_version);
						$c = count($tmp_version);
						if($c > 0) {
							for($i=1;$i<2;$i++) {
								$server_error_code  = (float)str_ireplace(['apache',' '],['',''],$tmp_version[$i]);
							}

						} else {
							$server_error_code = 'Cannot determine Apache version.';
						}
					}

					} else {
						$server_error_code = 'Cannot determine Apache version: reasons: Apache is not present, or PHP cannot determine version.';
					}

					$server_error_code_tmp = substr($server_error_code,0,1);

						switch($server_error_code_tmp) {

							case '0':
							$htaccess_version = 1;
							break;

							case '1':
							$htaccess_version = 1;
							break;

							case '2':
							
								$sv = substr($server_error_code,0,3);

								switch($sv) {
									case '2':
									case '2.1':
									case '2.2':
									case '2.3':
									$htaccess_version = 1;
									break;
									case '2.4':
									case '2.5':
									case '2.6':
									case '2.7':
									case '2.8':
									case '2.9':
									$htaccess_version = 2;
									default:
									break;
								}
							break;
							case range(3,12):
							$htaccess_version = 2;
							break;
							default:
							$htaccess_version = 1;
							break;
						}

					return $htaccess_version;
				}
				
/***
/* Create a .htpasswd programatically.
/* For better security, it would be good to create it manually. Consider it.
*/
	
function create_htpasswd($username,$password) {

	$encrypted_password = crypt($password, base64_encode($password));
	$data = $username.":".$encrypted_password;
					
	$ht = fopen("administration/.htpasswd", "w") or die("<div class=\"installer-message\">Could not open .htpassword for writing. Please make sure that the server is allowed to write to the administration folder. In Apache, the folder should be chowned to www-data. </div>");
	fwrite($ht, $data);
	fclose($ht);
}

function create_htaccess_root($ts_shop_folder) {
	
$htaccess_mod = '
Options All -Indexes
Options +FollowSymLinks

RewriteEngine On

# Rewrite URI\'s
RewriteCond %{HTTPS} !on
RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}

# product single item
RewriteRule ^category/(.*)/(.*)/(item)/(.*)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&subcat=$2&product=$5&productid=$6&page=$7 [NC,L]
RewriteRule ^category/(.*)/(.*)/(item)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&subcat=$2&product=$5&productid=$6 [NC,L]

RewriteRule ^category/(.*)/(item)/(.*)/(.*)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&product=$4&productid=$5&page=$6 [NC,L]
RewriteRule ^category/(.*)/(item)/(.*)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&product=$4&productid=$5&page=$6 [NC,L]
RewriteRule ^category/(.*)/(item)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&product=$4&productid=$5 [NC,L]

# products index
RewriteRule ^category/(.*)/(item)/(.*)/(.*)/(.*)/(.*)$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&product=$4&productid=$5&productid=$6 [NC,L]
RewriteRule ^category/(.*)/(item)/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Product.php?cat=$1&product=$4&productid=$5 [NC,L]

# category sorting
RewriteRule ^category/(.*)/(.*)/(sort)/(.*)$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&page=$2&sorting=$4 [NC,L]
RewriteRule ^category/(.*)/(sort)/(.*)$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&sorting=$3 [NC,L]

# single cat pag.
RewriteRule ^category/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&page=$2 [NC,L]
# single cat
RewriteRule ^category/(.*)/$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1 [NC,L]

# subcategory sorting
RewriteRule ^subcategory/(.*)/(.*)/(.*)/(sort)/(.*)$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&subcat=$2&page=$3&sorting=$5 [NC,L]
RewriteRule ^subcategory/(.*)/(.*)/(sort)/(.*)$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&subcat=$2&sorting=$4 [NC,L]

# subcat pag.
RewriteRule ^subcategory/(.*)/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&subcat=$2&page=$3 [NC,L]
# subcat
RewriteRule ^subcategory/(.*)/(.*)/$ /'.$ts_shop_folder.'/instance/Category.php?cat=$1&subcat=$2 [NC,L]

RewriteRule ^blog/$ /'.$ts_shop_folder.'/pages/blog.php  [NC,L]
RewriteRule ^articles/$ /'.$ts_shop_folder.'/pages/articles.php  [NC,L]
RewriteRule ^pages/$ /'.$ts_shop_folder.'/pages/page.php  [NC,L]

RewriteRule ^blog/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/blog.php?blogid=$1&blogtitle=$2  [NC,L]
RewriteRule ^pages/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/page.php?pageid=$1&pagetitle=$2  [NC,L]
RewriteRule ^articles/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/articles.php?articleid=$1&articletitle=$2  [NC,L]

RewriteRule ^'.$ts_shop_folder.'/blog/$ /'.$ts_shop_folder.'/pages/blog.php  [NC,L]
RewriteRule ^'.$ts_shop_folder.'/articles/$ /'.$ts_shop_folder.'/pages/articles.php  [NC,L]
RewriteRule ^'.$ts_shop_folder.'/blog/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/blog.php?blogid=$1&blogtitle=$2  [NC,L]
RewriteRule ^'.$ts_shop_folder.'/pages/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/page.php?pageid=$1&pagetitle=$2  [NC,L]
RewriteRule ^'.$ts_shop_folder.'/articles/(.*)/(.*)/$ /'.$ts_shop_folder.'/pages/articles.php?articleid=$1&articletitle=$2  [NC,L]

RewriteRule ^search/(.*)$ /'.$ts_shop_folder.'/instance/Search.php [NC,L]
RewriteRule ^refine/(.*)$ /'.$ts_shop_folder.'/instance/Refine.php [NC,L]

RewriteRule ^bargain/(.*):(.*)$ /'.$ts_shop_folder.'/instance/Pricebar.php?minprice=$1&maxprice=$2 [NC,L]

RewriteRule ^vacation/(.*)$ /'.$ts_shop_folder.'/pages/shop-error.php?reason=1 [NC,L]
RewriteRule ^offline/(.*)$ /'.$ts_shop_folder.'/pages/shop-error.php?reason=2 [NC,L]
RewriteRule ^closed/(.*)$ /'.$ts_shop_folder.'/pages/shop-error.php?reason=3 [NC,L]

# /query/rnd/action/code/
RewriteRule ^query/(.*)/(.*)/(.*)/$ instance/Query.php?action=$2&code=$3  [NC,L]

# /wishlist/rnd/action/product/tr/
RewriteRule ^wishlist/(.*)/(.*)/(.*)/(.*)/$ instance/Query.php?action=$2&product=$3&tr=$4  [NC,L]

# /cart/action/rnd/product/
# /cart/addtocart/rnd/id/

RewriteRule ^cart/$ instance/Cart.php [NC,L]

RewriteRule ^cart/checkout/$ instance/Checkout.php [NC,L]
RewriteRule ^'.$ts_shop_folder.'/cart/checkout/$ /instance/Checkout.php [NC,L]

RewriteRule ^cart/cancel/$ instance/Query.php?action=cancel [NC,L]
RewriteRule ^cart/paid/$ instance/Query.php?action=payed [NC,L]
RewriteRule ^'.$ts_shop_folder.'/cart/paid/$ /instance/Query.php?action=payed [NC,L]
RewriteRule ^cart/ipn/$ instance/Query.php?action=ipn [NC,L]
RewriteRule ^'.$ts_shop_folder.'/cart/delete/(.*)/$ /instance/Query.php [NC,L]
RewriteRule ^'.$ts_shop_folder.'/cart/update/(.*)/$ /instance/Query.php?action=$1 [NC,L]
RewriteRule ^cart/(.*)/(.*)/$ instance/Query.php?action=$1 [NC,L]
RewriteRule ^'.$ts_shop_folder.'/cart/(.*)/(.*)/$ /instance/Query.php?action=$1 [NC,L]

# Webapplication firewall.

RewriteCond %{REQUEST_METHOD}  ^(HEAD|TRACE|DELETE|TRACK) [NC,OR]
RewriteCond %{HTTP_REFERER}    ^(.*)(<|>|\'|%0A|%0D|%27|%3C|%3E|%00).* [NC,OR]
RewriteCond %{REQUEST_URI}     ^/(,|;|<|>|/{2,999}).* [NC,OR]
RewriteCond %{HTTP_USER_AGENT} ^$ [OR]
RewriteCond %{HTTP_USER_AGENT} ^(java|curl|wget).* [NC,OR]
RewriteCond %{HTTP_USER_AGENT} ^.*(winhttp|HTTrack|clshttp|archiver|loader|email|harvest|extract|grab|miner).* [NC,OR]
RewriteCond %{HTTP_USER_AGENT} ^.*(libwww|curl|wget|python|nikto|scan).* [NC,OR]
RewriteCond %{HTTP_USER_AGENT} ^.*(<|>|\'|%0A|%0D|%27|%3C|%3E|%00).* [NC,OR]
RewriteCond %{HTTP_COOKIE}     ^.*(<|>|\'|%0A|%0D|%27|%3C|%3E|%00).* [NC,OR]
RewriteCond %{QUERY_STRING}    ^.*(;|\'|").*(union|select|insert|declare|drop|update|md5|benchmark).* [NC,OR]
RewriteCond %{QUERY_STRING}    ^.*(localhost|loopback|127\.0\.0\.1).* [NC,OR]
RewriteCond %{QUERY_STRING}    ^.*\.[A-Za-z0-9].* [NC,OR] # prevents shell injection
RewriteCond %{QUERY_STRING}    ^.*(<|>|\'|%0A|%0D|%27|%3C|%3E|%00).* [NC]
RewriteRule ^(.*)$ /server/error/500.html [NC,L]

<IfModule mod_headers.c>
    Header unset ETag
</IfModule>

<IfVersion < 2.4>
	<FilesMatch "(\.(bak|config|dist|inc|ini|log|sh|sql|swp|json|csv|htpasswd)|~)$">
   	 	# Apache 2.2
   	 	Order allow,deny
    		Deny from all
    		Satisfy All
   	</FilesMatch>
</IfVersion>

<IfVersion >= 2.4>
	<FilesMatch "(\.(bak|config|dist|inc|ini|log|sh|sql|swp|json|csv|htpasswd)|~)$">
    		 Require all denied
	</FilesMatch>
</IfVersion>

<IfModule mod_deflate.c>
    # Compress all output labeled with one of the following MIME-types
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE application/atom+xml \
                                      application/javascript \
                                      application/json \
                                      application/rss+xml \
                                      application/x-web-app-manifest+json \
                                      application/xhtml+xml \
                                      application/xml \
                                      font/opentype \
                                      image/svg+xml \
                                      image/x-icon \
                                      text/css \
                                      text/html \
                                      text/plain \
                                      text/x-component \
                                      text/xml
    </IfModule>
</IfModule>
';

if(!is_writable(".htaccess")) {

chmod(".htaccess",0777);

}

$hta_root = fopen(".htaccess", "w+") or die("<div class=\"installer-message\">Unable to open .htaccess</div>");
fwrite($hta_root, $htaccess_mod);
fclose($hta_root);

}

/***
/* Create a .htaccess programatically.
*/	
				
function create_htaccess($ip,$root,$ts_shop_folder) {						
$htaccess = 'AuthType Basic
AuthName "OpenShop Administration"
AuthUserFile '.$root.'/'.$ts_shop_folder.'/administration/.htpasswd
Require valid-user
Order Deny,Allow
Deny from all
Allow from '.$ip.'
';

				if(!is_writable("administration/.htaccess")) {
					chmod("administration/.htaccess",0777);
				}
					
				$hta = fopen("administration/.htaccess", "w+") or die("<div class=\"installer-message\">Unable to open administration .htaccess</div>");
					fwrite($hta, $htaccess);
					fclose($hta);
				}

				$username = $sanitizer->sanitize($_POST['admin_username'],'unicode');
				$password = $sanitizer->sanitize($_POST['admin_password'],'query');
				$ip 	  = $sanitizer->sanitize($_POST['admin_ip'],'table');
				$root 	  = $sanitizer->sanitize($_SERVER['DOCUMENT_ROOT'],'table');
				
				create_htpasswd($username,$password);
				create_htaccess($ip,$root,$ts_shop_folder);
				create_htaccess_root($ts_shop_folder);

				/***
				/* Store Site JSON configuration.
				*/	
				
				$keys = 'server/config/site.conf.json';
				$backups->backup($keys);
				$json = $shop->json->load_json($keys); 
				
				$json[0]["site.canonical"] 	= $ts_shop_folder;
				$json[0]["site.url"] 		= $sanitizer->sanitize($_POST['admin_website'],'url');
				$json[0]["site.domain"] 	= $sanitizer->sanitize($_POST['admin_website'],'url');
				$json[0]["site.currency"] 	= $sanitizer->sanitize($_POST['admin_currency'],'num');
				$json[0]["site.email"] 		= $sanitizer->sanitize($_POST['admin_email'],'url');

				if($_POST['admin_encryption'] == '1') {
					$json[0]["site.email"] = $cryptography->encrypt($sanitizer->sanitize($_POST['admin_email'],'url'));
					} else {
					$json[0]["site.email"] = $sanitizer->sanitize($_POST['admin_email'],'url');
				}
				
				if($_POST['theme'] == 'default') {
					$json[0]["site.stylesheet.reset"] = "resources/style/themes/default/reset.css";
					$json[0]["site.stylesheet1"] = "resources/style/themes/default/css.css";
					$json[0]["site.stylesheet2"] = "resources/style/themes/default/style.css";
					$json[0]["site.stylesheet3"] = "resources/style/themes/default/pages.css";
				}

				if($_POST['theme'] == 'dark') {
					$json[0]["site.stylesheet.reset"] = "resources/style/themes/dark/reset.css";
					$json[0]["site.stylesheet1"] = "resources/style/themes/dark/css.css";
					$json[0]["site.stylesheet2"] = "resources/style/themes/dark/style.css";
					$json[0]["site.stylesheet3"] = "resources/style/themes/dark/pages.css";
				}
				
				if(isset($_POST['freeshipping'])) {
					$json[0]["site.freeshipping"] = (int)$_POST['freeshipping'];
					} else {
					$json[0]["site.freeshipping"] = "50.00";
				}

				if(isset($_POST['admin_title'])) {
					$json[0]["site.title"] = $sanitizer->sanitize($_POST['admin_title'],'unicode');
					$json[0]["site.meta.title"] = $sanitizer->sanitize($_POST['admin_description'],'unicode'); 
				}
				
				if(isset($_POST['admin_description'])) {
					$json[0]["site.description"] = $sanitizer->sanitize($_POST['admin_description'],'unicode'); 
					$json[0]["site.meta.description"] = $sanitizer->sanitize($_POST['admin_description'],'unicode'); 
				}
				
				if(isset($_POST['socialmedia_option1'])) {
					$json[0]["site.socialmedia.option1"] = $sanitizer->sanitize($_POST['socialmedia_option1'],'url'); 
				}
				if(isset($_POST['socialmedia_option2'])) {
					$json[0]["site.socialmedia.option2"] = $sanitizer->sanitize($_POST['socialmedia_option2'],'url');
				}
				if(isset($_POST['socialmedia_option3'])) {
					$json[0]["site.socialmedia.option3"] = $sanitizer->sanitize($_POST['socialmedia_option3'],'url');
				}
				if(isset($_POST['socialmedia_option4'])) {
					$json[0]["site.socialmedia.option4"] = $sanitizer->sanitize($_POST['socialmedia_option4'],'url');
				}
				if(isset($_POST['socialmedia_option5'])) {
					$json[0]["site.socialmedia.option5"] = $sanitizer->sanitize($_POST['socialmedia_option5'],'url');
				}
		
				$storagecontainer->storedata($keys,$json);

				/***
				/* Store PayPal JSON configuration.
				*/
				
				$keys_paypal = 'server/config/paypal.json';
				$backups->backup($keys_paypal);
				$json_paypal = $shop->json->load_json($keys_paypal); 		
				$json_paypal[0]["paypal.domain"] = $sanitizer->sanitize($_POST['admin_website'],'url');		
				$json_paypal[0]["paypal.email"] = $sanitizer->sanitize($_POST['admin_paypal_email'],'url');
				
				$storagecontainer->storedata($keys_paypal,$json_paypal);

		/***
		/* If successful, show the following message.
		*/
				
		echo '<div class="installer-message-success">';
		echo 'OpenShop was installed and should function correctly! If not, please read the manual on Github: https://github.com/flaneurette/OpenShop'. PHP_EOL;
		echo 'Please delete the install.php file, or <a href="install.php?delete='.$sanitizer->sanitize($nonce,'alphanum').'">click here.</a> to let OpenShop do it for you'. PHP_EOL;
		echo '</div>';	
		
	} elseif(isset($_POST['setup-complete']) == 1) {
		
		echo '<div class="installer-message-success">';
		echo 'OpenShop was installed and should function correctly! If not, please read the manual on Github: https://github.com/flaneurette/OpenShop'. PHP_EOL;
		echo 'Please delete the install.php file, or <a href="install.php?delete='.$sanitizer->sanitize($nonce,'alphanum').'">click here.</a> to let OpenShop do it for you'. PHP_EOL;
		echo '</div>';		

		// make files non-writeable again.

		chmod("administration/.htpasswd",0755);
		chmod("administration/session.ses",0755);
		chmod("administration/.htaccess",0644);
		chmod(".htaccess",0644);
		chmod("server/config/paypal.json",0755);
		chmod("server/config/paypal.json",0755);
	
	} else {
		
// End of Installer

?>

<h1>Setup OpenShop</h1>
<div>
	<?php echo "All requirements were met. Continue to configure OpenShop";?>
</div>
<hr />
		<div id="ts-shop-cart-form">
					<form name="" action="" method="post">
						<input name="setup" value="1" type="hidden">
						<input name="nonce" value="<?php echo $sanitizer->sanitize($nonce,'alphanum');?>" type="hidden">
						Shop title: <input name="admin_title" value="OpenShop webshop" type="text"> 
						Shop description: <input name="admin_description" value="OpenShop webshop is cool!" type="text"> 
						Website: <input name="admin_website" value="https://<?php echo $host;?>" type="text"> 
						Shop folder name /shop/ (without slashes) <input name="shop_folder" value="<?php echo $sanitizer->sanitize($shopfolder,'alphanum');?>" type="text" alt="Without slashes" title="Without slashes">
						Website e-mail: <input name="admin_website_email" value="info@website.com" type="text">
						<hr />
						Theme: <select name="theme"> 
						<option value="default">White Theme</option>
						<option value="dark">Dark Theme</option>
						</select>
						<hr />
						Free shipping above: <br /><input name="freeshipping" value="50.00" type="text" size="5" />
						<hr />
						Currency:
						<select name="admin_currency">
						<?php
						
							$html = "";
							
							$currencies = $shop->json->load_json("server/config/currencies.conf.json");
							
								if($currencies !== null) {
									$i=0;
									foreach($currencies[0] as $key => $value)
									{
										$html .= "<option value=\"".$sanitizer->sanitize($key,'num')."\">".$currencies[0][$i]['sign']."</option>";
										$i++;
									}		
								}
								echo $html;
						?>
						</select>
						<hr />
						Admin Username: <input name="admin_username" value="" type="text">
						Admin Password: <input name="admin_password" value="" type="text">
						Admin E-mail: <input name="admin_email" value="" type="text">
						<hr />
						<strong>Security.</strong> Encrypt e-mail address? 
						<select name="admin_encryption">
							<option value="2">No</option>
							<option value="1">Yes</option>
						</select> <sup>(if NO, it will be visible to everyone)</sup>
						<hr />
						Admin IP: <input name="admin_ip" value="<?php echo  $sanitizer->sanitize($_SERVER['REMOTE_ADDR'],'table');?>" type="text">
						<hr />
						<div id="paypal">PayPal e-mail (to accept payments on):</div> <input id="paypal-email" name="admin_paypal_email" value="" type="text">
						<hr />
						
						Social media accounts. i.e. https://www.twitter.com/tinywebshop
						<input name="socialmedia_option1" value="" type="text"> 
						<input name="socialmedia_option2" value="" type="text"> 
						<input name="socialmedia_option3" value="" type="text"> 
						<input name="socialmedia_option4" value="" type="text"> 
						<input name="socialmedia_option5" value="" type="text"> 
						
						<hr />
						<input type="submit" value="Setup OpenShop &raquo;">
						<br />
						<hr />
					</form>
				</div>
<?php
}
?>
</body>
</html>
