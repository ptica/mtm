<?php
?>

<p>
    Dear <?= $Booking['name'] ?>.
</p>

<p>We hereby confirm your <?= $reg_type ?> registration to:</p>

<table>
    <tr>
        <td>
            <ul>
                <?php
                    foreach ($RegItem as $item) {
                        echo '<li>' . $item['desc'] . '</li>';
                    }
                ?>
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <td style="text-align:left">
            for a total of CZK 0<br>
        </td>
    </tr>
    <tr>
        <td>
            Registered participant:
            <ul>
                <li><?= $Booking['institution'] ?></li>
				<li><?= $Booking['address'] ?></li>
				<li><?= $Booking['name'] ?></li>
            </ul>
        </td>
    </tr>
</table>


<p>Thank you for your registration.</p>
<p>The MTM 18 team.</p>

<p>
    <img src="cid:logo">
</p>
