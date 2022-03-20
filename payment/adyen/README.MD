# Adyen Payment Gateway

By default, OpenShop only has PayPal as selected payment gateway. In this way, we only have to maintain our own codebase and are not responsible for 3rd party software and payment gateways. Below are code examples on how to integrate Adyen. OpenShop could do it for you, for a small fee if you are unable to make it work. If so, then please contact us via: https://www.flaneurette.work

Since we do not (and cannot) maintain documentation of 3rd party software, always reference the orginal documentation before integrating it into OpenShop. OpenShop is not responsible for any security-related issues arising through the use of 3rd party gateways.


https://docs.adyen.com/online-payments/api-only?tab=codeBlockmethods_request_4

```
// Set your X-API-KEY with the API key from the Customer Area.
$client = new \Adyen\Client();
$client->setEnvironment(\Adyen\Environment::TEST);
$client->setXApiKey("YOUR_X-API-KEY");
$service = new \Adyen\Service\Checkout($client);
 
$params = array(
    "countryCode" => "NL",
    "shopperLocale" => "nl-NL",
    "amount" => array(
        "currency" => "EUR",
        "value" => 1000
    ),
    "channel" => "Web",
    "merchantAccount" => "YOUR_MERCHANT_ACCOUNT"
);
$result = $service->paymentMethods($params);

```
