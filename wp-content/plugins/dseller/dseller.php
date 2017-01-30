<?php
/*
    Plugin Name: DSeller
    Plugin URI: http://kuzovkov12.ru
    Description: Plugin for seller digital goods
    Armstrong: Digital Seller
    Version: 0.1
    Author URI: kuzovkov12.ru
*/


class DSeller {


    public $wm_options = array(
        'dseller_shop_id' => 'VideoService',
        'dseller_success_url' => 'dseller_wm_success',
        'dseller_fail_url' => 'dseller_wm_fail',
        'dseller_result_url' => 'dseller_wm_result',
        'dseller_secret_key' => 'Sekret_Merchant',
        'dseller_sign' => 'sha256',
        'dseller_success_method' => 'post',
        'dseller_fail_method' => 'post',
        'dseller_purse' => 'R425889686600',
        'dseller_sim_mode' => '0'

    );

    public $rk_options = array(
        'dseller_rk_shop_id' => 'blog-cook',
        'dseller_rk_sign' => 'sha256',
        'dseller_rk_success_url' => 'dseller_rk_success',
        'dseller_rk_fail_url' => 'dseller_rk_fail',
        'dseller_rk_result_url' => 'dseller_rk_result',
        'dseller_rk_success_method' => 'post',
        'dseller_rk_fail_method' => 'post',
        'dseller_rk_result_method' => 'post',
        'dseller_rk_pass1' => 'Passw0rd1',
        'dseller_rk_pass2' => 'Passw0rd2',
        'dseller_rk_testpass1' => 'tPassw0rd1',
        'dseller_rk_testpass2' => 'tPassw0rd2',
        'dseller_rk_testsign' => 'sha256',
        'dseller_rk_istest' => 1

    );

    public $options = array(
        'dseller_dir' => 'upload', /*имя каталога загрузки товаров*/
        'dseller_buy_url' => 'dseller_buy', /*URL обработчика кнопки "Купить"*/
        'dseller_download_url' => 'dseller_download', /*URL для загрузки оплаченного товара*/
        'dseller_link_timelive' => 10,/*время жизни ссылки для загрузки в днях*/
        'dseller_payment_system' => 'wm'
    );

    public $table_product = 'dseller_products'; /*имя таблицы товаров*/
    public $table_downloadcodes = 'dseller_downloadcodes'; /*имя таблицы кодов загрузки*/
    public $table_payments = 'dseller_payments'; /*имя таблицы произведенных платежей*/
    public $table_payments_rk = 'dseller_payments_rk'; /*имя таблицы произведенных платежей через robokassa*/
    public $field_file_name = 'file';/*имя поля загрузки файла формы добавления товара*/
    public $encode_in = 'windows-1251';
    public $encode_out = 'UTF-8//IGNORE';

    public function __construct(){
        global $wpdb;
        add_action('admin_menu', array($this,'add_admin_pages'));
        add_action('init', array($this,'run'));
        add_action('wp_logout', array($this,'endSession'));
        add_action('wp_login', array($this, 'endSession'));
        register_activation_hook(__FILE__, array($this,'install'));
        register_deactivation_hook(__FILE__, array($this,'uninstall'));
    }

    /**
     * Создание пункта меню в админпанели
     */
    public function add_admin_pages(){
        add_menu_page('DSeller Settings', 'DSeller', 8, 'dseller-opt', array($this,'show_settings_page'), plugins_url( 'dseller/img/webmoney.jpg' ));
    }

    /**
     * Старт сессии
     */
    public function startSession() {
        if(!session_id()) {
            session_start();
        }
    }

    /**
     * Удаление сессии
     */
    public function endSession() {
        session_destroy ();
    }

