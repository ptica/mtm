<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * @link		  http://cakephp.org CakePHP(tm) Project
 * @package	   app.Model
 * @since		 CakePHP(tm) v 0.2.9
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package	   app.Model
 */
class AppModel extends Model {
	/**
	 * various date to sql format
	 */
	public function date_to_sql($from) {
		// \xC2\xA0 is &nbsp; entity
		$from = preg_replace("/(\s|\xc2\xa0)+/", '', $from);
		// missing dot
		if (preg_match('/^\d{1,2}\.\d{1,2}$/', $from))
			$from = $from . '.';
		// missing year
		if (preg_match('/^\d{1,2}\.\d{1,2}.$/', $from))
			$from = $from . date('Y');
		($date = date_create_from_format('j#n#y', $from))	   # d-m-y
		|| ($date = date_create_from_format('j#n#Y', $from))	# d-m-yyyy
		|| ($date = date_create_from_format('j#n#Y', date('j.n.Y', strtotime($from))));
		return date_format($date, 'Y-m-d');
	}
	public function sql_to_date($date) {

	}
	public function convert_date_fields() {
		if ($this->dateFields) foreach ($this->dateFields as $date_field) if (!empty($this->data[$this->alias][$date_field])) {
			$this->data[$this->alias][$date_field] = $this->date_to_sql($this->data[$this->alias][$date_field]);
		}
	}

	public function beforeSave($options = array()) {
		$this->convert_date_fields();
		return true;
	}

	/**
	 * so virtualField Room.fullname could access Location info
	 */
	function beforeFind($query) {
		// for every Room we JOIN Location
		// NEW: except when we just want a list?
		// NEW: except when we just want a field?
		// DISABLED NOW as it joins in unwanted situations as well
		if (0 && $this->Room && !@$query['list'] && !@$query['fields']) {
			$query['joins'][] = array(
				"table" => "locations",
				"alias" => "Location",
				"type" => "INNER",
				"conditions" => array("Room.location_id = Location.id")
			);
		}
		// for every Room->find('list') we upgrade the recursive level
		if (0 && get_class($this) == 'Room') {
			if (@$query['recursive'] < 0) $query['recursive'] = 0;
		}
		return $query;
	}
}
