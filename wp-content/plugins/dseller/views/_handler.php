<?php

/**
 * Обработчики форм для добавления, редактирования и удаления товаров
 */
    /*Обновление опций WebMoney*/
    if ($this->is_form_submited('dseller_wm_opt_btn')){
        foreach($this->wm_options as $key => $val){
            update_option($key, $_POST[$key]);
        }
    }

    /*Обновление опций Robokassa*/
    if ($this->is_form_submited('dseller_rk_opt_btn')){
        foreach($this->rk_options as $key => $val){
            update_option($key, $_POST[$key]);
        }
        
        update_option('dseller_rk_istest', 0);
        if (isset($_POST['dseller_rk_istest']) && $_POST['dseller_rk_istest'] == 'on'){
            update_option('dseller_rk_istest', 1);
        }
    }

    /*Обновление основных опций*/
    if ($this->is_form_submited('dseller_mainopt_btn')){
        foreach($this->options as $key => $val){
            update_option($key, $_POST[$key]);
        }
    }

    /*Добавление товара*/
    if($this->is_form_submited('dseller_product_add_btn')){
        $name = $_POST['name'];
        $price = floatval($_POST['price']);
        $url = $_POST['url'];
        $desc = $_POST['description'];
        $post_category = $_POST['post_category'];
        //var_dump($_POST);
        //var_dump($_FILES);
        if (isset($_FILES[$this->field_file_name])){
            if($filename = $this->upload_file($_FILES[$this->field_file_name])){
                $url =  home_url() . '/' . get_option('dseller_dir') . '/' . $filename;
            }
        }
        $this->add_product($name, $price, $url, $desc);
        $this->add_product_post($post_category);

    }

    /*Редактирование товара*/
    if($this->is_form_submited('dseller_product_update_btn')){
        $name = $_POST['name'];
        $price = floatval($_POST['price']);
        $url = $_POST['url'];
        $id = intval($_POST['id']);
        $desc = $_POST['description'];
        $post_category = $_POST['post_category'];
        if (isset($_FILES[$this->field_file_name])){
            if($filename = $this->upload_file($_FILES[$this->field_file_name])){
                $url =  home_url() . '/' . get_option('dseller_dir') . '/' . $filename;
            }
        }
        $this->update_product($id, $name, $price, $url, $desc);
        $this->update_product_post($id, $post_category);

    }

    /*Удаление товара*/
    if($this->is_form_submited('dseller_product_del_btn')){
        $id = intval($_POST['id']);
        $this->delete_product($id);
    }

    $products = $this->get_products();



?>