    /**
     * Обработчик входящих запросов
     */
    public function run(){
        $this->startSession();
        $real_uri = $_SERVER['REQUEST_URI'];

        if (($p = strpos($real_uri, '?')) === false){
            $uri = substr($real_uri, 1);
        }else{
            $uri = substr($real_uri, 1, strpos($real_uri, '?') - 1);
        }
        
        if ($uri == get_option('dseller_buy_url')){
            $id = (isset($_POST['id']))? intval($_POST['id']) : null;
            if ($id !== null){
                $this->send_payment_request($id);
            }else{
                wp_redirect( '/', 302 );
            }
            exit();
        }elseif ($uri == get_option('dseller_success_url')){
            $arr = (isset($_POST['LMI_PAYMENT_NO']))? $_POST : $_GET;
            $this->show_wm_success($arr);
            exit();
        }elseif($uri == get_option('dseller_fail_url')){
            $arr = (isset($_POST['LMI_PAYMENT_NO']))? $_POST : $_GET;
            $this->show_wm_fail($arr);
            exit();
        }elseif($uri == get_option('dseller_result_url')){
            if ($this->wm_check_result()) {
                $this->add_download_code($_POST);
                $this->add_payment($_POST);
            }
            exit();
        }elseif ($uri == get_option('dseller_rk_success_url')){
            $arr = (isset($_POST['InvId']))? $_POST : $_GET;
            $this->show_rk_success($arr);
            exit();
        }elseif ($uri == get_option('dseller_rk_fail_url')){
            $arr = (isset($_POST['InvId']))? $_POST : $_GET;
            $this->show_rk_fail($arr);
            exit();
        }elseif ($uri == get_option('dseller_rk_result_url')){
            $arr = (isset($_POST['InvId']))? $_POST : $_GET;
            if ($this->rk_check_result()) {
                $this->add_download_code($arr);
                $this->add_payment_rk($arr);
                echo 'OK' . $arr['InvId'];
            }
            exit();
        }elseif($uri == get_option('dseller_download_url')){
            $this->start_download();
            exit();
        }elseif($uri == 'dseller_debug'){
            //$this->add_payment(array());
            exit();
        }else{
            $_SESSION['curr_uri'] = $real_uri;
        }
    }

    /**
     * Проверка данных переданных системой WebMoney в оповещении о платеже
     */
    public function wm_check_result(){
        $result = implode(array(
            $_POST['LMI_PAYEE_PURSE'],
            $_POST['LMI_PAYMENT_AMOUNT'],
            $_POST['LMI_PAYMENT_NO'],
            $_POST['LMI_MODE'],
            $_POST['LMI_SYS_INVS_NO'],
            $_POST['LMI_SYS_TRANS_NO'],
            $_POST['LMI_SYS_TRANS_DATE'],
            get_option('dseller_secret_key'),
            $_POST['LMI_PAYER_PURSE'],
            $_POST['LMI_PAYER_WM']
        ));
        $hash = strtoupper(hash(get_option('dseller_sign'), $result));
        return ($_POST['LMI_HASH'] == $hash)? true : false;
    }

    /**
     * Проверка данных переданных системой Robokassa в оповещении о платеже
     */
    public function rk_check_result(){
        $pass = (intval(get_option('dseller_rk_istest')) == 1)? get_option('dseller_rk_testpass2') : get_option('dseller_rk_pass2');
        $result = implode(':', array(
            $_REQUEST['OutSum'],
            $_REQUEST['InvId'],
            $pass,
            'Shp_curr_url='.$_REQUEST['Shp_curr_url'],
            'Shp_dcode='.$_REQUEST['Shp_dcode'],
            'Shp_product_id='.$_REQUEST['Shp_product_id']
        ));
        $hash = strtoupper(hash(get_option('dseller_rk_sign'), $result));
        return (strtoupper($_REQUEST['SignatureValue']) == $hash)? true : false;
    }


    /**
     * Поках пользователю страницы успешного платежа
     * @param $arr массив POST или GET от системы WebMoney
     */
    public function show_wm_success($arr){
        include('views/wm_success.php');
    }

