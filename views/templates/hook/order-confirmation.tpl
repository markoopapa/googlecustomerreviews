<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": {$merchant_id|escape:'javascript':'UTF-8'},
          "order_id": "{$order_id|escape:'javascript':'UTF-8'}",
          "email": "{$email|escape:'javascript':'UTF-8'}",
          "delivery_country": "{$country_code|escape:'javascript':'UTF-8'}",
          "estimated_delivery_date": "{$delivery_date|escape:'javascript':'UTF-8'}",
          "products": {$products_gtin nofilter}
        });
    });
  }
</script>
