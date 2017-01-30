<html>
<head>
    <title>Redirecting to payment gateway</title>
    <meta charset="utf-8"/>
</head>
<body>

<img class="preloader" src="<?php echo plugins_url();?>/dseller/img/preloader.gif"/>

<?php
    $product = $this->get_product($id);
    $desc = 'Оплата за продукт ' . $product->name;
    $dcode = $this->random_string(20);

//Robokassa
// регистрационная информация (Идентификатор магазина, пароль #1)
// registration info (Merchant ID, password #1)
$mrh_login = get_option('dseller_rk_shop_id');
$is_test = intval(get_option('dseller_rk_istest'));

$mrh_pass1 = ($is_test == 0)? get_option('dseller_rk_pass1') : get_option('dseller_rk_testpass1');

// номер заказа
// number of order
$inv_id = $this->get_payment_number();

// описание заказа
// order description
$inv_desc = $desc;

// сумма заказа
// sum of order
$out_summ = round(floatval($product->cost),2);

// язык
// language
$culture = "ru";

// кодировка
// encoding
$encoding = "utf-8";

// Валюта счёта
// OutSum Currency
//$OutSumCurrency = "USD";

$shp_curr_url = $_SESSION['curr_uri'];
$shp_dcode = $dcode;
$shp_product_id = $product->id;

// формирование подписи
// generate signature
$crc  = hash(get_option('dseller_rk_sign'), "$mrh_login:$out_summ:$inv_id:$mrh_pass1:Shp_curr_url=$shp_curr_url:Shp_dcode=$shp_dcode:Shp_product_id=$shp_product_id");

?>
<!-- Robokassa -->

<form name="rk_form" action='https://auth.robokassa.ru/Merchant/Index.aspx' method=POST>"
    <input type="hidden" name="MrchLogin" value="<?php echo $mrh_login;?>">
    <input type="hidden" name="OutSum" value="<?php echo $out_summ;?>">
    <input type="hidden" name="InvId" value="<?php echo $inv_id;?>">
    <input type="hidden" name="Desc" value="<?php echo $inv_desc;?>">
    <input type="hidden" name="SignatureValue" value="<?php echo $crc;?>">
    <input type="hidden" name="Culture" value="<?php echo $culture;?>">
    <input type="hidden" name="IsTest" value="<?php echo $is_test;?>">
    <input type="hidden" name="Shp_curr_url" value="<?php echo $shp_curr_url;?>">
    <input type="hidden" name="Shp_dcode" value="<?php echo $shp_dcode;?>">
    <input type="hidden" name="Shp_product_id" value="<?php echo $shp_product_id;?>">

    <!--<input type=hidden name=OutSumCurrency value=$OutSumCurrency>-->
</form>


<!-- WebMoney-->
<form name="wm_form" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp" accept-charset="windows-1251">
    <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?php echo round(floatval($product->cost),2);?>">
    <input type="hidden" name="LMI_PAYMENT_DESC" value="<?php echo $desc;?>">
    <input type="hidden" name="LMI_PAYMENT_NO" value="<?php echo $this->get_payment_number();?>">
    <input type="hidden" name="LMI_PAYEE_PURSE" value="<?echo get_option('dseller_purse');?>">
    <input type="hidden" name="LMI_SIM_MODE" value="<?echo get_option('dseller_sim_mode');?>">
    <input type="hidden" name="PRODUCT_ID" value="<?echo $product->id;?>">
    <input type="hidden" name="DCODE" value="<?echo $dcode;?>">
    <input type="hidden" name="CURR_URI" value="<?echo $_SESSION['curr_uri'];?>">
</form>



<script type="text/javascript">
    window.setTimeout(submitform,1000);
    function submitform(){
        document.<?php echo get_option('dseller_payment_system');?>_form.submit();
    }
</script>
</body>
</html>