    /**
     * Поках пользователю страницы неуспешного платежа
     * @param $arr массив POST или GET от системы WebMoney
     */
    public function show_wm_fail($arr){
        include('views/wm_fail.php');
    }

    /**
     * Поках пользователю страницы успешного платежа
     * @param $arr массив POST или GET от системы Robokassa
     */
    public function show_rk_success($arr){
        include('views/rk_success.php');
    }

    /**
     * Поках пользователю страницы неуспешного платежа
     * @param $arr массив POST или GET от системы Robokassa
     */
    public function show_rk_fail($arr){
        include('views/rk_fail.php');
    }

    /**
     * Поках пользователю страницы с сообщением что ссылка на скачивание не активна
     */
    public function show_link_fail(){
        include('views/link_fail.php');
    }


    /**
     * создание опций по умолчанию
     */
    public function add_options(){
        foreach($this->options as $key => $val){
            add_option($key, $val);
        }

        foreach($this->wm_options as $key => $val){
            add_option($key, $val);
        }

        foreach($this->rk_options as $key => $val){
            add_option($key, $val);
        }
    }

    /**
     * удаление опций по умолчанию
     */
    public function delete_options(){
        foreach($this->options as $key => $val){
            delete_option($key);
        }

        foreach($this->wm_options as $key => $val){
            delete_option($key, $val);
        }

        foreach($this->rk_options as $key => $val){
            delete_option($key, $val);
        }
    }


