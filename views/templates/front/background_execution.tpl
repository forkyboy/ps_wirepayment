{block name='page_title'}{$background_title|escape:'html':'UTF-8'}{/block}

<section id="ps_wirepayment_background" class="box">
  <p>{$background_intro|escape:'html':'UTF-8'}</p>

  <div class="ps-wirepayment-details">
    <p><strong>{l s='Amount to pay:' mod='ps_wirepayment'} {$total_to_pay|escape:'html':'UTF-8'}</strong></p>

    {if $bankwire_owner}
      <p><strong>{l s='Account owner:' mod='ps_wirepayment'}</strong><br>{$bankwire_owner|escape:'html':'UTF-8'}</p>
    {/if}

    {if $bankwire_details}
      <p><strong>{l s='Bank details:' mod='ps_wirepayment'}</strong><br>{$bankwire_details nofilter}</p>
    {/if}

    {if $bankwire_address}
      <p><strong>{l s='Bank address:' mod='ps_wirepayment'}</strong><br>{$bankwire_address nofilter}</p>
    {/if}
  </div>

  <div id="ps-wirepayment-status" class="alert alert-info" role="status">
    {$processing_message|escape:'html':'UTF-8'}
  </div>

  <div id="ps-wirepayment-error" class="alert alert-danger" role="alert" style="display:none;">
    {$error_message|escape:'html':'UTF-8'}
  </div>
</section>

{block name='javascript'}
  {$smarty.block.parent}
  <script>
    (function () {
      var asyncUrl = '{$async_process_url|escape:'javascript'}';
      var statusBox = document.getElementById('ps-wirepayment-status');
      var errorBox = document.getElementById('ps-wirepayment-error');
      var totalRaw = '{$total_to_pay_raw|escape:'javascript'}';
      var currencyId = '{$currency_id|intval}';

      function handleError(message) {
        if (statusBox) {
          statusBox.style.display = 'none';
        }
        if (errorBox) {
          errorBox.style.display = '';
          if (message) {
            errorBox.textContent = message;
          }
        }
      }

      if (!asyncUrl || !window.fetch) {
        handleError();
        return;
      }

      var postBody = 'ajax=1';
      if (totalRaw) {
        postBody += '&total_paid_real=' + encodeURIComponent(totalRaw);
        postBody += '&total_paid=' + encodeURIComponent(totalRaw);
      }
      if (currencyId) {
        postBody += '&id_currency=' + encodeURIComponent(currencyId);
      }

      var requestConfig = {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: postBody
      };

      fetch(asyncUrl, requestConfig)
        .then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
          return response.json();
        })
        .then(function (data) {
          if (!data || !data.success || !data.redirectUrl) {
            throw new Error();
          }
          window.location.href = data.redirectUrl;
        })
        .catch(function (error) {
          handleError(error && error.message);
        });
    })();
  </script>
{/block}
