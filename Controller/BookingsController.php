<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakePdf', 'CakePdf.Pdf');

class BookingsController extends AppController {
	public $layout = 'BootstrapCake.bootstrap';

	public $components = array('Paginator', 'Session');

	public $paginate = array(
		'limit' => 150,
		'maxLimit' => 200,
		'order' => 'Booking.id'
	);

	// declare public actions
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('add', 'edit', 'invoice');
	}

	public function add() {
		if ($this->request->is('post')) {
			$room_id = @$this->request->data['Booking']['room_id'];
			$beds = @$this->request->data['Booking']['beds'];
			// create a security token
			$token = uniqid();
			$this->request->data['Booking']['token'] = $token;
			$this->Booking->create();
			if ($this->Booking->save($this->request->data)) {
				$booking_id = $this->Booking->id;
				// decrement amount_available
				if ($room_id) {
					$this->Booking->Room->id = $room_id;
					$this->Booking->Room->updateAll(
						array('Room.amount_left' => 'Room.amount_left - ' . $beds),
						array('Room.id' => $room_id)
					);
				}
				
				if ($this->request->data['Booking']['web_price'] == '0.00') {
					$this->zero_receipt($booking_id);
				} else {
					$this->email_add_notice();
				}
				
				$this->Session->setFlash(__('The registration has been saved.'), 'default', array('class' => 'alert alert-success'));

				return $this->redirect("/pay/$booking_id/$token");
				//return $this->redirect('/thank-you');
			} else {
				$this->Session->setFlash(__('The registration could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		}
	}

	public function edit($token = null) {
		$conditions = array('Booking.token' => $token);
		$booking = $this->Booking->find('first', compact('conditions'));
		if (!$booking) {
			throw new NotFoundException(__('Invalid booking'));
		}
		$id = $booking['Booking']['id'];
		if ($this->request->is(array('post', 'put'))) {
			$allowed_fields = array(
				'name' => 1,
				'institution' => 1,
				'country' => 1,
				'address' => 1,
				'email' => 1,
				'fellow_email' => 1,
			);
			$sanitized_data = array();
			$sanitized_data['Booking'] = array_intersect_key($this->request->data['Booking'], $allowed_fields);
			$sanitized_data['Booking']['id'] = $id;
			if (@$this->request->data['Query']) $sanitized_data['Query'] = $this->request->data['Query'];

			if ($this->Booking->save($sanitized_data)) {
				$this->Session->setFlash(__('The registration has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect('/edit/'.$token);
			} else {
				$this->Session->setFlash(__('The registration could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		} else {
			$options = array('conditions' => array('Booking.' . $this->Booking->primaryKey => $id));
			$this->request->data = $this->Booking->find('first', $options);
		}
		$this->request->data = $booking;
		$priceTypes          = $this->Booking->PriceType->find('list');
		$upsells             = $this->Booking->Upsell->find('list');
		$meals               = $this->Booking->Meal->find('list');
		$queries             = $this->Booking->Query->find('list');
		$rooms               = $this->Booking->Room->find('list');
		$locations           = $this->Booking->Room->Location->find('list');
		$location_desc       = $this->Booking->Room->Location->find('list', array('fields'=>array('id', 'desc')));
		$this->set(compact('rooms', 'priceTypes', 'upsells', 'meals', 'queries', 'locations', 'location_desc'));
	}

	public function admin_index() {
		$this->Booking->bindModel(array(
			'hasMany' => array(
				'Payment' => array(
					'className' => 'Payment.Payment',
					'conditions' => array(
						'Payment.status !=' => null
					),
					'order' => 'Payment.id ASC'
				)
			))
		);

		$this->Booking->recursive = 1;
		$this->Paginator->settings = $this->paginate;
		$this->set('bookings', $this->Paginator->paginate());
		//$this->set('bookings', $this->Booking->find('all'));
		$this->set('locations', $this->Booking->Room->Location->find('list'));
	}

	public function admin_payments() {
		$this->Booking->bindModel(array(
			'hasMany' => array(
				'Payment' => array(
					'className' => 'Payment.Payment',
					'conditions' => array(
						'Payment.status' => 'confirmed ok'
					),
					'order' => 'Payment.id ASC'
				)
			))
		);

		$this->Booking->recursive = 1;
		$joins = array(
			array(
				'table' => 'payments',
				'alias' => 'Payment',
				'type' => 'INNER',
				'conditions' => array(
					'Payment.booking_id = Booking.id',
					'Payment.status' => 'confirmed ok',
				)
			)
		);
		$this->Paginator->settings = array(
			'limit' => 75,
			'joins' => $joins,
			'order' => 'Payment.id'
		);
		$this->set('bookings', $this->Paginator->paginate());
		//$this->set('bookings', $this->Booking->find('all', array('joins'=>$joins)));
		$this->set('locations', $this->Booking->Room->Location->find('list'));
	}

	public function admin_view($id = null) {
		if (!$this->Booking->exists($id)) {
			throw new NotFoundException(__('Invalid booking'));
		}
		$options = array('conditions' => array('Booking.' . $this->Booking->primaryKey => $id));
		$this->set('booking', $this->Booking->find('first', $options));
	}

	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Booking->create();
			if ($this->Booking->save($this->request->data)) {
				$this->Session->setFlash(__('The booking has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The booking could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		}
		$rooms = $this->Booking->Room->find('list');
		$priceTypes = $this->Booking->PriceType->find('list');
		$upsells = $this->Booking->Upsell->find('list');
		$meals = $this->Booking->Meal->find('list');
		$queries = $this->Booking->Query->find('list');
		$this->set(compact('rooms', 'priceTypes', 'upsells', 'meals', 'queries'));
	}

	public function admin_edit($id = null) {
		if (!$this->Booking->exists($id)) {
			throw new NotFoundException(__('Invalid booking'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Booking->save($this->request->data)) {
				$this->Session->setFlash(__('The booking has been saved.'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The booking could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
			}
		} else {
			$options = array('conditions' => array('Booking.' . $this->Booking->primaryKey => $id));
			$this->request->data = $this->Booking->find('first', $options);
		}
		$rooms = $this->Booking->Room->find('list');
		$priceTypes = $this->Booking->PriceType->find('list');
		$upsells = $this->Booking->Upsell->find('list');
		$meals = $this->Booking->Meal->find('list');
		$queries = $this->Booking->Query->find('list');
		$this->set(compact('rooms', 'priceTypes', 'upsells', 'meals', 'queries'));
	}

	public function admin_delete($id = null) {
		$this->Booking->id = $id;
		if (!$this->Booking->exists()) {
			throw new NotFoundException(__('Invalid booking'));
		}
		$booking = $this->Booking->findById($id);
		$room_id = $booking['Booking']['room_id'];
		$beds    = $booking['Booking']['beds'];
		$this->request->onlyAllow('post', 'delete');
		if ($this->Booking->delete()) {
			// credit room beds
			$this->Booking->Room->id = $room_id;
			$this->Booking->Room->updateAll(
				array('Room.amount_left' => 'Room.amount_left + ' . $beds),
				array('Room.id' => $room_id)
			);
			$this->Session->setFlash(__('The booking has been deleted.'), 'default', array('class' => 'alert alert-success'));
		} else {
			$this->Session->setFlash(__('The booking could not be deleted. Please, try again.'), 'default', array('class' => 'alert alert-danger'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	private function email_add_notice() {
		$content = array();
		$content[] = 'Your registration details were saved!';
		$content[] = '';
		$content[] = 'You may review your registration details and find the payment link at:';
		$content[] = Router::url('/edit/' . $this->Booking->field('token'), true);
		$owner_mail = $this->Booking->field('email');
		$to = array($owner_mail);

		$Email = new CakeEmail('default');
		$Email->to($to);
		$Email->send($content);
	}

	public function mail_all() {
		$conditions = array(
			'Booking.web_price >' => 0
		);
		$bookings = $this->Booking->find('all', compact('conditions'));

		foreach ($bookings as $booking) {
			echo("sending email for " . $booking['Booking']['email']. "<br>");
			$this->Booking->clear();
			$this->Booking->save(array(
				'id' =>  $booking['Booking']['id'],
				'paylink_sent' => date('Y-m-d H:i:s')
			));
			$this->send_link_to_payment($booking['Booking']['id']);
		}
	}

	public function mail_one($booking_id) {
		$conditions = array(
			'Booking.id' => $booking_id
		);
		$booking = $this->Booking->find('first', compact('conditions'));

		echo("sending email for " . $booking['Booking']['email']. "<br>");
		$this->Booking->clear();
		$this->Booking->save(array(
			'id' =>  $booking['Booking']['id'],
			'paylink_sent' => date('Y-m-d H:i:s')
		));
		$this->send_link_to_payment($booking['Booking']['id']);
	}

	public function price_all() {
		$bookings = $this->Booking->find('all');

		foreach ($bookings as $booking) {
			$price = $this->Booking->get_price($booking);

			$this->Booking->clear();
			$this->Booking->save(array(
				'id' =>  $booking['Booking']['id'],
				'web_price' => $price
			));
		}

		debug('OK');
	}

	/**
	 * payment email
	 */
	private function send_link_to_payment($booking_id) {
		$booking_id = (int) $booking_id;
		$cnd = array( 'Booking.id' => $booking_id );

		$booking = $this->Booking->find('first', array('conditions'=>$cnd));
		if (!$booking) {
			echo 'Payment not found.';
			exit();
		}

		$this->set_request_scheme();
		$token = $booking['Booking']['token'];
		$name = $booking['Booking']['name'];
		$subject = 'MTM 18 Registration Payment';
		$to = $booking['Booking']['email'];
		$to = array($to);

		$content = array();
		$content[] = "Dear $name.";
		$content[] = '';
		$content[] = 'Thank you for registering at MTM 18. To confirm your booking, you need to make the payment *before July 31*.';
		$content[] = '';
		$content[] = 'Please review your registration details and proceed to the payment at ';
		$content[] = Router::url("/pay/$booking_id/$token", $absolute=true);
		$content[] = '';
		$content[] = 'Take care, the MTM 18 team.';

		$Email = new CakeEmail('default');
		$Email->to($to);
		$Email->subject($subject);
		$Email->send($content);
	}

	public function invoice() {
		$payment_id = 1049;
		$this->loadModel('Payment.Payment');
		$this->Payment->recursive = 2;
		$payment = $this->Payment->findById($payment_id);
		$viewVars = $payment;
		$reg_type = @$payment['Booking']['RegType'][0]['key'];
		$viewVars['reg_type'] = $reg_type;

		$this->set_request_scheme();
		$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
		$token = $payment['Payment']['token'];

		$CakePdf = new CakePdf(Configure::read('CakePdfDomPdf'));
		$CakePdf->template('receipt', 'default');
		$CakePdf->viewVars($viewVars);
		$receipt = 'receipt-'.$payment['Booking']['id'].'.pdf';
		$pdf = $CakePdf->write(APP . 'files' . DS . $receipt);
		$this->render('../Pdf/receipt');
	}
	
	public function zero_receipt($booking_id) {
		$this->Booking->recursive = 2;
		$booking = $this->Booking->findById($booking_id);
		$viewVars = $booking;
		//$reg_type = $booking['Booking']['RegType'][0]['key'];
		$viewVars['reg_type'] = '';
		$viewVars['Booking']['RegItem'] = $booking['RegItem'];

		$this->set_request_scheme();
		$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];

		$CakePdf = new CakePdf(Configure::read('CakePdfDomPdf'));
		$CakePdf->template('zero_receipt', 'default');
		$CakePdf->viewVars($viewVars);
		$receipt = 'receipt-'.$booking['Booking']['id'].'.pdf';
		$pdf = $CakePdf->write(APP . 'files' . DS . $receipt);
		$this->set($viewVars);
		
		$attachments = array(
			'logo.png' => array(
				'file' => WWW_ROOT . '/images/logo.png',
				'mimetype' => 'image/png',
				'contentId' => 'logo'
			),
			'receipt.pdf' => array(
				'file' => APP . 'files' . DS . $receipt,
				'mimetype' => 'application/pdf'
			)
		);

		$subject = 'Your MTM 18 registration';
		$to = $booking['Booking']['email'];

		$Email = new CakeEmail('default');
		$Email->template('zero_receipt')
			->emailFormat('html')
			->to($to)
			->subject($subject)
			->viewVars($viewVars)
			->attachments($attachments);
		$Email->send();
		
		
		//$this->render('../Pdf/zero_receipt');
	}
}
