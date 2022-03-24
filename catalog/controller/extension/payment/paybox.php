<?php
class ControllerExtensionPaymentPaybox extends Controller {

    public function index() {

        $this->language->load('extension/payment/paybox');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->load->model('account/order');
        $order_products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
        $order_products['total'] = 0;


        $strOrderDescription = "";
        foreach($order_products as $product) {
            $strOrderDescription .= @$product["name"]."*".@$product["quantity"]."; ";
            $order_products['total'] += $product['total'];
        }

        if ($this->config->get('payment_paybox_ofd_shipping')) {
            $order_products['total'] += floatval($this->session->data['shipping_method']['cost']);
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');


        // Настройки

        $merchant_id = $this->config->get('payment_paybox_merchant_id');
        $secret_word = $this->config->get('payment_paybox_secret_word');
        $lifetime = $this->config->get('payment_paybox_lifetime');

        $msg_description = $this->language->get('msg_description');

        $this->load->model('extension/payment/paybox');

        $strCurrency = $order_info['currency_code'];
        if($strCurrency == "RUR") {
            $strCurrency = "RUB";
        }

        $arrReq = array(
            'pg_amount'         => (int)$order_products['total'],
            'pg_check_url'      => HTTPS_SERVER . 'index.php?route=extension/payment/paybox/check',
            'pg_description'    => substr($strOrderDescription, 0, -5),
            'pg_encoding'       => 'UTF-8',
            'pg_currency'       => $strCurrency,
            'pg_user_ip'        => $_SERVER['REMOTE_ADDR'],
            'pg_lifetime'       => !empty($lifetime) ? $lifetime * 3600 : 86400,
            'pg_merchant_id'    => $merchant_id,
            'pg_order_id'       => $order_info['order_id'],
            'pg_result_url'     => HTTPS_SERVER . 'index.php?route=extension/payment/paybox/callback',
            'pg_request_method' => 'GET',
            'pg_salt'           => rand(21, 43433),
            'pg_success_url'    => HTTPS_SERVER . 'index.php?route=checkout/success',
            'pg_failure_url'    => HTTPS_SERVER . 'index.php?route=checkout/failure',
            'pg_user_phone'     => $order_info['telephone'],
            'pg_user_contact_email' => $order_info['email']
        );

        if($this->config->get('payment_paybox_test') == 1) {
            $arrReq['pg_testing_mode'] = 1;
        }

        if ($this->config->get('payment_paybox_ofd') == 1) {
            foreach ($this->model_account_order->getOrderProducts($this->session->data['order_id']) as $key => $value) {
                $arrReq['pg_receipt_positions'][] = [
                    'count' => $value['quantity'],
                    'name' => $value['name'],
                    'price' => $value['price'],
                    'tax_type' => $this->config->get('payment_paybox_ofd_tax_type')
                ];
            }

            if ($this->config->get('payment_paybox_ofd_shipping')) {
                $arrReq['pg_receipt_positions'][] = [
                    'count' => 1,
                    'name' => $this->session->data['shipping_method']['title'],
                    'price' => $this->session->data['shipping_method']['cost'],
                    'tax_type' => $this->config->get('payment_paybox_ofd_tax_type')
                ];
            }
        }

        $arrReq['pg_sig'] = $this->model_extension_payment_paybox->make('payment.php', $arrReq, $secret_word);
        $query = http_build_query($arrReq);

        $data['action'] = 'https://api.paybox.money/payment.php?' . $query;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/paybox')) {
            return $this->load->view($this->config->get('config_template') . '/extension/payment/paybox', $data);
        } else {
            return $this->load->view('extension/payment/paybox', $data);
        }
    }

    public function check() {

        $this->language->load('extension/payment/paybox');
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/paybox');

        $arrResponse = array();

        if(!empty($this->request->post))
            $data = $this->request->post;
        else
            $data = $this->request->get;

        $pg_sig = !empty($data['pg_sig'])?$data['pg_sig']:'';
        unset($data['pg_sig']);

        $secret_word = $this->config->get('payment_paybox_secret_word');

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

        $arrResponse['pg_sig'] = $this->model_extension_payment_paybox->make('index.php', $arrResponse, $secret_word);

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
        $this->language->load('extension/payment/paybox');
        $this->load->model('extension/payment/paybox');
        $this->load->model('checkout/order');

        $arrResponse = array();

        if(!empty($this->request->post))
            $data = $this->request->post;
        else
            $data = $this->request->get;

        $pg_sig = $data['pg_sig'];
        unset($data['pg_sig']);

        $secret_word = $this->config->get('payment_paybox_secret_word');

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

        $arrResponse['pg_sig'] = $this->model_extension_payment_paybox->make('index.php', $arrResponse, $secret_word);

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
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_paybox_order_status_id'), 'Paybox');
                return;
            }
            if($order_info['order_status_id'] != $this->config->get('payment_paybox_order_status_id')) {
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_paybox_order_status_id'), 'Paybox', TRUE);
            }

        }

    }


    private function getVersion() {
        $version = explode('.', VERSION);
        return array(
            'alpha' => $version[0],
            'beta' => $version[1],
            'rc' => $version[2],
            'public' => $version[3]
        );
    }
}
?>
