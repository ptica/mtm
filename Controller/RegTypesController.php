<?php
App::uses('AppController', 'Controller');
class RegTypesController extends AppController {
	public $layout = 'BootstrapCake.bootstrap';
	public $components = array('Paginator', 'Session');
	public $uses = array('Configuration');

	public function admin_index() {
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Configuration->save($this->request->data)) {
				$this->Session->setFlash(__('The configuration has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The configuration could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		}
		$late_reg_start = $this->Configuration->findByName('late_registration_start_date');
		$this->request->data = $late_reg_start;
	}
}
