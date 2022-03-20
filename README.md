
# OpenShop

Is a barebones opensource webshop software, written in PHP and flat file JSON. 

# Table of Contents
<details open>
<summary><b>(click to expand or hide)</b></summary>

1. [Installing](#Installing)
1. [Requirements](#Requirements)
1. [Optional](#Optional)
1. [Ports](#Ports)
1. [Permissions](#Permissions)
1. [Payment configuration](#Payment-configuration)
1. [Directory security](#Directory-security)
1. [Sending mail](#Sending-mail)
1. [Testing a fake order mail](#Testing-a-fake-order-mail)
1. [Importing exporting](#Importing-exporting)
1. [Shipping](#Shipping)
1. [Tax](#Tax)
1. [Payment-types](#Payment-types)
1. [Currencies](#Currencies)
1. [Storage](#Storage)
1. [Encryption](#Encryption)
1. [Backups](#Backups)
1. [SEO](#SEO)
1. [Products](#Products)
1. [Style and themes](#Style-and-themes)
1. [Logging](#Logging)
1. [CSV files of interest](#CSV-files-of-interest)
1. [JSON values and parameters](#JSON-values-and-parameters)
1. [Custom programming](#Custom-programming)
1. [Loading a JSON file](#Loading-a-JSON-file)
1. [Product list demo](#Product-list-demo)
1. [Security Policy](#Security-Policy)
1. [Reporting a Vulnerability](#Reporting-a-Vulnerability)
</details>

<a name="First-steps-on-working-with-OpenShop"></a> 

# First steps on working with OpenShop 
If installed, go to the administration folder and there you will be able to upload .CSV files. The CSV files included in /inventory/csv/ are examples of a shop. Normally, the only CSV files that need to be edited are: 

- server/config/site.conf.csv
- inventory/shop.csv
- inventory/categories.csv 
- inventory/subcategories.csv. 

These CSV files can be kept on your computer, and uploaded through the administration screen. Most shop settings can be edited through these CSV files. All products are stored inside *shop.csv* and linked to categories and subcategories, so at a minimal you need to have to edit these 3 CSV files. For more, such as Paypal settings, read the entire README.

In OpenShop it is possible to change the column order of the CSV, in any way you desire. It would be wise to not delete any columns, as then OpenShop might not be able to read the shop corectly, although it may be able to read products. OpenShop accepts only text, integers, floats and uri locators in CSV fields. Embeded images are not supported. 

The CSV files can be downloaded through the administration panel, by visiting the /downloads/ tab.

<a name="Installing"></a>

# Installing
- Clone or download the zip and upload them to a folder on your server.
- Make sure that the shop folder is properly chowned under the rights of apache or php, to prevent installer failures: 

	`chown -R www-data:www-data shopfolder`
- Run install.php in your browser and follow directions.

OpenShop checks all requirements and if satisfied, the package should be installed seamlessly. If not, it will prompt for further action.

<a name="Requirements"></a>

# Requirements
- PHP 5.6+ (the higher the better) PHP 8 is supported, but might not be optimized.
- PHP extensions (the installer will check on them and prompt for missing extensions)
- Sendmail or Postfix
- Server module: (Apache) mod_rewrite for .htaccess functionalities. The .htaccess is written dynamically upon installing. By default, a standard .htaccess is present.
- The /shop/ and the /administration/ folder needs to be owned and writeable by the server (In Apache for example, the owner should be www-data. If not, it needs to be manually chowned through a terminal.) otherwise, session data and the .htaccess and .htpasswd, and the inventory files cannot be written.
- Server Module: mod_access_compat, Available in Apache HTTP Server 2.3. (in order to protect directories) or equalivant on other architectures, however this might require additional changes to the .htaccess. OpenShop assumes Apache, or apache derivates and modules.

## <a name="Optional"></a>
# Optional
	These server modules are optional and OpenShop will work without them. However, it would be useful to have these server modules installed.
	- Server module: version_module: required to secure folders, if not installed then directories maybe visibile beyond server scope.
	- Server module: mod_deflate & mod_filter.c: to filter, cache and compress: css, javascripts, csv & json.
	- Server module: mod_headers: for extra server-side security measures.

<a name="Ports"></a>

# Ports
OpenShop requires the following minimal open ports: 80, 443 and 25. 
It would be wise to install UFW for port control:

	apt-get install ufw
	ufw status numbered
	ufw allow 25
	ufw allow 80
	ufw allow 443
	ufw enable

<a name="Permissions"></a>

# Permissions
The following files need to be writeable by the installer. The installer attempts to chmod the files *automatically*, if this fails, then file rights need to be manually assigned as follows. The installer tries to obtain the greatest possible permissions: 0777 to prevent setup failures.

- administration/.htpasswd 	: 0777
- administration/session.ses 	: 0777
- administration/.htaccess 	: 0777
- .htaccess 			: 0777
- payment/paypal/paypal.json 	: 0777
- inventory/site.json 		: 0777
- /inventory/backups/ 		: 0770
- resources/images/products/ 	: 0777
- resources/images/category/ 	: 0777

Remember to change permissions back to 0755 after the installer has run, except for the .htaccess which needs to have a 0644 file permisson. (remember that some applications hide the .htaccess) Again, the installer itself attempts to do this *automatically* but it would be wise to check manually. The installer will try to give a notice if the chmodding fails. To prevent dual installations, and for security, session data is written to */administration/session.ses*, each time the installer runs. If installation fails, this file needs to be emptied manually.

The administration panel requires the following folders to be writable contineously:

- inventory/ 			: 0777 or 0755
- inventory/csv/ 		: 0777 or 0755

Depening on your situation, it would be wise to start with the lowest permissions, however, the adminsitration panel might require more permissions. OpenShop will try to chmod the .csv and .json files when a change is made, and chmods the files automatically back to 0755. If you cannot upload files, chmod these folders to 0777 manually to prepare OpenShop to make changes automatically.

<a name="Payment-configuration"></a>

# Payment configuration
For ease of use, the installer writes your PayPal e-mail address to the file: /payment/paypal/paypal.json. However, it would be wise to check manually if your e-mail was written to it. If not, then PayPal might not function correctly. 

<a name="Directory-security"></a>

# Directory security
If the Server Modules: mod_access_compat and version_module are available and installed, then OpenShop has more ways to secure itself. For example, OpenShop adds the below rules inside the main *.htaccess*. With these added rules, it is not possible for others except for the server to access the: json, csv, log and backup files. Most often, these modules come with Apache, and therefore there is no need to add them, as the installer adds these rules automatically.

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

<a name="Sending-mail"></a>

# Sending mail

OpenShop has a basic contact page, which is located at: /mail/index.php. which can be added to the nagivation panel, if so desired.

By default, OpenShop uses the e-mail address that was given upon installation, which is stored inside *site.json* and is either readable or encrypted. If no e-mail address is listed, OpenShop uses a default e-mail address that is hardcoded inside the class below. If no e-mail is given, then this class needs to be edited in order for OpenShop to send e-mails. OpenShop uses a custom built mail class, which can be found at: /resources/php/class.SecureMail.php. There are 3 constants that are *required* to be modified in order to prevent SPAM false positives. To default, it uses localhost, as this could work. However it would be wise to check if this works on your instance.

	const DOMAIN			= 'localhost'; // Domain this script is hosted on.
	const SERVERADDR		= 'server <admin@localhost>'; // Server e-mail address.
	const DEFAULTTO			= 'admin@localhost'; // default "to" e-mail address when address has not been provided.

Because of the nature of PHP and sendmail, it is possible that some mail might be flagged as SPAM if the email, domain and serveraddress is not correct. In order to prevent this, do a test order to see whether mail is labelled as SPAM. OpenShop has a contactform inside the /mail/ folder.

<a name="Testing-a-fake-order-mail"></a>

# Testing a fake order & mail

To test a fake order and mail function, run this script in the browser:

	/payment/paid/paid_test.php 
	
This script places a fake order, to see whether you can receive an order to your e-mail. Remember to delete this file after testing.

<a name="Importing-exporting"></a>

# Importing & exporting
OpenShop made the file structure in such a way that it will be able to import/export data from other shoppingcarts, such as Shopify and in a later stage Magento and other popular shoppingcarts. However, this functionality is currently under development.

<a name="Shipping"></a>

# Shipping
OpenShop has a basic flat fee shipping file, *shipping.csv* where each country has a flat fee shipping price. Each product can also have an individual shipping price. The cart will automatically calculate the total shipping amount. If the shipping price exceeds the flat fee shipping price, as listed in shipping.csv, then it will take the flat fee price. If the total purchase amount exceeds the free shipping option, then shipping will be free. The free shipping amount can be edited in site.csv, or site.json and uploaded through the administration screen.

To edit the *free shipping* option, edit: *site.conf.csv* by default it ships an order for free if the purchase amount is more than 50, in the currency your shop works in.

<a name="Tax"></a>

# Tax
Tax added values can be added to every single product individually, by editing shop.csv and the product.tax column. If no value is given, then OpenShop defaults by reading the tax.conf.json file, which holds tax values for each country. This process is automated. Tax.conf.csv can be edited and uploaded, if you want to change the tax value of a country. Currently, OpenShop has default values of 10%.

<a name="Payment-types"></a>

# Payment types

By default, the free version, only accepts PayPal payments (including credit cards). 

Long term planned integration (with a future paid plan) will support more Payment Gateways:

Bancontact, KNET, CitrusPay, Mada, QPAY, EPS, Giropay, iDEAL, Bitcoin: Coingate, Poli, Przelewy24, Sofort, Boleto Bancário, Fawry, Multibanco, OXXO, Pago Fácil, Rapipago, Via Baloto, ACH, SEPA Direct Debit, Klarna, Bancontact, KNET, Mada, QPAY, Stripe, Alipay, Apple Pay, BenefitPay, Google Pay and PayPal.

<a name="Currencies"></a>

# Currencies
OpenShop supports 36 different currencies, including Bitcoin.

<a name="Storage"></a>

# Storage
OpenShop uses JSON to store data. The benefit of a flat file database, is that it works on all platforms and operating systems, and there is no need to install database software. JSON can be easely converted back and forth into CSV and excel, making it easy for a shop owner to update the shop, without having to login into a complex portal or a server-side administration screen. 

<a name="Encryption"></a>

# Encryption
OpenShop has a reasonably safe encryption method to encrypt the shop data, namely AES 256. Since it does not store user-details, the encryption is disabled by default. All user details are not stored, but e-mailed to the shop owner. It is possible to store the details and thus encrypt it through OpenShop, but that is up to the shop owner.

<a name="Backups"></a>

# Backups
OpenShop makes (real-time) automatic backups of the JSON database each time a product is added, changed or removed.

<a name="SEO"></a>

# SEO
OpenShop creates SEO friendly URL's of all products.

<a name="Products"></a>

# Products
The file shop.csv|.json, contains all the products. All products require to have a unique product identifier, or productId. This is numeric, for example: 10000234. It is advised to have a large product identifier, in this way one can add more products. Without productId, or duplicate productIds, the shop might not work properly. It is best to start with: 10000001. The items can be added sequentially, as OpenShop sorts the products from new to old, through a array_reverse, automatically.

To place a product inside a category or subcategory, the subsequent csv files need to be edited or viewed to obtain the categoryId or subcategoryId. In this way, products are linked. As an example, OpenShop has a basic list of demo products and (sub) categories, which makes it easy to see how OpenShop works. 

- Future of product modification.
In a future version of OpenShop, all CSV files will be linked into a single Excel document, making it even more practical. In this way the whole shop can be modified from a single excel document.

<a name="Style-and-themes"></a>

# Style and themes

Since version 2.73, OpenShop has the option to change themes. Themes are changed by editting the site.csv, and the subsequent theme folder.

- Default: /resources/style/themes/default/
- Empty: /resources/style/themes/empty/
- Dark: /resources/style/themes/dark/

To change the colors and thematical style of the shop these CSS files can be modified:

Two main stylesheets for the webshop:
- /resources/style/themes/default/css.css
- /resources/style/themes/default/style.css

Pages stylesheet for articles, pages and blogs:
- /resources/style/themes/default/pages.css

Administration stylesheet:
- /resources/style/themes/default/admin.css

Reset stylesheet, needs not to be modified:
- /resources/style/themes/default/reset.css

<a name="Logging"></a>

# Logging
OpenShop emulates Apache logging. By default, OpenShop logs most folders and the logging locations can be modified by editting the server/config/csv/site.conf.csv or JSON. The file log.log contains basic rows of user information that accessed the shop. This file could be parsed further, if so required. There is a hard logging limit of about 3 to 5MB, before the log is emptied out automatically. The data logged is User-Agent, IP, date and time of access, querystring, request method and which location was viewed. OpenShop does not place any cookies, as logging is done server-side. Logging is only done for security reasons. OpenShop does not track visitors. Logging can be turned off by editing server/config/csv/site.conf.csv and set site.logging = 0 instead of 1. OpenShop does not set cookies, however cookies can be site through the site.conf.csv or when external tracking javascript is added. If this is the case, a popup needs to be show in line with EU rules. By default, OpenShop does not show a popup since it does not track visitors.

<a name="CSV-files-of-interest"></a>

# CSV files of interest

As of version 3.2, the administration panel has a downloads page where the CSV can be downloaded.

Most CSV and JSON files can be edited, and it is advised to do so for OpenShop to work properly. A few are listed below that are required to be edited:

- server/config/csv/site.conf.csv

This file contains site wide settings, such as meta tags, logo, javascripts and stylesheets.

- Navigation.csv

OpenShop has a preset navigation which can be changed according to one's wishes. Currently, OpenShop does not support dropdown navigation only a horizontal navigation bar. This might change in future versions. Navigation supports relative paths only. 

- Shop.csv

This file contains all shop products. By default, preset shop products are loaded and displayed. Since all products are stored inside this CSV, this file can become quite large depending on the amount of products. For now, there is no limited on the amount of products but it would be wise to be economical. OpenShop is designed to carry a 100 to 500 products, but it probably can hold much more. If possible, one could add variants in a single csv row by making use of the variant parameters.

- Categories.csv

This file contains the categories that are loaded in the left-side navigation bar.

- Subcategories.csv

This file contains the subcategories that are loaded in the left-side navigation bar and displayed under each particular category.

- payment/paypal/paypal.csv

This file contains the PayPal information. Upon installation, the PayPal e-mailadress will be asked and is written to this file. Further information could be changed manually, such as return pages and cancellation pages.

- Currencies.csv

This file contains all currencies, normally this file needs not to be changed.

- Articles.csv

This file contains all the articles, if written and displayed under the navigation of articles.

- Blog.csv

This file contains all the weblogs, if written and displayed under the navigation of weblog or blog.

<a name="JSON-values-and-parameters"></a>

# JSON values and parameters

An upload page is used to convert each CSV to JSON, located in /administration/. In this way, only the CSV files have to be edited and the shop will be updated automatically. Obviously, it is also possible to upload each JSON and CSV file through either SCP, FTP or command line, rendering the upload page expendable. Just remember that OpenShop does not read the CSV files, only JSON files. The CSV files are used offline and uploaded to be converted to JSON.

<a name="Shop "></a>
# Shop 
```
product.id		- ID of the product, usually a large number such as: 1000000022. The leading 1 is important. Required.
product.price		- Price of product. i.e. 9.99 periods are recommended. Required.
shipping.fixed.price	- Fixed price of shipping. If empty, the cart will use shipping.csv to calculate flat fee shipping prices.
product.stock		- An ammount of stock. i.e. 10. Required.
product.tax		- Tax value of a single product. i.e. 10%, optional. If empty, OpenShop takes the country tax as found in tax.csv
product.status		- Either 1 for ONLINE, or 0 for OFFLINE. Required.
product.title		- Description of the product. Required.
product.description	- Description of the product. Required.
product.category	- Category name, as listed in categories.csv. Required.
product.category.sub	- Category Sub name, as listed in subcategories.csv. Required.
product.image		- Image of the main product
product.catno		- Catalogue number, for your own reference, however it is shown.
product.format		- Optional, for your own reference, however it is shown.
product.type 		- Optional, for your own reference, however it is shown.
product.weight		- Optional, for your own reference, however it is shown.
product.condition	- Optional, for your own reference, however it is shown.
product.ean		- EAN code of a product
product.sku		- SKU code of a product
product.vendor		- Vendor of a product
product.margin		- Your margin of a product, for your own reference.
product.price_min	- Unused, for now.
product.price_max	- Unused, for now.
product.price_varies	- Unused, for now.
product.date		- Unused, for your own reference.
product.url		- Url to a product if it is a featured product.
product.tags		- Comma separated values that are used to generate product tags, and meta-tags
product.images		- Comma separated values of images, relative paths are recommended.
product.featured	- Add 1 for having an image featured.
product.featured.location - The name of category where image is featured: i.e. index.
product.featured_carousel - Unused.
product.featured.image	- The url of the featured image.
product.content		- Unused, for now.
product.variants	- Unused, for now.
product.available	- Unused, for now.
product.selected_variant - Unused, for now.
product.collections	- Unused, for now.
product.options		- Unused, for now.
socialmedia		- Either 1 for yes, empty or 0 for no social media sharing option.
variant.title1		- Comma separated values of variants: i.e. blue,red,purple 			
variant.title2		- idem.
variant.title3		- idem.
variant.image1		- Unused, for now.
variant.image2		- Unused, for now.
variant.image3		- Unused, for now.
variant.option1		- Comma separated values of variants: i.e. blue,red,purple 
variant.option2		- idem.
variant.option3		- idem.
variant.price1		- Comma separated price values of variant options: i.e. 12,5,23 
variant.price2		- Unused, for now.
variant.price3		- Unused, for now.
shipping.locations	- comma separated value of a country. For example: US,DE,GB. If empty, it ships to all countries.

Unused: variables that are placeholders for future compatibility with other shopping cart systems, for ease of compatibility, export or switching to OpenShop.

```
<a name="Site"></a>
# Site
```
site.url			- URL of main website. i.e. https://example.com
site.domain			- URL of main website. i.e. https://www.example.com
site.canonical			- Shopping folder where shop is installed. i.e. shop or shopping
site.cdn			- Content delivery path, currently unused.
site.charset			- Character set of the shop. Recommended to set to UTF-8
site.title			- Title of the webshop
site.description		- Description of the webshop
site.logo			- i.e. resources/images/logo.png
site.icon			- Favicon. i.e. resources/images/favicon.ico
site.status			- either: online, offline or vacation.
site.updated			- optional, for search engines or personal reference
site.currency			- Currency ID as set in currency.json. 0 = Dollar. 
site.freeshipping		- Amount over which shipping will be free. i.e. 50.00
site.meta.title			- Title of shop, meta.
site.meta.description		- Description of shop, meta.
site.meta.tags			- Tags, meta, comma separated.
site.meta.name.1		- Custom meta name 1
site.meta.name.2		- Custom meta name 2
site.meta.name.3		- Custom meta name 3
site.meta.name.4		- Custom meta name 4
site.meta.value.1		- Custom meta 1 value
site.meta.value.2		- Custom meta 2 value
site.meta.value.3		- Custom meta 3 value
site.meta.value.4		- Custom meta 4 value
site.tags			- Custom tags, comma separated.
site.socialmedia.option1	- URL to social media website
site.socialmedia.option2	- URL to social media website
site.socialmedia.option3	- URL to social media website
site.socialmedia.option4	- URL to social media website
site.socialmedia.option5	- URL to social media website
site.javascript			- Custom JavaScript URI.
site.ext.javascript		- External JavaScript URL.
site.stylesheet.reset 		- Default style or Theme "resources/style/themes/default/reset.css",
site.stylesheet1 		- Default style or Theme "resources/style/themes/default/css.css",
site.stylesheet2 		- Default style or Theme "resources/style/themes/default/style.css",
site.stylesheet3 		- Default style or Theme "resources/style/themes/default/pages.css",
site.ext.stylesheet 		- External stylesheet URL 
site.google.tags		- Google tags or verification
site.cookie.name.1 		- Custom hardcoded cookie name
site.cookie.name.2 		- Custom hardcoded cookie name
site.cookie.name.3 		- Custom hardcoded cookie name
site.cookie.value.1 		- Custom hardcoded cookie value
site.cookie.value.2 		- Custom hardcoded cookie value
site.cookie.value.3 		- Custom hardcoded cookie value
site.analytics 			- Analytics url.
site.payment.gateways		- Array of possible payment gateways.
```		
<a name="Shop.conf "></a>
# Shop.conf 
```
products.orientation: thumb|list - unused
products.alt.tags		 - shows alt tags on products, either 1 or 0.
products.scene.type		 - unused
products.row.count		 - unused
products.per.page		 - maximum products per page
products.per.cat		 - maximum products per category
products.quick.cart		 - displays a quick cart button at each product. either 1 or 0.
products.carousel		 - unused
products.search			 - displays a searchbar. Either 1 or 0.
```

Since OpenShop is a webshop software there is limited support for blogs, articles and pages. OpenShop provides basic functionalities for the afformentioned items and only displays the basics. However, it is possible to expand on these items. Unused items and placeholders could be expanded upon, if needed. However, this requires custom coding.

<a name="Pages:"></a>
# Pages:
```
page.id				- id, numerical. i.e. 100000001, depending on the number of pages.
page.title			- a one line title of the page
page.description		- short description of page
page.short.text			- short description of page
page.long.text			- the page text
page.url			- full URI to the page
page.tags			- comma separated values of tags
page.author			- the author of the page
page.handle			- the handle of the page
page.image.header 		- main image, full URI to the image. i.e. https://shop.tld/page/image.png
page.image.main			- unused
page.status			- either 1 or 0
page.archived			- unused
page.created			- unused
page.published			- date of publication
page.updated			- unused
```
<a name="Blog:"></a>
# Blog:
```
blog.id				- id, numerical. i.e. 100000001, depending on the number of blogs.
blog.title			- a one line title of the blog
blog.description		- short description of blog
blog.short.text			- short description of blog
blog.long.text			- the blog text
blog.url			- full URI to the blog
blog.tags			- comma separated values of tags
blog.author			- the author of the blog
blog.handle			- the handle of the blog
blog.image.header 		- main image, full URI to the image. i.e. https://shop.tld/blog/image.png
blog.image.main			- unused
blog.status			- either 1 or 0
blog.archived			- unused
blog.created			- unused
blog.published			- date of publication
blog.updated			- unused
```   
<a name="Articles:"></a>
# Articles:
``` 
article.short.text		- short description of article
article.long.text		- the article text
article.url			- full URI to the article
article.tags			- comma separated values of tags
article.author			- the author of the article
article.handle			- the handle of the article
article.image.header 		- main image, full URI to the image. i.e. https://shop.tld/article/image.png
article.image.main		- unused
article.status			- either 1 or 0
article.archived		- unused
article.created			- unused
article.published		- date of publication
article.updated			- unused
```

<a name="Custom-programming"></a>

# Custom programming: Product list minimal demo (for programmers):

- Function: getproducts() takes params: method, category, string, limit, pagination and token.

	$shop->getproducts($method,$category,$string=false,$limit=false,$page=false,$token) 

- Method: either list or group. 
- Category: the shop category. If empty, it shows all categories.

```
include("resources/php/header.inc.php");
include("class.Shop.php");
	
$shop     = new Shop();
$products = $shop->getproducts('list','index');
		
echo $products[1];
			
```
<a name="Loading-a-JSON-file"></a>

# Loading a JSON file
```
include("resources/php/header.inc.php");
include("class.Shop.php");

$shop     = new Shop();

$shopconf = $shop->load_json("server/config/shop.conf.json");

foreach($shopconf as $row)
{
	foreach($row as $key => $value)
	{
	echo "<b>".$key."</b>".':'.$value.'<br>';
	}
}
	
```
<a name="Product-list-demo"></a>

# Product list demo


<a name="Security-Policy"></a>
# Security Policy

OpenShop expects that users install the latest version of OpenShop. The supported versions for security updates are:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

<a name="Reporting-a-Vulnerability"></a>

# Reporting a Vulnerability

Security issues can be reported by opening an issue.

