

<h2>Настройки WebMoney</h2>
<form class="opt-form" name="dseller_form" method="post" action="<?php echo $_SERVER['HTTP_SELF']?>?page=dseller-opt&amp;update=true&amp;tab=webmoney">
    <?php if(function_exists('wp_nonce_field')) wp_nonce_field( 'dseller_form' ); ?>
    <div class="form-group"><label for="purse">Кошелек для приема оплаты:</label><input id="purse" class="form-control" name="dseller_purse" value="<?php echo get_option("dseller_purse")?>"/></div>
    <div class="form-group"><label for="shop_id">Shop ID:</label><input id="shop_id" class="form-control" name="dseller_shop_id" value="<?php echo get_option("dseller_shop_id")?>"/></div>
    <div class="form-group"><label for="secret_key">Secret Key:</label><input class="form-control" id="secret_key" name="dseller_secret_key" value="<?php echo get_option("dseller_secret_key")?>"/></div>
    <div class="form-group"><label for="success_url">Success URL:</label><input id="success_url"  class="form-control" name="dseller_success_url" value="<?php echo get_option("dseller_success_url")?>"/></div>
    <div class="form-group"><label for="dseller_sign">Метод вызова Success URL:</label>
        <select id="dseller_sign" name="dseller_success_method"  class="form-control" >
            <option value="get" <?php if(get_option('dseller_success_method') == 'get') echo 'selected';?>>GET</option>
            <option value="post" <?php if(get_option('dseller_success_method') == 'post') echo 'selected';?>>POST</option>
        </select>
    </div>
    <div class="form-group"><label for="fail_url">Fail URL:</label><input id="fail_url"  class="form-control" name="dseller_fail_url" value="<?php echo get_option("dseller_fail_url")?>"/></div>
    <div class="form-group"><label for="dseller_sign">Метод вызова Fail URL:</label>
        <select id="dseller_sign" name="dseller_fail_method"  class="form-control" >
            <option value="get" <?php if(get_option('dseller_fail_method') == 'get') echo 'selected';?>>GET</option>
            <option value="post" <?php if(get_option('dseller_fail_method') == 'post') echo 'selected';?>>POST</option>
        </select>
    </div>
    <div class="form-group"><label for="result_url">Result URL:</label><input id="result_url"  class="form-control" name="dseller_result_url" value="<?php echo get_option("dseller_result_url")?>"/></div>
    <div class="form-group"><label for="dseller_sign">Метод формирования контрольной подписи оповещения о платеже:</label>
    <select id="dseller_sign" name="dseller_sign"  class="form-control" >
        <!--<option value="sign" <?php if(get_option('dseller_sign') == 'sign') echo 'selected';?>>SIGN</option>-->
        <option value="sha256" <?php if(get_option('dseller_sign') == 'sha256') echo 'selected';?>>SHA256</option>
        <option value="md5" <?php if(get_option('dseller_sign') == 'md5') echo 'selected';?>>MD5</option>
    </select>
    </div>
    <div class="form-group"><label for="dseller_sim_mode">Дополнительное поле, определяющее режим тестирования:</label>
    <select id="dseller_sim_mode" name="dseller_sim_mode"  class="form-control" >
        <option value="0" <?php if(get_option('dseller_sim_mode') == '0') echo 'selected';?>>0</option>
        <option value="1" <?php if(get_option('dseller_sim_mode') == '1') echo 'selected';?>>1</option>
        <option value="2" <?php if(get_option('dseller_sim_mode') == '2') echo 'selected';?>>2</option>
    </select>
    </div>
    <button name="dseller_wm_opt_btn" type="submit" class="btn btn-default">Сохранить</button>
</form>

