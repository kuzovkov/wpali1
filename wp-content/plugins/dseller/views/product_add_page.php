

<?php if (isset($_POST['operation']) && $_POST['operation'] == 'edit'):?>
    <h2>Редактировать товар</h2>
<?php else:?>
    <h2>Добавить товар</h2>
<?php endif;?>
<form class="opt-form" name="dseller_form" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['HTTP_SELF']?>?page=dseller-opt&amp;update=true&amp;tab=add_product">
    <?php
        $product = null;
        if (isset($_POST['operation']) && $_POST['operation'] == 'edit'):
            $id = intval($_POST['id']);
            $product = $this->get_product($id);
            $product_post_category = get_the_category($product->post_id);

    ?>
        <div class="form-group"><label for="id">id:</label><input id="id" class="form-control" name="id" value="<?php echo $_POST['id'];?>" disabled/></div>
        <input type="hidden" name="id" value="<?php echo $product->id;?>"/>
        <input type="hidden" name="operation" value="edit"/>
    <?php endif;?>
    <div class="form-group"><label for="name">Название:</label><input id="name" class="form-control" name="name" value="<?php if($product) echo $product->name;?>"/></div>
    <div class="form-group"><label for="price">Цена:</label><input id="price" class="form-control" name="price" value="<?php if($product) echo $product->cost;?>"/></div>
    <div class="form-group"><label for="post_category">Категория поста о товаре:</label>
    <select id="post_category" name="post_category" class="form-control" >
        <?php $categories = get_categories(array('hide_empty' => false)); foreach($categories as $cat):?>
            <option value="<?php echo $cat->slug;?>" <?php if($product && $product_post_category[0]->cat_ID == $cat->cat_ID) echo 'selected';?>><?php echo $cat->name;?></option>
        <?php endforeach;?>
    </select>
    </div>
    <div class="form-group"><label for="url">URL:</label><input class="form-control" id=url name="url" value="<?php if($product) echo $product->url;?>"/></div>
    <div class="form-group"><label for="dseller-desc">Описание:</label><textarea class="form-control" id=dseller-desc name="description" value=""><?php if($product) echo stripslashes($product->description);?></textarea></div>
    <div class="form-group"><label for="file">File:</label><input type="file" class="form-control" id=file name="file" value=""/></div>
    <?php if (isset($_POST['operation']) && $_POST['operation'] == 'edit'): ?>
        <button name="dseller_product_update_btn" type="submit" class="btn btn-default">Сохранить</button>
    <?php else:?>
        <button name="dseller_product_add_btn" type="submit" class="btn btn-default">Добавить</button>
    <?php endif;?>
    <?php if(function_exists('wp_nonce_field')) wp_nonce_field( 'dseller_form' ); ?>
</form>
<hr/>


<?php include ('_tinymce.php');?>



