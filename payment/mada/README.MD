# Development guide for: Mada Payment Gateway

By default, OpenShop only has PayPal as selected payment gateway. In this way, we only have to maintain our own codebase and are not responsible for 3rd party software and payment gateways. Below are code examples on how to integrate Mada Frames. OpenShop could do it for you, for a small fee if you are unable to make it work. If so, then please contact us via: https://www.flaneurette.work

Since we do not (and cannot) maintain documentation of 3rd party software, always reference the orginal documentation before integrating it into OpenShop. OpenShop is not responsible for any security-related issues arising through the use of 3rd party gateways.

https://docs.checkout.com/integrate/frames

```
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Frames v2</title>
  <style>*,*::after,*::before{box-sizing:border-box}html{padding:1rem;background-color:#FFF;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif}#payment-form{width:31.5rem;margin:0 auto}iframe{width:100%}.one-liner{display:flex;flex-direction:column}#pay-button{border:none;border-radius:3px;color:#FFF;font-weight:500;height:40px;width:100%;background-color:#13395E;box-shadow:0 1px 3px 0 rgba(19,57,94,0.4)}#pay-button:active{background-color:#0B2A49;box-shadow:0 1px 3px 0 rgba(19,57,94,0.4)}#pay-button:hover{background-color:#15406B;box-shadow:0 2px 5px 0 rgba(19,57,94,0.4)}#pay-button:disabled{background-color:#697887;box-shadow:none}#pay-button:not(:disabled){cursor:pointer}.card-frame{border:solid 1px #13395E;border-radius:3px;width:100%;margin-bottom:8px;height:40px;box-shadow:0 1px 3px 0 rgba(19,57,94,0.2)}.card-frame.frame--rendered{opacity:1}.card-frame.frame--rendered.frame--focus{border:solid 1px #13395E;box-shadow:0 2px 5px 0 rgba(19,57,94,0.15)}.card-frame.frame--rendered.frame--invalid{border:solid 1px #D96830;box-shadow:0 2px 5px 0 rgba(217,104,48,0.15)}.success-payment-message{color:#13395E;line-height:1.4}.token{color:#b35e14;font-size:0.9rem;font-family:monospace}@media screen and (min-width: 31rem){.one-liner{flex-direction:row}.card-frame{width:318px;margin-bottom:0}#pay-button{width:175px;margin-left:8px}}</style>
</head>

<body>

  <!-- add frames script -->
  <script src="https://cdn.checkout.com/js/framesv2.min.js"></script>

  <form id="payment-form" method="POST" action="https://merchant.com/charge-card">
    <div class="one-liner">
      <div class="card-frame">
        <!-- form will be added here -->
      </div>
      <!-- add submit button -->  
      <button id="pay-button" disabled>
        PAY GBP 24.99
      </button>
    </div>
    <p class="success-payment-message"></p>
  </form>

  <script>
    var payButton = document.getElementById("pay-button");
    var form = document.getElementById("payment-form");

    Frames.init("pk_test_6ff46046-30af-41d9-bf58-929022d2cd14");

    Frames.addEventHandler(
      Frames.Events.CARD_VALIDATION_CHANGED,
      function (event) {
        console.log("CARD_VALIDATION_CHANGED: %o", event);

        payButton.disabled = !Frames.isCardValid();
      }
    );

    Frames.addEventHandler(
      Frames.Events.CARD_TOKENIZED,
      function (event) {
        var el = document.querySelector(".success-payment-message");
        el.innerHTML = "Card tokenization completed<br>" +
          "Your card token is: <span class=\"token\">" + event.token + "</span>";
      }
    );

    form.addEventListener("submit", function (event) {
 	  payButton.disabled = true // disables pay button once submitted
      event.preventDefault();
      Frames.submitCard();
    });
  </script>

</body>

</html>
```
