<?php include('_header.php'); ?>

<h2>Платежи</h2>

<?php
    $payments = $this->get_payments();
    $headers = array(
        'id' => 'ID',
        'PAYMENT_NO' => 'Номер платежа',
        'PAYMENT_AMOUNT' => 'Сумма платежа',
        'PAYEE_PURSE' => 'Кошелек на который поступила оплата',
        'SYS_TRANS_NO' => 'Номер платежа в системе WM',
        'PAYER_PURSE' =>'Кошелек плательщика',
        'PAYER_WM' => 'WMID плательщика',
        'SYS_TRANS_DATE' => 'Дата и время операции',
        'PAYMENT_DESC' => 'Описание платежа'
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

