<html>
<head>
    <title>Payment fail</title>
    <meta charset="utf-8"/>
    <?php require('_header.php');?>
</head>
<body>
<?php
    $curr_uri = $arr['CURR_URI'];
?>
<h2>Оплата не произведена</h2>
<p><a href="<?php echo $curr_uri; ?>">Обратно на страницу товара</a></p>
<?php require('_footer.php');?>
</body>
</html>
