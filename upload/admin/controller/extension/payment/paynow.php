<?php
class ControllerExtensionPaymentPaynow extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		//Load the language file for this module
		$this->load->language('extension/payment/paynow');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle($this->language->get('heading_title'));
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_paynow', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}
	
		//This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['paynow_integration_id'])) {
			$data['error_paynow_integration_id'] = $this->error['paynow_integration_id'];
		} else {
			$data['error_paynow_integration_id'] = '';
		}

		if (isset($this->error['paynow_integration_key'])) {
			$data['error_paynow_integration_key'] = $this->error['paynow_integration_key'];
		} else {
			$data['error_paynow_integration_key'] = '';
		}

		if (isset($this->error['paynow_store_name'])) {
			$data['error_paynow_store_name'] = $this->error['paynow_store_name'];
		} else {
			$data['error_paynow_store_name'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/paynow', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['action'] = $this->url->link('extension/payment/paynow', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_paynow_integration_id'])) {
			$data['payment_paynow_integration_id'] = $this->request->post['payment_paynow_integration_id'];
		} else {
			$data['payment_paynow_integration_id'] = $this->config->get('payment_paynow_integration_id');
		}		

		if (isset($this->request->post['payment_paynow_integration_key'])) {
			$data['payment_paynow_integration_key'] = $this->request->post['payment_paynow_integration_key'];
		} else {
			$data['payment_paynow_integration_key'] = $this->config->get('payment_paynow_integration_key');
		}

		if (isset($this->request->post['payment_paynow_store_name'])) {
			$data['payment_paynow_store_name'] = $this->request->post['payment_paynow_store_name'];
		} else {
			$data['payment_paynow_store_name'] = $this->config->get('payment_paynow_store_name');
		}

		if (isset($this->request->post['payment_paynow_status'])) {
			$data['payment_paynow_status'] = $this->request->post['payment_paynow_status'];
		} else {
			$data['payment_paynow_status'] = $this->config->get('payment_paynow_status');
		}

		// probably not necessary
		if (isset($this->request->post['payment_paynow_total'])) {
			$data['payment_paynow_total'] = $this->request->post['payment_paynow_total'];
		} else {
			$data['payment_paynow_total'] = $this->config->get('payment_paynow_total');
		}

		if (isset($this->request->post['payment_paynow_geo_zone_id'])) {
			$data['payment_paynow_geo_zone_id'] = $this->request->post['payment_paynow_geo_zone_id'];
		} else {
			$data['payment_paynow_geo_zone_id'] = $this->config->get('payment_paynow_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		// Sort order
		if (isset($this->request->post['payment_paynow_sort_order'])) {
			$data['payment_paynow_sort_order'] = $this->request->post['payment_paynow_sort_order'];
		} else {
			$data['payment_paynow_sort_order'] = $this->config->get('payment_paynow_sort_order');
		}

		// Send the output.
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/paynow', $data));
	}
	
	/*
	 * 
	 * This function is called to ensure that the settings chosen by the admin user are allowed/valid.
	 * You can add checks in here of your own.
	 * 
	 */
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paynow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['payment_paynow_integration_id']) {
			$this->error['paynow_integration_id'] = $this->language->get('error_paynow_integration_id');
		}

		if (!$this->request->post['payment_paynow_integration_key']) {
			$this->error['paynow_integration_key'] = $this->language->get('error_paynow_integration_key');
		}

		if (!$this->request->post['payment_paynow_store_name']) {
			$this->error['paynow_store_name'] = $this->language->get('error_paynow_store_name');
		}

		return !$this->error;
	}
}
?>