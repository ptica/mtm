<?php
    $reg_types = Hash::extract($Booking['RegType'], '{n}.key');
    $reg_type = implode(' & ', $reg_types);

    $total = $Booking['web_price'];
?>

<p>
    Dear <?= $Booking['name'] ?>.
</p>

<p>We hereby confirm your <?= $reg_type ?> registration and payment for:</p>

<table>
    <tr>
        <td>
            <ul>
                <?php
                    foreach ($Booking['RegItem'] as $item) {
                        echo '<li>' . $item['desc'] . '</li>';
                    }
                ?>
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <td style="text-align:left">
            for a total of CZK <?= $Payment['amountcents']/100 ?><br>
            payment was accepted on <?= $Payment['created'] ?>
        </td>
    </tr>
    <tr>
        <td>
            Registered participant:
            <ul>
                <li><?= $Booking['institution'] ?></li>
				<li><?= $Booking['address'] ?></li>
				<li><?= $Booking['vat'] ?></li>
				<li><?= $Booking['name'] ?></li>
            </ul>
        </td>
    </tr>
</table>


<p>Thank you for your payment.</p>
<p>The TLT 16 team.</p>

<p>
    <img src="<?php echo $logo; ?>">
</p>
