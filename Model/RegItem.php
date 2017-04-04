<?php
App::uses('AppModel', 'Model');

class RegItem extends AppModel {
	public $displayField = 'key';
	public $order = array('RegItem.ord'=>'asc');
}
