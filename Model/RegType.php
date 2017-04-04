<?php
App::uses('AppModel', 'Model');

class RegType extends AppModel {
	public $displayField = 'key';
	public $order = array('RegType.ord'=>'asc');
}
