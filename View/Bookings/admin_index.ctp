<?php
	//debug($bookings);
	App::import('Model', 'Booking');
	$this->Booking = new Booking();
?>
<div class="bookings index">
	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<ul class="nav nav-pills pull-right">
						<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;' . __('List Payments'), array('action'=>'payments'), array('escape'=>false)); ?></li>
				</ul>
				<ul class="nav nav-pills pull-right">
					<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . __('New Booking'), array('action' => 'add'), array('escape' => false)); ?></li>
				</ul>
				<h1><?php echo __('Bookings'); ?></h1>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-2">
			<?php echo $this->element('admin_navigation'); ?>
		</div>
		<div class="col-md-10">
			<table cellpadding="0" cellspacing="0" class="table table-striped">
				<thead>
					<tr>
						<th><?php echo $this->Paginator->sort('name'); ?></th>
						<th><?php echo $this->Paginator->sort('institution'); ?></th>
						<!--th><?php echo $this->Paginator->sort('country'); ?></th>
						<th><?php echo $this->Paginator->sort('address'); ?></th>
						<th><?php echo $this->Paginator->sort('room_id'); ?></th>
						<th><?php echo $this->Paginator->sort('beds'); ?></th>
						<th><?php echo $this->Paginator->sort('start'); ?></th>
						<th><?php echo $this->Paginator->sort('end'); ?></th-->
						<th><?php echo $this->Paginator->sort('email'); ?></th>
						<th>Items</th>
						<th>Types</th>
						<!--th><?php echo $this->Paginator->sort('fellow_email', 'Fellow'); ?></th-->
						<!--th class="r">Room</th>
						<th class="r">Lunches</th-->
						<th class="r">Total</th>
						<th style="min-width: 175px;">Payment</th>
						<th class="actions"></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($bookings as $booking) { ?>
					<tr>
						<td><?php echo h($booking['Booking']['name']); ?></td>
						<td><?php echo h($booking['Booking']['institution']); ?></td>
						<!--td><?php echo h($booking['Booking']['country']); ?></td>
						<td><?php echo h($booking['Booking']['address']); ?></td>
						<td>
							<?php
								$title = '-';
								if (isset($booking['Room']['name'])) $title = $booking['Room']['name'] . '@' . @$locations[$booking['Room']['location_id']];
								$url = array('controller' => 'bookings', 'action' => 'edit', $booking['Booking']['id']);
								echo @$this->Html->link($title, $url);
							?>
						</td-->
						<!--td>
							<?php echo $this->Html->link($booking['PriceType']['name'], array('controller' => 'price_types', 'action' => 'view', $booking['PriceType']['id'])); ?>
						</td-->
						<!--td class="c">
							<?php if (isset($booking['Room']['id'])) { echo h($booking['Booking']['beds']); } ?>
						</td>
						<td><?php echo $this->Time->format($booking['Booking']['start'], '%-d.%-m.&nbsp;%Y'); ?></td>
						<td><?php echo $this->Time->format($booking['Booking']['end'], '%-d.%-m.&nbsp;%Y'); ?></td-->
						<td style="font-size:11px"><?php echo h($booking['Booking']['email']); ?></td>
						<td>
							<?php
								$codes = implode(', ', Hash::extract($booking['RegItem'], '{n}.key'));
								echo $codes;
							?>
						</td>
						<td>
							<?php
								$codes = implode(', ', Hash::extract($booking['RegType'], '{n}.key'));
								echo $codes;
							?>
						</td>
						<!--td><?php echo h($booking['Booking']['fellow_email']); ?></td-->
						<?php
							$price = $this->Booking->get_price($booking, $per_partes=true);
						?>
						<!--td class="r"><?php echo h($price['accomodation']); ?></td>
						<td class="r"><?php echo h($price['meals']); ?></td-->
						<?php
							$early_set = explode(' ', "4725 5265 5805 6210 6750 7290 5670 6210 6750 7830 8370 8910");
							$late_set  = explode(' ', "5940 6480 7020 8100 8640 9180 6615 7155 7695 9720 10260 10800");
							$total = (int) floor($booking['Booking']['web_price']);
							$is_early = array_search($total, $early_set);
							$is_late  = array_search($total, $late_set);
						?>
						<td style="text-align:right">
							<?php
								$booking_id = $booking['Booking']['id'];
								$token      = $booking['Booking']['token'];
								echo $this->Html->link($booking['Booking']['web_price'], "/pay/$booking_id/$token"); ?>&nbsp;Kč
							<div style="font-size:10px">
							<?php
								echo $is_early ? 'early' : '';
								echo $is_late ? 'late' : '';
								if ($is_late && $booking['Booking']['id'] >= 36 && $booking['Booking']['id'] <= 39) {
									$early_price = $early_set[$is_late];
									$diff = $total - $early_price;
									echo "<br>$total - $early_price = $diff";
								}
							?>
							</div>
						</td>
						<td style="font-size:11px"><?php
							//$statuses = Hash::extract($booking['Payment'], '{n}.status');
							//$statuses = Hash::extract($booking['Payment'], ['(%s): %s', '{n}.id', '{n}.status']);
							$statuses = Hash::format($booking['Payment'], ['{n}.id', '{n}.status'], '%1$d: %2$s');
							$status = implode('<br>', $statuses);
							echo $status;
							if ($booking['Booking']['id'] == 111) echo 'free ticket for memsource - 50% paid via Andrea Belvedere';
						?></td>
						<td class="actions">
							<?php echo $this->Html->link('<span class="glyphicon glyphicon-edit"></span>', array('action' => 'edit', $booking['Booking']['id']), array('escape' => false)); ?>
							<?php echo $this->Form->postLink('<span class="glyphicon glyphicon-remove"></span>', array('action' => 'delete', $booking['Booking']['id']), array('escape' => false), __('Are you sure you want to delete # %s?', $booking['Booking']['id'])); ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

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

</div>
