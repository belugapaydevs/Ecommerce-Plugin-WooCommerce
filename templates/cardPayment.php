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
  .blp-card {
    position: relative;
  }
  .blp-logos {
    position: absolute;
    top: 0px;
    right: 0;
    height: 100%;
    display: flex;
    justify-content: flex-end;
    align-items: center;
  }
  .blp-logos .carnet {
    width: auto;
    height: 25px;
    margin-right: 10px;
  }
  .blp-logos .visa {
    width: auto;
    height: 15px;
    margin-right: 10px;
  }
  .blp-logos .mastercard {
    width: auto;
    height: 20px;
    margin-right: 10px;
  }
</style>
<div class="form-blp-woocommerce">

  <div class="form-row form-row-wide blp-mb">
    <label for="cardHolder">Nombre del tarjetahabiente<span class="required">*</span></label>
    <input id="cardHolder" name="cardHolder" class="input-text" type="text"/>
  </div>

  <div class="form-row form-row-wide blp-mb">
    <label for="cardNumber">Número de tarjeta<span class="required">*</span></label>
    <div class="blp-card">
      <input id="cardNumber" name="cardNumber" class="input-text" type="text" maxlength="16"/>
      <div class="blp-logos">
        <img class="visa" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/logo_Visa.svg'; ?>">
        <img class="mastercard" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/logo_mastercard.svg'; ?>">
        <img class="carnet" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'includes/images/logo_carnet.svg'; ?>">
      </div>
    </div>
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