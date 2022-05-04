<?php
class ModelExtensionPaymentPaybox extends Model {
    public function getMethod($address) {
        $this->load->language('extension/payment/paybox');

        if ($this->config->get('payment_paybox_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_paybox_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('payment_paybox_geo_zone_id')) {
                $status = TRUE;
            } elseif ($query->num_rows) {
                $status = TRUE;
            } else {
                $status = FALSE;
            }
        } else {
            $status = FALSE;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'paybox',
                'title'      => !$this->config->get('payment_paybox_payment_name') ? $this->language->get('text_title') : $this->config->get('payment_paybox_payment_name'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_paybox_sort_order')
            );
        }

        return $method_data;
    }

    public function make($scriptName, $arrReq, $secret_word) {
        $sign_data = $this->prepare_send($arrReq);
        ksort($sign_data);

        array_unshift($sign_data, $scriptName);
        array_push($sign_data, $secret_word);
        $sig = implode(';', $sign_data);

        return md5($sig);

    }

    public function checkSig($signature, $scriptName, $arrReq, $secret_word) {

        return (string)$signature === $this->make($scriptName, $arrReq, $secret_word);

    }

    public function prepare_send($data, $parent_name = '') {
        if (!is_array($data)) return $data;

        $arrFlatParams = [];
        $i = 0;
        foreach ($data as $key => $val) {
            $i++;
            $name = $parent_name . ((string) $key) . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = array_merge($arrFlatParams, $this->prepare_send($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }

        return $arrFlatParams;
    }

    public function getPositionsProductToOfd($productPrice, $type, $data, $count = null) {
        switch ($type) {
            case 'coupon':
                if ('P' === $data['type']) {
                    return (string)($productPrice - $productPrice/100*(int)$data['discount']);
                } else {
                    return (string)($productPrice - $data['discount']/$count);
                }
            case 'voucher':
                return (string)($productPrice - $data['amount']/$count);
            default:
                return null;
        }
    }

    public function stringFieldFormatting($str)
    {
        $str = substr(trim($str), 0, 1024); // API PayBox поле pg_description принимает не более 1024 символов
        $pattern = [
            '/[^',                             // Начало регулярного выражения
            'A-zА-я0-9\,\-\!\*\#\'\"\s\+\;',   // Базовый набор допустимых символов
            'ёЁЇїІіЄєҐґ',                      // Символы алфавита Украины
            'ӘәҒғҚқҢңӨөҰұҮүҺһІі',              // Символы алфавита Казахстана
            'ҢңӨөҮү',                          // Символы алфавита Кыргызстана
            ']/u'                              // Конец регулярного выражения
        ];

        return preg_replace(implode('', $pattern), '', $str); // Удаляем все символы, что не соответствуют шаблону
    }
}