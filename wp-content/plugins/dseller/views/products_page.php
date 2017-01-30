
<h2>Список товаров</h2>

<?php if ($products): ?>
    <table class="table">
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>URL</th>
            <th>Описание</th>
            <th></th>
            <th></th>
        </tr>
<?php foreach($products as $item):?>
    <form class="opt-form" name="dseller_form" method="post" action="<?php echo $_SERVER['HTTP_SELF']?>?page=dseller-opt&amp;update=true&amp;tab=list_products">
        <?php if(function_exists('wp_nonce_field')) wp_nonce_field( 'dseller_form' ); ?>
        <tr>
            <td> <?php echo $item->id?> </td>
            <td> <?php echo $item->name;?> </td>
            <td> <?php echo $item->cost;?> </td>
            <td> <?php echo $item->url;?> </td>
            <td> <?php echo strip_tags($item->description);?> </td>
            <input type="hidden" name="id" value="<?php echo $item->id;?>"/>
            <td><button name="dseller_product_del_btn" type="submit" class="btn btn-default">Удалить</button></td>

    </form>
        <form method="post"  name="dseller_form" action="<?php echo $_SERVER['HTTP_SELF']?>?page=dseller-opt&amp;update=true&amp;tab=add_product">
            <?php if(function_exists('wp_nonce_field')) wp_nonce_field( 'dseller_form' ); ?>
            <input type="hidden" name="id" value="<?php echo $item->id;?>">
            <input type="hidden" name="operation" value="edit"/>
            <td><button name="dseller_product_edit_btn" type="submit" class="btn btn-default">Редактировать</button></td>
        </form>
        </tr>

<?php endforeach;?>
    </table> 
<?php else: ?>
    <p>Товаров нет</p>
<?php endif; ?>

