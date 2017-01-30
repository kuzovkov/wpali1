<?php include('_header.php'); ?>
<?php require('_handler.php');?>

<div>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#general" aria-controls="home" role="tab" data-toggle="tab">Основные настройки</a></li>
        <li role="presentation"><a href="#webmoney" aria-controls="profile" role="tab" data-toggle="tab">WebMoney</a></li>
        <li role="presentation"><a href="#robokassa" aria-controls="profile" role="tab" data-toggle="tab">Robokassa</a></li>
        <li role="presentation"><a href="#add_product" aria-controls="messages" role="tab" data-toggle="tab">Добавить товар</a></li>
        <li role="presentation"><a href="#list_products" aria-controls="settings" role="tab" data-toggle="tab">Список товаров</a></li>
        <li role="presentation"><a href="#payments" aria-controls="settings" role="tab" data-toggle="tab">Платежи</a></li>
        <li role="presentation"><a href="#payments_rk" aria-controls="settings" role="tab" data-toggle="tab">Платежи Robokassa</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="general"><?php require('general_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="webmoney"><?php require('wm_opt_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="robokassa"><?php require('rk_opt_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="add_product"><?php require('product_add_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="list_products"><?php require('products_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="payments"><?php require('payments_page.php');?></div>
        <div role="tabpanel" class="tab-pane" id="payments_rk"><?php require('payments_rk_page.php');?></div>
    </div>

</div>


<?php include('_footer.php');?>

<script>

    $('#myTabs a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })
    
    <?php if (isset($_GET['tab'])): ?>
        $('.nav-tabs > li').removeClass('active');
        $('a[href="#<?php echo $_GET['tab'];?>"]').parent().addClass('active');
        $('.tab-content > div').removeClass('active');
        $('.tab-content #<?php echo $_GET['tab'];?>').addClass('active');
    <?php endif; ?>
</script>