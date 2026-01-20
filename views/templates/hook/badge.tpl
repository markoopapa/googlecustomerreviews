{if $badge_pos != 'USER_DEFINED'}
<script id='merchantWidgetScript' src="https://www.gstatic.com/shopping/merchant/merchantwidget.js" defer></script>

<script>
  merchantWidgetScript.addEventListener('load', function () {
    merchantwidget.start({
         "merchant_id": {$merchant_id|escape:'javascript':'UTF-8'},
         "position": "{$badge_pos|escape:'javascript':'UTF-8'}"
    });
  });
</script>
{/if}
