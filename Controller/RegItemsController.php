<?php
App::uses('AppController', 'Controller');
class RegItemsController extends AppController {
	public $layout = 'BootstrapCake.bootstrap';

	public $components = array('Paginator', 'Session', 'Flash');


	public function admin_index() {
		$this->RegItem->recursive = 0;
		$this->set('regItems', $this->Paginator->paginate());
	}

	public function admin_reorder() {
		if ($this->request->is('post')) {
			$this->RegItem->saveMany($this->request->data);
			exit();
		}
		$this->set('regItems', $this->RegItem->find('all'));
	}

	public function admin_view($id = null) {
		if (!$this->RegItem->exists($id)) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		$options = array('conditions' => array('RegItem.' . $this->RegItem->primaryKey => $id));
		$this->set('regItem', $this->RegItem->find('first', $options));
	}

	public function admin_add() {
		if ($this->request->is('post')) {
			$this->RegItem->create();
			if ($this->RegItem->save($this->request->data)) {
				$this->Session->setFlash(__('The reg item has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The reg item could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		}
	}

	public function admin_edit($id = null) {
		if (!$this->RegItem->exists($id)) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->RegItem->save($this->request->data)) {
				$this->Session->setFlash(__('The reg item has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The reg item could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		} else {
			$options = array('conditions' => array('RegItem.' . $this->RegItem->primaryKey => $id));
			$this->request->data = $this->RegItem->find('first', $options);
		}
	}

	public function admin_delete($id = null) {
		$this->RegItem->id = $id;
		if (!$this->RegItem->exists()) {
			throw new NotFoundException(__('Invalid reg item'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->RegItem->delete()) {
			$this->Session->setFlash(__('The reg item has been deleted.'), 'default', array('class' => 'alert alert-success'));
		} else {
			$this->Session->setFlash(__('The reg item could not be deleted. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
