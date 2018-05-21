<?php
	//debug($bookings);
	App::import('Model', 'Booking');
	$this->Booking = new Booking();

	$reg_types_1 = file_get_contents(APP . 'Config/price-mtm.json');
	$reg_types_2 = file_get_contents(APP . 'Config/price-workshop.json');

	$reg_types_1 = json_decode($reg_types_1, $assoc = TRUE);
	$reg_types_2 = json_decode($reg_types_2, $assoc = TRUE);

	$total = [
		'workshop' => 0,
		'mtm' => 0,
		'grand' => 0,
	];
	$counts = [
		'workshop' => 0,
		'mtm_early' => 0,
		'mtm_late' => 0,
	];
?>
<div class="bookings index">
	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
					<ul class="nav nav-pills pull-right">
							<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;' . __('List Bookings'), array('action'=>'index'), array('escape'=>false)); ?></li>
					</ul>
					<h1><?php echo __('Recieved Payments'); ?></h1>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<?php echo $this->Form->create('Booking'); ?>
				<table cellpadding="0" cellspacing="0" class="table table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th><?php echo $this->Paginator->sort('name'); ?></th>
							<th><?php echo $this->Paginator->sort('address'); ?></th>
							<th style="min-width:115px;"><?php echo $this->Paginator->sort('start', 'Date'); ?></th>
							<th><?php echo $this->Paginator->sort('email'); ?></th>
							<!--th class="r">Room + Addons</th-->
							<!--th class="r">Lunches</th-->
							<th class="r">Workshop</th>
							<th class="r">MTM 18</th>
							<th class="r">Total</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($bookings as $booking) { ?>
						<tr>
							<td><?php $payment = end($booking['Payment']); echo $payment['id']; ?></td>
							<td><?php echo h($booking['Booking']['name']); ?></td>
							<!--td>
								<?php
									$title = '-';
									if (isset($booking['Room']['name'])) {
										$title  = $booking['Booking']['beds'] . '@';
										$title .= $booking['Room']['name'] . '@';
										$title .= @$locations[$booking['Room']['location_id']];
									}
									$url = array('controller' => 'bookings', 'action' => 'edit', $booking['Booking']['id']);
									echo @$this->Html->link($title, $url);
								?>
							</td-->
							<td><?php echo $booking['Booking']['institution'] . '<br>'. $booking['Booking']['address']; ?></td>
							<td><?php echo $booking['Booking']['created']; ?></td>
							<td><?php echo h($booking['Booking']['email']); ?></td>
							<?php
								$price = $this->Booking->get_price($booking, $per_partes=true);
							?>
							<!--td class="r"><?php echo h($price['accomodation']); ?></td-->
							<!--td class="r"><?php echo h($price['meals']); ?></td-->
							<?php
								$early_set = explode(' ', "4725 5265 5805 6210 6750 7290 5670 6210 6750 7830 8370 8910");
								$late_set  = explode(' ', "5940 6480 7020 8100 8640 9180 6615 7155 7695 9720 10260 10800");
								$web_price = (int) floor($booking['Booking']['web_price']);
								$is_early = array_search($web_price, $early_set) !== false;
								$is_late  = array_search($web_price, $late_set)  !== false;

								$codes = Hash::extract($booking['RegType'], '{n}.key');
								$items = Hash::extract($booking['RegItem'], '{n}.key');
								if ($is_early) {
									array_push($codes, 'early');
								}
								sort($codes);

								if (array_search('mtm', $items) !== false) {
									$key1 = implode('-', $codes);
									$mtm_price = $reg_types_1[$key1]['czk'];
								} else {
									$mtm_price = 0;
								}

								if (array_search('workshop', $items) !== false) {
									$key2 = array_search('student', $codes) ? 'student' : '';
									$workshop_price = $reg_types_2[$key2]['czk'];
								} else {
									$workshop_price = 0;
								}

								$total['workshop'] += $workshop_price;
								$total['mtm']      += $mtm_price;
								$total['grand']    += $booking['Booking']['web_price'];

								if ($workshop_price > 0) $counts['workshop']++;
								if ($mtm_price > 0)     $is_early ? $counts['mtm_early']++ : $counts['mtm_late']++;
								
							?>
							<td class="r"><?= $workshop_price ?></td>
							<td class="r"><?= $mtm_price ?>
								<?php if ($mtm_price > 0) { ?>
									<div style="font-size:10px"><?= $is_early ? 'early' : 'late' ?></div></td>
								<?php } ?>
							<td class="r"><?php echo h($booking['Booking']['web_price']); ?></td>
						</tr>
					<?php } ?>
						<tr>
							<td colspan="5"><b>Total</b>
							<td class="r"><?= $total['workshop'] ?>
							<td class="r"><?= $total['mtm'] ?>
							<td class="r"><?= $total['grand'] ?>
						</tr>
						<tr>
							<td colspan="5"><b>Counts</b>
							<td class="r" style="font-size:10px">
								<?= $counts['workshop'] ?>x
							<td class="c" style="font-size:10px">
								<?= $counts['mtm_early'] ?>x early<br>
								<?= $counts['mtm_late'] ?>x late<br>
							<td class="r">
						</tr>
					</tbody>
				</table>
			</form>

			<p>
				<small><?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));?></small>
			</p>

			<?php
			$params = $this->Paginator->params();
			if ($params['pageCount'] > 1) {
			?>
			<ul class="pagination pagination-sm">
				<?php
					echo $this->Paginator->prev('&larr; Previous', array('class' => 'prev','tag' => 'li','escape' => false), '<a onclick="return false;">&larr; Previous</a>', array('class' => 'prev disabled','tag' => 'li','escape' => false));
					echo $this->Paginator->numbers(array('separator' => '','tag' => 'li','currentClass' => 'active','currentTag' => 'a'));
					echo $this->Paginator->next('Next &rarr;', array('class' => 'next','tag' => 'li','escape' => false), '<a onclick="return false;">Next &rarr;</a>', array('class' => 'next disabled','tag' => 'li','escape' => false));
				?>
			</ul>
			<?php } ?>

		</div> <!-- end col md 12 -->
	</div><!-- end row -->

	<div class="row">
		<div class="col-md-3">
			<?php echo $this->element('admin_navigation'); ?>
		</div><!-- end col md 3 -->
	</div>

</div>
