<style>
  .form-blp-woocommerce {
    font-size: 16px;
  }
  .blp-mb {
    margin-bottom: 1em;
  }
  .blp-container-expiration {
    display: grid;
    grid-template-columns: calc(50% - 15px) calc(50% - 15px);
    gap: 30px;
  }
</style>
<div class="form-blp-woocommerce">
  <div class="form-row form-row-wide blp-mb">
    <label for="cardNumber">Número de tarjeta<span class="required">*</span></label>
    <input id="cardNumber" name="cardNumber" class="input-text" type="text" maxlength="16"/>
  </div>

  <div class="blp-container-expiration blp-mb">
    <div class="form-row form-row-wide">
      <label for="expirationMonth">Mes de expiración<span class="required">*</span></label>
      <input id="expirationMonth" name="expirationMonth" class="input-text" type="text" maxlength="2" />
    </div>

    <div class="form-row form-row-wide">
      <label for="expirationYear">Año de expiración<span class="required">*</span></label>
      <input id="expirationYear" name="expirationYear" class="input-text" type="text" maxlength="2" />
    </div>  
  </div>

  <div class="form-row form-row-wide blp-mb">
    <label for="cvv">CVV<span class="required">*</span></label>
    <input id="cvv" name="cvv" class="input-text" type="text" maxlength="3" />
  </div>
</div>