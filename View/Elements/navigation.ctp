<?php
	$links = array(
	);
?>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<!--a class="navbar-brand" href="<?php echo Router::url('/');?>">MTM 18</a-->
				<a class="navbar-brand" href="https://ufal.mff.cuni.cz/mtm18/">MTM 18</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<?php
						foreach ($links as $title => $url) {
							$link = $this->Html->link(__($title), $url);
							$options = array();
								if (strpos($this->request->here, Router::url($url)) === 0) { // detect query string + params
									$options = array('class' => 'active');
								}
								echo $this->Html->tag('li', $link, $options);
						}
					?>
				</ul>
				<?php if (AuthComponent::user('id')) { ?>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->Session->read('Auth.User.username'); ?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li> <a href="<?php echo Router::url('/admin/reg_types');?>"><?php echo __("Config"); ?></a> </li>
								<li class="divider"></li>
								<li> <a href="<?php echo Router::url('/logout');?>"><?php echo __("Logout"); ?></a> </li>
							</ul>
						</li>
					</ul>
				<?php } else { ?>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="<?php echo Router::url('/admin');?>"><?php echo __("admin"); ?></a></li>
					</ul>
				<?php } ?>
			</div>
		</div>
	</div>
