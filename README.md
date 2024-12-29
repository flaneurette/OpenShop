# OpenShop

Is a barebones opensource webshop software, written in PHP and flat file JSON and CSV.

# First steps on working with OpenShop 
If installed, go to the administration folder and there you will be able to upload .CSV files. The CSV files included in /inventory/csv/ are examples of a shop. Normally, the only CSV files that need to be edited are: 

- server/config/site.conf.csv
- inventory/shop.csv
- inventory/categories.csv 
- inventory/subcategories.csv. 

These CSV files can be kept on your computer, and uploaded through the administration screen. Most shop settings can be edited through these CSV files. All products are stored inside *shop.csv* and linked to categories and subcategories, so at a minimal you need to have to edit these 3 CSV files. For more, such as Paypal settings, read the entire README.
In OpenShop it is possible to change the column order of the CSV, in any way you desire. It would be wise to not delete any columns, as then OpenShop might not be able to read the shop corectly, although it may be able to read products. OpenShop accepts only text, integers, floats and uri locators in CSV fields. Embeded images are not supported. 
The CSV files can be downloaded through the administration panel, by visiting the /downloads/ tab.

# Installing

OpenShop can be installed via two methods, composer and manual installation.

### Composer: 

	composer require flaneurette/open-shop:dev-main
- Make sure that the shop folder is properly chowned under the rights of apache or php, to prevent installer failures: 

	`chown -R www-data:www-data shopfolder`
- Run install.php in your browser and follow directions.

### Manual:

- Clone or download the zip and upload them to a folder on your server.
- Make sure that the shop folder is properly chowned under the rights of apache or php, to prevent installer failures: 

	`chown -R www-data:www-data shopfolder`
- Run install.php in your browser and follow directions.

OpenShop checks all requirements and if satisfied, the package should be installed seamlessly. If not, it will prompt for further action.

# Requirements
- PHP 5.6+ (the higher the better) PHP 8 is supported!
- PHP extensions (the installer will check on them and prompt for missing extensions)
- Sendmail or Postfix
- Server module: (Apache) mod_rewrite for .htaccess functionalities. The .htaccess is written dynamically upon installing. By default, a standard .htaccess is present.
- The /shop/ and the /administration/ folder needs to be owned and writeable by the server (In Apache for example, the owner should be www-data. If not, it needs to be manually chowned through a terminal.) otherwise, session data and the .htaccess and .htpasswd, and the inventory files cannot be written.
- Server Module: mod_access_compat, Available in Apache HTTP Server 2.3. (in order to protect directories) or equalivant on other architectures, however this might require additional changes to the .htaccess. OpenShop assumes Apache, or apache derivates and modules.

# Optional
	These server modules are optional and OpenShop will work without them. However, it would be useful to have these server modules installed.
	- Server module: version_module: required to secure folders, if not installed then directories maybe visibile beyond server scope.
	- Server module: mod_deflate & mod_filter.c: to filter, cache and compress: css, javascripts, csv & json.
	- Server module: mod_headers: for extra server-side security measures.

# Ports
OpenShop requires the following minimal open ports: 80, 443 and 25. 
It would be wise to install UFW for port control:

	apt-get install ufw
	ufw status numbered
 	ufw allow 22
	ufw allow 25
	ufw allow 80
	ufw allow 443
	ufw enable
