<?php
App::uses('AppController', 'Controller');
class RegTypesController extends AppController {
	public $layout = 'BootstrapCake.bootstrap';
	public $components = array('Paginator', 'Session');
	public $uses = array('RegType', 'Configuration');

	public function admin_index() {
		$this->RegType->recursive = 0;
		$this->set('regTypes', $this->Paginator->paginate());

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

	public function admin_reorder() {
		if ($this->request->is('post')) {
			$this->RegType->saveMany($this->request->data);
			exit();
		}
		$this->set('regTypes', $this->RegType->find('all'));
	}

	public function admin_view($id = null) {
		if (!$this->RegType->exists($id)) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		$options = array('conditions' => array('RegType.' . $this->RegType->primaryKey => $id));
		$this->set('regType', $this->RegType->find('first', $options));
	}

	public function admin_add() {
		if ($this->request->is('post')) {
			$this->RegType->create();
			if ($this->RegType->save($this->request->data)) {
				$this->Session->setFlash(__('The reg item has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The reg item could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		}
	}

	public function admin_edit($id = null) {
		if (!$this->RegType->exists($id)) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->RegType->save($this->request->data)) {
				$this->Session->setFlash(__('The reg item has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The reg item could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		} else {
			$options = array('conditions' => array('RegType.' . $this->RegType->primaryKey => $id));
			$this->request->data = $this->RegType->find('first', $options);
		}
	}

	public function admin_delete($id = null) {
		$this->RegType->id = $id;
		if (!$this->RegType->exists()) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->RegType->delete()) {
			$this->Session->setFlash(__('The reg item has been deleted.'), 'default', array('class' => 'alert alert-success'));
		} else {
			$this->Session->setFlash(__('The reg item could not be deleted. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
