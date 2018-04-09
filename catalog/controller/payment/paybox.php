<?php
class ControllerPaymentPaybox extends Controller {
	protected function index() {
        $this->language->load('payment/paybox');

		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');

		$this->load->model('account/order');
		$order_products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
		$strOrderDescription = "";
		foreach($order_products as $product){
			$strOrderDescription .= @$product["name"]." ".@$product["model"]."*".@$product["quantity"].";";
		}

		$this->load->model('payment/paybox');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // Настройки

        $merchant_id = $this->config->get('paybox_merchant_id');
        $secret_word = $this->config->get('paybox_secret_word');
        $lifetime = $this->config->get('paybox_lifetime');

        $msg_description = $this->language->get('msg_description');

        $this->load->model('payment/paybox');

		$strCurrency = "RUR";
		if($order_info['currency_code'] == "RUB")
			$strCurrency = "RUR";
		else
			$strCurrency = $order_info['currency_code'];

        $arrReq = array(
            'pg_amount'         => (int)$order_info['total'],
            'pg_check_url'      => HTTPS_SERVER . 'index.php?route=payment/paybox/check',
            'pg_description'    => $strOrderDescription,
            'pg_encoding'       => 'UTF-8',
			'pg_currency'       => $strCurrency,
			'pg_user_ip'		=> $_SERVER['REMOTE_ADDR'],
            'pg_lifetime'       => !empty($lifetime) ? $lifetime * 3600 : 86400,
            'pg_merchant_id'    => $merchant_id,
            'pg_order_id'       => $order_info['order_id'],
            'pg_result_url'     => HTTPS_SERVER . 'index.php?route=payment/paybox/callback',
			'pg_request_method'	=> 'POST',
            'pg_salt'           => rand(21, 43433),
            'pg_success_url'    => HTTPS_SERVER . 'index.php?route=checkout/paybox_success',
            'pg_failure_url'    => HTTPS_SERVER . 'index.php?route=checkout/paybox_fail',
            'pg_user_ip'        => $_SERVER['REMOTE_ADDR'],
            'pg_user_phone'     => $order_info['telephone'],
            'pg_user_contact_email' => $order_info['email']
        );

        if($this->config->get('paybox_test') == 1) {
			$arrReq['pg_testing_mode'] = 1;
			unset($arrReq['pg_currency']);
        }

        $arrReq['pg_sig'] = $this->model_payment_paybox->make('payment.php', $arrReq, $secret_word);

        $query = http_build_query($arrReq);

        $this->data['action'] = 'https://api.paybox.money/payment.php?' . $query;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paybox.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/paybox.tpl';
        } else {
            $this->template = 'default/template/payment/paybox.tpl';
        }

        $this->render();

    }

    public function check() {

        $this->language->load('payment/paybox');
        $this->load->model('checkout/order');
        $this->load->model('payment/paybox');

        $arrResponse = array();

       	if(!empty($this->request->post))
			$data = $this->request->post;
		else
			$data = $this->request->get;

        $pg_sig = !empty($data['pg_sig'])?$data['pg_sig']:'';
        unset($data['pg_sig']);

        $secret_word = $this->config->get('paybox_secret_word');

        if(!$this->model_payment_paybox->checkSig($pg_sig, 'index.php', $data, $secret_word)) {
            die('Incorrect signature!');
        }

        // Получаем информацию о заказе
        $order_id = $data['pg_order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $arrResponse['pg_salt'] = $data['pg_salt'];

        if(isset($order_info['order_id'])) {
            $arrResponse['pg_status'] = 'ok';
            $arrResponse['pg_description'] = '';
        } else {
            $arrResponse['pg_status'] = 'rejected';
            $arrResponse['pg_description'] = $this->language->get('err_order_not_found');
        }

        $arrResponse['pg_sig'] = $this->model_payment_paybox->make('index.php', $arrResponse, $secret_word);

        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        echo "<response>\r\n";
        echo "<pg_salt>" . $arrResponse['pg_salt'] . "</pg_salt>\r\n";
        echo "<pg_status>" .$arrResponse['pg_status'] . "</pg_status>\r\n";
        echo "<pg_description>" . htmlentities($arrResponse['pg_description']). "</pg_description>\r\n";
        echo "<pg_sig>" . $arrResponse['pg_sig'] . "</pg_sig>\r\n";
        echo "</response>";

    }

    public function callback() {

        $this->language->load('payment/paybox');
        $this->load->model('payment/paybox');
        $this->load->model('checkout/order');

        $arrResponse = array();

		if(!empty($this->request->post))
			$data = $this->request->post;
		else
			$data = $this->request->get;

        $pg_sig = $data['pg_sig'];
        unset($data['pg_sig']);

        $secret_word = $this->config->get('paybox_secret_word');

        if(!$this->model_payment_paybox->checkSig($pg_sig, 'index.php', $data, $secret_word)) {
            die('Incorrect signature!');
        }

        // Получаем информацию о заказе
        $order_id = $data['pg_order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $arrResponse['pg_salt'] = $data['pg_salt'];

		if($data['pg_result'] != 1){
			$arrResponse['pg_status'] = 'failed';
            $arrResponse['pg_error_description'] = '';
		}
        elseif(isset($order_info['order_id'])) {
            $arrResponse['pg_status'] = 'ok';
            $arrResponse['pg_error_description'] = '';
        } else {
            $arrResponse['pg_status'] = 'rejected';
            $arrResponse['pg_error_description'] = $this->language->get('err_order_not_found');
        }

        $arrResponse['pg_sig'] = $this->model_payment_paybox->make('index.php', $arrResponse, $secret_word);

        header('Content-type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        echo "<response>\r\n";
        echo "<pg_salt>" . $arrResponse['pg_salt'] . "</pg_salt>\r\n";
        echo "<pg_status>" .$arrResponse['pg_status'] . "</pg_status>\r\n";
        echo "<pg_error_description>" . htmlentities($arrResponse['pg_error_description']). "</pg_error_description>\r\n";
        echo "<pg_sig>" . $arrResponse['pg_sig'] . "</pg_sig>\r\n";
        echo "</response>\r\n";

        if($arrResponse['pg_status'] == 'ok') {
            if($order_info['order_status_id'] == 0) {
                $this->model_checkout_order->confirm($order_id, $this->config->get('paybox_order_status_id'), 'PayBox');
                return;
            }

            if($order_info['order_status_id'] != $this->config->get('paybox_order_status_id'))
                $this->model_checkout_order->update($order_id, $this->config->get('paybox_order_status_id'), 'PayBox', TRUE);

        }

    }
}
?>
