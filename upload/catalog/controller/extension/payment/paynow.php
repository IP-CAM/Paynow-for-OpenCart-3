<?php
use Paynow\Payments\Paynow;

require_once('paynow/autoloader.php');
class ControllerExtensionPaymentPaynow extends Controller{
	
	/**
	 * This is as the name suggests. The index.
	 */
	public function index() {

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$data['ref'] = $this->config->get('payment_paynow_store_name') . ': Order' . $order_info['order_id'];
		$data['action'] = $this->url->link('extension/payment/paynow/submit');
		$data['method'] = 'POST';

		return $this->load->view('extension/payment/paynow', $data);
	}

	/**
	 * This is where we will process the submit action
	 */
	public function submit(){
		if($this->request->post){
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$return_url = $this->url->link('extension/payment/paynow/callback/?order_id=' . $order_info['order_id']);
			$result_url = $this->url->link('extension/payment/paynow/result/?order_id=' . $order_info['order_id']);
	
			$paynow = new Paynow(
				$this->config->get('payment_paynow_integration_id'),
				$this->config->get('payment_paynow_integration_key'),
				$return_url,
				$result_url
			);
	
			// get the email address set for the order
			$payment = $paynow->createPayment($_POST['ref'], $order_info['email']);
	
			$data['storename'] = $this->config->get('payment_paynow_store_name') . ' Order';
			$data['amount'] = $this->currency->format( $order_info[ 'total' ], $order_info[ 'currency_code' ], '', false );

			$payment->add($data['storename'], (int)$data['amount']);
	
			$response = $paynow->send($payment);

			$payment_paynow_order = $response->data();
			// UPDATE users set password 
			if ($response->success()){
				$this->db->query("UPDATE " 
					. DB_PREFIX . "order SET payment_custom_field = '" 
					. json_encode($payment_paynow_order)  . "', order_status_id = '1' WHERE order_id='" 
					. $order_info['order_id'] . "'");

				header("Location:" . $response->redirectUrl());	
			} else {
				echo $response->errors();
			}
		}
	}

	/**
	 * This is where we go to when redirected from paynow
	 */
	public function callback(){

		$completed_url = $this->url->link( 'checkout/success' );
		$cancelled_url = $this->url->link( 'checkout/checkout' );
		$order_id = $this->getOrderIdFromRoute();

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$payment_paynow_order = $order_info['payment_custom_field']; 
		
		$paynow = new Paynow(
			$this->config->get('payment_paynow_integration_id'),
			$this->config->get('payment_paynow_integration_key'),
			'',
			''
		);
		
		$response = $paynow->pollTransaction($payment_paynow_order['pollurl']);
		
		$this->updateCustomOrderData($order_id, $response->data());

		switch($response->data()['status']){
			case 'Awaiting Delivery': 
				// $this->updateOrderStatus($order_id, 2);
				header('location:' . $completed_url);
				break;
			case 'Paid': 
				// $this->updateOrderStatus($order_id, 2);
				header('location:' . $completed_url);
				break;
			case 'Delivered': 
				// $this->updateOrderStatus($order_id, 2);
				header('location:' . $completed_url);
				break;
			case 'Cancelled': 
				// $this->updateOrderStatus($order_id, 7);
				header('refresh: 5;url=' . $cancelled_url);
				echo '<h1 style="text-align: center;">Order Cancelled</h1>';
				break;
			default: 
				// $this->updateOrderStatus($order_id, 10);
				header('refresh: 5;url=' . $cancelled_url);
				echo '<h1 style="text-align: center;">Order processing failed.</h1>';
		}
	}

	/**
	 * This is where paynow will post the results of the txn
	 */
	public function result(){
		if ($_POST['pollurl']){
			$paynow = new Paynow(
				$this->config->get('payment_paynow_integration_id'),
				$this->config->get('payment_paynow_integration_key'),
				'',
				''
			);

			$order_id = $this->getOrderIdFromRoute();

			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);
			$payment_paynow_order = $order_info['payment_custom_field']; 

			$response = $paynow->pollTransaction($payment_paynow_order['pollurl']);

			$this->updateCustomOrderData($order_id, $response->data());

			switch($response->data()['status']){
				case 'Awaiting Delivery': 
					$this->updateOrderStatus($order_id, 2);
					break;
				case 'Paid': 
					$this->updateOrderStatus($order_id, 2);
					break;
				case 'Delivered': 
					$this->updateOrderStatus($order_id, 2);
					break;
				case 'Cancelled': 
					$this->updateOrderStatus($order_id, 7);
					break;
				default: 
					$this->updateOrderStatus($order_id, 10);
			}
		}
	}

	private function updateOrderStatus($order_id, $status_id){
		$this->db->query("UPDATE " 
			. DB_PREFIX . "order SET order_status_id = " . $status_id . " WHERE order_id='" 
			. $order_id . "'");
	}

	private function updateCustomOrderData($order_id, $data){
		$this->db->query("UPDATE " 
			. DB_PREFIX . "order SET payment_custom_field = '" 
			. json_encode($data) . "' WHERE order_id='" 
			. $order_id . "'");
	}

	private function getOrderIdFromRoute(){
		$route = $this->request->get['route'];
		$get = explode('?', $route);
		$order_id = explode('=', $get[1])[1];

		return $order_id;
	}
}