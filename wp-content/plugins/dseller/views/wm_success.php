<html>
<head>
    <title>Payment success</title>
    <meta charset="utf-8"/>
    <?php require('_header.php');?>
</head>
<body>
<h2>Оплата успешно произведена</h2>

<?php
    $timelive = get_option('dseller_link_timelive');
    $dcode = $arr['DCODE'];
    $curr_uri = $arr['CURR_URI'];
    $link = home_url() . '/' . get_option('dseller_download_url') . '?dcode=' . $dcode;
?>

<p>Ваша ссылка на скачивание: <a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a> (действительна <?php echo $timelive; ?> дней)</p>
<p><a href="<?php echo $curr_uri; ?>">Обратно на страницу товара</a></p>
<?php require('_footer.php');?>
</body>
</html>