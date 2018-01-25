<?php
class ControllerPaymentPaybox extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/paybox');
		$this->document->setTitle = $this->language->get('heading_title');
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('paybox', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
        $data['entry_donate_me'] = $this->language->get('entry_donate_me');
        $data['entry_payment_name'] = $this->language->get('entry_payment_name');
		$data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$data['entry_secret_word'] = $this->language->get('entry_secret_word');
        $data['entry_lifetime'] = $this->language->get('entry_lifetime');
        $data['entry_success_url'] = $this->language->get('entry_success_url');
        $data['entry_fail_url'] = $this->language->get('entry_fail_url');
        $data['entry_result_url'] = $this->language->get('entry_result_url');
		$data['copy_result_url'] 	= HTTP_CATALOG . 'index.php?route=payment/paybox/callback';
		$data['copy_success_url']	= HTTP_CATALOG . 'index.php?route=payment/paybox/success';
		$data['copy_fail_url'] 	= HTTP_CATALOG . 'index.php?route=payment/paybox/fail';
        $data['tooltip_payment_name'] = $this->language->get('tooltip_payment_name');
        $data['tooltip_merchant_id'] = $this->language->get('tooltip_merchant_id');
        $data['tooltip_secret_word'] = $this->language->get('tooltip_secret_word');
        $data['tooltip_result_url'] = $this->language->get('tooltip_result_url');
        $data['tooltip_success_url'] = $this->language->get('tooltip_success_url');
        $data['tooltip_fail_url'] = $this->language->get('tooltip_fail_url');
        $data['tooltip_test'] = $this->language->get('tooltip_test');
        $data['tooltip_lifetime'] = $this->language->get('tooltip_lifetime');
        $data['tooltip_order_status'] = $this->language->get('tooltip_order_status');
        $data['tooltip_status'] = $this->language->get('tooltip_status');
        $data['tooltip_sort_order'] = $this->language->get('tooltip_sort_order');
        $data['entry_test'] = $this->language->get('entry_test');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['tab_general'] = $this->language->get('tab_general');

		//
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		//
        if (isset($this->error['payment_name'])) {
            $data['error_payment_name'] = $this->error['payment_name'];
        } else {
            $data['error_payment_name'] = '';
        }

		if (isset($this->error['merchant_id'])) {
			$data['error_merchant_id'] = $this->error['merchant_id'];
		} else {
			$data['error_merchant_id'] = '';
		}

		if (isset($this->error['secret_word'])) {
			$data['error_secret_word'] = $this->error['secret_word'];
		} else {
			$data['error_secret_word'] = '';
		}

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paybox', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);



        $data['action'] = $this->url->link('payment/paybox', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		//
        if (isset($this->request->post['paybox_payment_name'])) {
            $data['paybox_payment_name'] = $this->request->post['paybox_payment_name'];
        } else {
            $data['paybox_payment_name'] = $this->config->get('paybox_payment_name');
        }

		//
		if (isset($this->request->post['paybox_merchant_id'])) {
			$data['paybox_merchant_id'] = $this->request->post['paybox_merchant_id'];
		} else {
			$data['paybox_merchant_id'] = $this->config->get('paybox_merchant_id');
		}


		//
		if (isset($this->request->post['paybox_secret_word'])) {
			$data['paybox_secret_word'] = $this->request->post['paybox_secret_word'];
		} else {
			$data['paybox_secret_word'] = $this->config->get('paybox_secret_word');
		}

        if (isset($this->request->post['paybox_test'])) {
            $data['paybox_test'] = $this->request->post['paybox_test'];
        } else {
            $data['paybox_test'] = $this->config->get('paybox_test');
        }

        if (isset($this->request->post['paybox_lifetime'])) {
            $data['paybox_lifetime'] = $this->request->post['paybox_lifetime'];
        } else {
            $data['paybox_lifetime'] = $this->config->get('paybox_lifetime');
        }

		if (isset($this->request->post['paybox_order_status_id'])) {
			$data['paybox_order_status_id'] = $this->request->post['paybox_order_status_id'];
		} else {
			$data['paybox_order_status_id'] = $this->config->get('paybox_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['paybox_geo_zone_id'])) {
			$data['paybox_geo_zone_id'] = $this->request->post['paybox_geo_zone_id'];
		} else {
			$data['paybox_geo_zone_id'] = $this->config->get('paybox_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['paybox_status'])) {
			$data['paybox_status'] = $this->request->post['paybox_status'];
		} else {
			$data['paybox_status'] = $this->config->get('paybox_status');
		}

		if (isset($this->request->post['paybox_sort_order'])) {
			$data['paybox_sort_order'] = $this->request->post['paybox_sort_order'];
		} else {
			$data['paybox_sort_order'] = $this->config->get('paybox_sort_order');
		}

		$this->template = 'payment/paybox.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('payment/paybox.tpl', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paybox')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        if (!$this->request->post['paybox_payment_name']) {
            $this->error['payment_name'] = $this->language->get('error_payment_name');
        }

		if (!$this->request->post['paybox_merchant_id']) {
			$this->error['merchant_id'] = $this->language->get('error_merchant_id');
		}

		if (!$this->request->post['paybox_secret_word']) {
			$this->error['secret_word'] = $this->language->get('error_secret_word');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>
