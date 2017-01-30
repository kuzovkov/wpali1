<h2>Настройки Robokassa</h2>
<form class="opt-form" name="dseller_form" method="post" action="<?php echo $_SERVER['HTTP_SELF']?>?page=dseller-opt&amp;update=true&amp;tab=robokassa">
    <?php if(function_exists('wp_nonce_field')) wp_nonce_field( 'dseller_form' ); ?>
    <div class="form-group"><label for="shop_id">Идентификатор магазина:</label><input id="shop_id" class="form-control" name="dseller_rk_shop_id" value="<?php echo get_option("dseller_rk_shop_id")?>"/></div>
    <div class="form-group"><label for="pass1">Пароль #1:</label><input id="pass1" class="form-control" name="dseller_rk_pass1" value="<?php echo get_option("dseller_rk_pass1")?>"/></div>
    <div class="form-group"><label for="pass2">Пароль #2:</label><input class="form-control" id="pass2" name="dseller_rk_pass2" value="<?php echo get_option("dseller_rk_pass2")?>"/></div>
    <div class="form-group"><label for="dseller_rk_success_method">Success URL:</label><input id="success_url"  class="form-control" name="dseller_rk_success_url" value="<?php echo get_option("dseller_rk_success_url")?>"/></div>
    <div class="form-group"><label for="dseller_sign">Метод вызова Success URL:</label>
        <select id="dseller_rk_success_method" name="dseller_rk_success_method"  class="form-control" >
            <option value="get" <?php if(get_option('dseller_rk_success_method') == 'get') echo 'selected';?>>GET</option>
            <option value="post" <?php if(get_option('dseller_rk_success_method') == 'post') echo 'selected';?>>POST</option>
        </select>
    </div>
    <div class="form-group"><label for="fail_url">Fail URL:</label><input id="fail_url"  class="form-control" name="dseller_rk_fail_url" value="<?php echo get_option("dseller_rk_fail_url")?>"/></div>
    <div class="form-group"><label for="dseller_fail_method">Метод вызова Fail URL:</label>
        <select id="dseller_fail_method" name="dseller_rk_fail_method"  class="form-control" >
            <option value="get" <?php if(get_option('dseller_rk_fail_method') == 'get') echo 'selected';?>>GET</option>
            <option value="post" <?php if(get_option('dseller_rk_fail_method') == 'post') echo 'selected';?>>POST</option>
        </select>
    </div>
    <div class="form-group"><label for="result_url">Result URL:</label><input id="result_url"  class="form-control" name="dseller_rk_result_url" value="<?php echo get_option("dseller_rk_result_url")?>"/></div>
    <div class="form-group"><label for="dseller_result_method">Метод вызова Result URL:</label>
        <select id="dseller_result_method" name="dseller_rk_result_method"  class="form-control" >
            <option value="get" <?php if(get_option('dseller_rk_result_method') == 'get') echo 'selected';?>>GET</option>
            <option value="post" <?php if(get_option('dseller_rk_result_method') == 'post') echo 'selected';?>>POST</option>
        </select>
    </div>

    <div class="form-group"><label for="dseller_rk_sign">Алгоритм расчета хеша:</label>
        <select id="dseller_rk_sign" name="dseller_rk_sign"  class="form-control" >
            <option value="sha256" <?php if(get_option('dseller_rk_sign') == 'sha256') echo 'selected';?>>SHA256</option>
            <option value="md5" <?php if(get_option('dseller_rk_sign') == 'md5') echo 'selected';?>>MD5</option>
        </select>
    </div>

    <h3>Параметры проведения тестовых платежей</h3>
    <div class="form-group"><label for="dseller_rk_testsign">Алгоритм расчета хеша:</label>
        <select id="dseller_rk_testsign" name="dseller_rk_testsign"  class="form-control" >
            <option value="sha256" <?php if(get_option('dseller_rk_testsign') == 'sha256') echo 'selected';?>>SHA256</option>
            <option value="md5" <?php if(get_option('dseller_rk_testsign') == 'md5') echo 'selected';?>>MD5</option>
        </select>
    </div>
    <div class="form-group"><label for="tpass1">Пароль #1:</label><input id="tpass1" class="form-control" name="dseller_rk_testpass1" value="<?php echo get_option("dseller_rk_testpass1")?>"/></div>
    <div class="form-group"><label for="tpass2">Пароль #2:</label><input class="form-control" id="tpass2" name="dseller_rk_testpass2" value="<?php echo get_option("dseller_rk_testpass2")?>"/></div>
    <div class="form-group"><label for="istest">Тестовый режим: </label> <input id="istest"  type="checkbox" class="form-control" name="dseller_rk_istest" <?php echo (intval(get_option("dseller_rk_istest")) == 1)? 'checked' : ''; ?>/></div>
    <button name="dseller_rk_opt_btn" type="submit" class="btn btn-default">Сохранить</button>
</form>