    /**
     * определение по коду загрузки активна ли ссылка на загрузку
     * и если активна то старт отгрузки пользователю соответвующего файла
     */
    public function start_download(){
        global $wpdb;
        $this->delete_expired_codes();
        if (isset($_GET['dcode'])){
            $dcode = $_GET['dcode'];
            $table_downloadcodes = $wpdb->prefix . $this->table_downloadcodes;
            $table_products = $wpdb->prefix . $this->table_product;
            $code_product = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_downloadcodes WHERE download_code = %s", $dcode)
            );

            if ($code_product){
                $product_code_id = $code_product->product_id;
                $product = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM $table_products WHERE id = %d", $product_code_id)
                );
                $url = $product->url;
                $this->download_file($url);
            }else{
                $this->show_link_fail();
            }
        }

    }


    /**
     * отгрузка файла пользователю
     * @param $url настоящий URL файла
     */
    public function download_file($url){
        $filename = basename($url);
        $fullname = ABSPATH . get_option('dseller_dir') . '/' . $filename;
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . filesize($fullname));
        header('Keep-Alive: timeout=5; max=100');
        header('Connection: Keep-Alive');
        header('Content-Type: coter-stream');
        readfile($fullname);
        exit();
    }

    /**
     * удаление просроченных кодов
     */
    public function delete_expired_codes(){
        global $wpdb;
        $final_time = time() - intval(get_option('dseller_link_timelive')) * 3600 * 24;
        $table_downloadcodes = $wpdb->prefix . $this->table_downloadcodes;
        $wpdb->query(
            $wpdb->prepare("DELETE FROM $table_downloadcodes WHERE ctime < %d", $final_time)
        );
    }

    /**
     * проверка получены ли данные от формы
     * @param $name имя параметра POST который должен быть
     * @return bool
     */
    public function is_form_submited($name){
        if (isset($_POST[$name])){

            if(function_exists('current_user_can') && !current_user_can('manage_options')){
                die(_e('Access restrict','dseller'));                        }

            if(function_exists('check_admin_referer')){
                check_admin_referer('dseller_form');
            }

            return true;
        }
        return false;
    }

    /**
     * Метод вызываемый при активации плагина
     * Создание таблиц в базе данных и сохранение опций по умолчанию
     */
    public function install(){
        global $wpdb;

        $this->add_options();

        $table_products = $wpdb->prefix . $this->table_product;
        $table_downloadcodes = $wpdb->prefix . $this->table_downloadcodes;
        $table_payments = $wpdb->prefix .$this->table_payments;
        $table_payments_rk = $wpdb->prefix .$this->table_payments_rk;

            $sql1 = "CREATE TABLE IF NOT EXISTS `". $table_downloadcodes ."` 
        (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `download_code` varchar(64) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `ctime` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql2 = "CREATE TABLE IF NOT EXISTS `". $table_products ."` 
        (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(250) NOT NULL,
            `description` TEXT,
            `cost` varchar(250) NOT NULL,
            `url` varchar(250) NOT NULL,
            `post_id` INT(10) DEFAULT 0,
            PRIMARY KEY (`id`)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql3 = "CREATE TABLE IF NOT EXISTS `". $table_payments ."` 
        (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `PAYMENT_NO` int(12) NOT NULL,
            `PAYMENT_AMOUNT` FLOAT(10),
            `PAYEE_PURSE` varchar(25) NOT NULL,
            `SYS_TRANS_NO` varchar(250) NOT NULL,
            `PAYER_PURSE` varchar(25) NOT NULL,
            `PAYER_WM` varchar(25) NOT NULL,
            `SYS_TRANS_DATE` DATETIME NOT NULL,
            `PAYMENT_DESC` TEXT DEFAULT '',
            PRIMARY KEY (`id`)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql4 = "CREATE TABLE IF NOT EXISTS `". $table_payments_rk ."` 
        (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `PAYMENT_NO` int(12) NOT NULL,
            `PAYMENT_AMOUNT` FLOAT(10),
            `Product_id` INT(12) NOT NULL,
            `Payment_date` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $wpdb->query($sql1);
        $wpdb->query($sql2);
        $wpdb->query($sql3);
        $wpdb->query($sql4);
    }

    /**
     * Метод вызываемый при деактивации плагина
     * Удаление таблиц в базе данных и сохранение опций по умолчанию
     */
    public function uninstall(){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $table_downloadcodes = $wpdb->prefix . $this->table_downloadcodes;
        $table_payments = $wpdb->prefix .$this->table_payments;
        $table_payments_rk = $wpdb->prefix .$this->table_payments_rk;

        $products = $this->get_products();
        if (is_array($products)){
            foreach($products as $product){
                $this->delete_product($product->id);
            }
        }

        $this->delete_options();

        $sql1 = "DROP TABLE IF EXISTS `". $table_products ."`;";
        $sql2 = "DROP TABLE IF EXISTS `". $table_downloadcodes ."`;";
        $sql3 = "DROP TABLE IF EXISTS `". $table_payments ."`;";
        $sql4 = "DROP TABLE IF EXISTS `". $table_payments_rk ."`;";

        $wpdb->query($sql1);
        $wpdb->query($sql2);
        $wpdb->query($sql3);
        $wpdb->query($sql4);
    }


    /**
     * Добавление в базу данных информации о проведенном пользователем платеже
     * @param $post Массив POST от системы WebMoney при оповещении о платеже
     */
    public function add_payment($post){
        global $wpdb;
        $table_payments = $wpdb->prefix .$this->table_payments;
        //file_put_contents('mydebug.txt', print_r($post, true));
        $date = DateTime::createFromFormat('Ymd H:i:s', strval($post['LMI_SYS_TRANS_DATE']));

        $wpdb->insert(
            $table_payments,
            array(
                'PAYMENT_NO' => intval($post['LMI_PAYMENT_NO']),
                'PAYMENT_AMOUNT' => floatval($post['LMI_PAYMENT_AMOUNT']),
                'PAYEE_PURSE' => strval($post['LMI_PAYEE_PURSE']),
                'SYS_TRANS_NO' => strval($post['LMI_SYS_TRANS_NO']),
                'PAYER_PURSE' => strval($post['LMI_PAYER_PURSE']),
                'PAYER_WM' => strval($post['LMI_PAYER_WM']),
                'SYS_TRANS_DATE' => $date->format('Y-m-d H:i:s'),
                'PAYMENT_DESC' => iconv($this->encode_in, $this->encode_out, strval($post['LMI_PAYMENT_DESC']))
            ),
            array('%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }


    /**
     * Добавление в базу данных информации о проведенном пользователем платеже
     * через robokassa
     * @param $req Массив POST или GET от системы Robokassa при оповещении о платеже
     */
    public function add_payment_rk($req){
        global $wpdb;
        $table_payments = $wpdb->prefix .$this->table_payments_rk;
        //file_put_contents('mydebug.txt', print_r($post, true));
        $date = new DateTime();

        $wpdb->insert(
            $table_payments,
            array(
                'PAYMENT_NO' => intval($req['InvId']),
                'PAYMENT_AMOUNT' => floatval($req['OutSum']),
                'Product_id' => intval($req['Shp_product_id']),
                'Payment_date' => $date->format('Y-m-d H:i:s')
            ),
            array('%d', '%f', '%d', '%s')
        );
    }


    /**
     * Получение из базы данных информации о проведенных платежах
     * @return mixed
     */
    public function get_payments(){
        global $wpdb;
        $table_payments = $wpdb->prefix .$this->table_payments;
        $payments = $wpdb->get_results("SELECT * FROM $table_payments");
        return $payments;
    }
    /**
     * Получение из базы данных информации о проведенных платежах
     * через robokassa
     * @return mixed
     */
    public function get_payments_rk(){
        global $wpdb;
        $table_payments_rk = $wpdb->prefix .$this->table_payments_rk;
        $payments = $wpdb->get_results("SELECT * FROM $table_payments_rk");
        return $payments;
    }

    /**
     * Добавление в базу данных кода на скачивание при успешном проведении платежа
     * @param $post Массив POST от системы WebMoney при оповещении о платеже
     */
    public function add_download_code($post){
        $dcode = (isset($_POST['DCODE']))? $_POST['DCODE'] : $_REQUEST['Shp_dcode'];
        $ctime = time();
        $product_id = (isset($_POST['PRODUCT_ID']))? $_POST['PRODUCT_ID'] : $_REQUEST['Shp_product_id'];
        global $wpdb;
        $table_downloadcodes = $wpdb->prefix . $this->table_downloadcodes;
        $wpdb->insert(
            $table_downloadcodes,
            array('download_code' => $dcode, 'ctime'=> $ctime, 'product_id' => $product_id),
            array('%s', '%d', '%d')
        );
    }

    /**
     * отображение страниц настроек в админке
     */
    public function show_settings_page(){
        require('views/settings_page.php');
    }


    /**
     * генерация кода для формы Покупки
     * @param $product
     * @return string
     */
    public function get_buy_button($product){
        $form = "<form method='post' action='/". get_option('dseller_buy_url') ."'>
            <input type='hidden' name='id' value='{$product->id}'/>
            <button>Купить</button>
        </form>";
        return $form;
    }


    /**
     * Отправка запроса на проведение платежа
     * @param $id ID выбранного товара
     */
    public function send_payment_request($id){
        require('views/payment_forms.php');
    }


    /**
     * добавляем пост с описанием товара
     */
    public function add_product_post($post_category){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $row = $wpdb->get_row("SELECT MAX(id) AS id FROM $table_products");
        $product = $this->get_product(intval($row->id));
        $category = get_category_by_slug( $post_category );
        $category_id = $category->cat_ID;
        $post_data = array(
            'post_title'    => wp_strip_all_tags( $product->name ),
            'post_content'  => $product->description . $this->get_buy_button($product),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_category' => array( $category_id )
        );
        $post_id = wp_insert_post( $post_data );
        $wpdb->update($table_products,
            array('post_id' => $post_id),
            array('id' => $product->id),
            array('%s'),
            array('%d')
        );
    }


    /**
     * Обновление поста продукта при обновлении в админке
     * @param $id ID продукта
     */
    public function update_product_post($id, $post_category){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $product = $this->get_product($id);
        $category = get_category_by_slug( $post_category );
        $category_id = $category->cat_ID;
        wp_delete_post($product->post_id);
        $post_data = array(
            'post_title'    => wp_strip_all_tags( $product->name ),
            'post_content'  => $product->description . $this->get_buy_button($product),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_category' => array( $category_id )
        );
        $post_id = wp_insert_post( $post_data );
        $wpdb->update($table_products,
            array('post_id' => $post_id),
            array('id' => $product->id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * возвр номер покупки
     * @return int
     */
    public function get_payment_number(){
        return time();
    }


    /**
     * @param $id ID продукта
     * @return mixed
     */
    public function get_product($id){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $product = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_products WHERE id=%d", $id)
        );
        return $product;
    }

    /**
     * @return mixed
     */
    public function get_products(){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $products = $wpdb->get_results("SELECT * FROM $table_products");
        return $products;
    }

    /**
     * @param $name
     * @param $price
     * @param $url
     */
    public function add_product($name, $price, $url, $desc){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $wpdb->insert(
            $table_products,
            array('name' => $name, 'cost'=> $price, 'url' => $url, 'description' => $desc),
            array('%s', '%s', '%s', '%s')
        );
    }


    /**
     * Удаление товара из базы
     * @param $id ID товара
     */
    public function delete_product($id){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $product = $this->get_product($id);
        $post_id = $product->post_id;
        $wpdb->query("DELETE FROM $table_products WHERE id=$id");
        $this->delete_lost_files();
        wp_delete_post($post_id);
    }


    /**
     * Обновление данных о товаре в базе данных
     * @param $id
     * @param $name
     * @param $price
     * @param $url
     * @param $desc
     */
    public function update_product($id, $name, $price, $url, $desc){
        global $wpdb;
        $table_products = $wpdb->prefix . $this->table_product;
        $wpdb->update($table_products,
                array('name' => $name, 'cost' => $price, 'url' => $url, 'description' => $desc),
                array('id' => $id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
        $this->delete_lost_files();
    }


    /**
     * Удаление файлов в каталоге загрузки, ссылок на которые нет в таблице товаров
     */
    public function delete_lost_files(){
        $products = $this->get_products();
        $files = array();
        foreach($products as $product){
            $files[] = basename($product->url);
        }
        if (file_exists(ABSPATH . get_option('dseller_dir')) && is_dir(ABSPATH . get_option('dseller_dir'))){
            foreach(scandir(ABSPATH . get_option('dseller_dir')) as $file){
                if ($file == '.' || $file == '..') continue;
                if (!in_array($file, $files)){
                    unlink(ABSPATH . get_option('dseller_dir') .'/' . $file);
                }
            }
        }

    }


    /**
     * Загрузка файла цифрового товара в каталог загрузки
     * @param $files подмассив массива $_FILES, содержащий данные по загруженному файлу
     * @return bool
     */
    public function upload_file($files){
        if (isset($files['error']) && $files['error'] == 0){
            if ($this->check_upload_dir()){
                $tmp_name = $files['tmp_name'];
                $name = $files['name'];
                return (move_uploaded_file($tmp_name, ABSPATH . get_option('dseller_dir') . '/' . $name))? $name : false;
            }
        }
    }


    /**
     * Проверка существования каталога загрузки и при отсутствии создание его
     * @return bool
     */
    public function check_upload_dir(){
        $upload_dir = ABSPATH . get_option('dseller_dir');
        if (file_exists($upload_dir) && is_dir($upload_dir)){
            return true;
        }else{
            return mkdir($upload_dir);
        }
    }


    /**
     * Генерация рандомной текстовой строки
     * @param $n длина строки
     * @return string
     */
    public function random_string($n){
        $str = 'wertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
        $arr = str_split($str);
        $pass= '';
        for ($i = 0; $i < $n; $i++){
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }

}


$dseller = new DSeller();



