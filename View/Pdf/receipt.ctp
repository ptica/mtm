<style>
	.th1 {
	}
	.th2 {
		font-family: monospace;
		text-align: right;
	}
</style>
<div style="margin: 45px;">
	<table border="1" cellpadding="15px">
		<tr>
			<th width="100%" colspan="2"><h2>RECEIPT</h2></th>
		</tr>
		<tr>
			<th colspan="2"><h3>Thirteenth MT Marathon 2018</h3></th>
		</tr>
	    <tr>
	        <td colspan="2">
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

		<?php
			$fields = array(
				'institution',
				'name',
				'address',
				'vat'
			);
			foreach ($fields as $field) {
				$name = $field == 'vat' ? 'VAT' : ucfirst($field);
				$val  = $Booking[$field];
				echo "<tr><td width='20%' class='th1'>$name</td> <td width='80%' class='th2'>$val</td></tr>";
			}
		?>

		<tr>
		   <td style="text-align:left">
			   amount paid
			<td class="th2" style="font-size:25px">
			  CZK <?= $Payment['amountcents']/100 ?>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right">
			   payment was accepted on <?= $Payment['created'] ?>
		   </td>
	   </tr>
	</table>

	<p>
	    <img src="<?= Router::Url('/images/logo.png', $full=true)?>">
	</p>


	<p>Thank you for your payment.</p>
	<p>The MTM 18 team.</p>
</div>
