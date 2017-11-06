<div class="regItems form">

	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<ul class="nav nav-pills pull-right">
					<li><?php echo $this->Form->postLink('<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;' . __('Delete'), array('action'=>'delete', $this->Form->value('RegItem.key')), array('escape'=>false), __('Are you sure you want to delete # %s?', $this->Form->value('RegItem.key'))); ?></li>
					<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;' . __('List Reg Items'), array('action'=>'index'), array('escape'=>false)); ?></li>
				</ul>
				<h1><?php echo __('Admin Edit Reg Item'); ?></h1>
			</div>
		</div>
	</div>



	<div class="row">
		<div class="col-md-3">
			<?php echo $this->element('admin_navigation'); ?>
		</div><!-- end col md 3 -->
		<div class="col-md-9">
			<?php echo $this->Form->create('RegItem', array('role'=>'form', 'class'=>'form-horizontal')); ?>
				<?php echo $this->Form->hidden('id'); ?>
				<div class="form-group">
					<?php echo $this->Form->input('key', array('class'=>'form-control', 'placeholder'=>__('Key')));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->input('desc', array('data-provide'=>'wysiwyg', 'class'=>'form-control', 'placeholder'=>__('Desc')));?>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-8">
						<?php echo $this->Form->submit(__('Submit'), array('class'=>'btn btn-primary')); ?>
					</div>

				</div>

			<?php echo $this->Form->end() ?>

		</div><!-- end col md 12 -->
	</div><!-- end row -->
</div>
