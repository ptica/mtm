<div class="queries index">
	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<ul class="nav nav-pills pull-right">
					<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . __('New Reg Type'), array('action' => 'add'), array('escape' => false)); ?></li>
					<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-sort"></span>&nbsp;&nbsp;' . __('Reorder'), array('action' => 'reorder'), array('escape' => false)); ?></li>
					<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;' . __('Prices Spreadsheet'), 'https://docs.google.com/spreadsheets/d/1D2OpAdVu01xhcbVqQv3N9YVg4FpSAPbIw5_igi-JsBQ/edit#gid=0', array('escape' => false)); ?></li>
				</ul>
				<h1><?php echo __('Reg Types'); ?></h1>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3">
			<?php echo $this->element('admin_navigation'); ?>
					</div><!-- end col md 3 -->

		<div class="col-md-9">
			<div id="sortableQueries" class="list-group" data-reorder-url="/admin/reg_types/reorder">
				<?php foreach ($regTypes as $item) {?>
					<div class="list-group-item" data-item-id="<?php echo h($item['RegType']['id']); ?>">
						<span class="glyphicon glyphicon-move" aria-hidden="true"></span>
						<?php echo $this->Html->link($item['RegType']['key'], array('action' => 'index', $item['RegType']['id'])); ?>					</div>
				<?php } ?>
			</div>
		</div> <!-- end col md 9 -->
	</div><!-- end row -->

</div>
