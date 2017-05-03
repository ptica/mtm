<?php
    $reg_types = Hash::extract($Booking['RegType'], '{n}.key');
    $reg_type = implode(' & ', $reg_types);

    $total = $Booking['web_price'];
?>

<p>
    Dear <?= $Booking['name'] ?>.
</p>

<p>We hereby confirm your <?= $reg_type ?> registration for:</p>
<ul>
    <?php
        foreach ($Booking['RegItem'] as $item) {
            echo '<li>' . $item['desc'] . '</li>';
        }
    ?>
    <li>for a total of CZK <?= $total ?>
    </li>
</ul>



<p>Thank you for your payment.</p>
<p>The EAMT 2017 team.</p>

<p>
    <img src="<?php echo $logo; ?>">
</p>
