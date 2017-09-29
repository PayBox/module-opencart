<?php
class ControllerExtensionPaymentPayboxFail extends Controller {
	public function index() {

		if ( isset($this->session->data['order_id']) && ( ! empty($this->session->data['order_id']))  ) {
			$this->session->data['last_order_id'] = $this->session->data['order_id'];
		}

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
		}

		$this->language->load('extension/payment/paybox');

		if (! empty($this->session->data['last_order_id']) ) {
			$this->document->setTitle(sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']));
		} else {
    		$this->document->setTitle($this->language->get('heading_title'));
		}

		$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);

		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', true),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);

      	$data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/failure'),
        	'text'      => $this->language->get('text_fail'),
        	'separator' => $this->language->get('text_separator')
      	);

		if (! empty($this->session->data['last_order_id']) ) {
			$data['heading_title'] = sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']);
		} else {
    		$data['heading_title'] = $this->language->get('heading_title');
		}

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/order/info&order_id=' . $this->session->data['last_order_id'], '', true), $this->session->data['last_order_id'], $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
    		$data['text_message'] = sprintf($this->language->get('text_guest'), $this->session->data['last_order_id'], $this->url->link('information/contact'));
		}

    	$data['button_continue'] = $this->language->get('button_continue');

    	$data['continue'] = $this->url->link('common/home');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . 'extension/payment/paybox/fail.tpl')) {
			$this->template = $this->config->get('config_template') . 'extension/payment/paybox/fail.tpl';
		} else {
			$this->template = 'extension/payment/paybox/fail.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->load->view($this->template, $data));
  	}
}
?>
