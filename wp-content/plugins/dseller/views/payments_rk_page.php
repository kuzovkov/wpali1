<?php include('_header.php'); ?>

<h2>Платежи Robokassa</h2>

<?php
$payments = $this->get_payments_rk();
$headers = array(
    'id' => 'ID',
    'PAYMENT_NO' => 'Номер платежа',
    'PAYMENT_AMOUNT' => 'Сумма платежа',
    'Product_id' => 'ID товара',
    'Payment_date' => 'Дата и время операции',
);
?>
<?php if ($payments): ?>
    <table class="table">
        <tr>
            <?php foreach($payments[0] as $key => $val): ?>
                <th> <?php echo $headers[$key]; ?></th>
            <?php endforeach;?>
        </tr>
        <?php foreach($payments as $row): ?>
            <tr>
                <?php foreach($row as $key => $val): ?>
                    <td> <?php echo $val; ?> </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else:?>
    <p>Платежей нет</p>
<?php endif;?>

<?php include('_footer.php');?>

