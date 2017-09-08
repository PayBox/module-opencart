<?php
class ModelPaymentPlatron extends Model {
	public function getMethod($address) {
		$this->load->language('payment/platron');

		if ($this->config->get('platron_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('platron_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if (!$this->config->get('platron_geo_zone_id')) {
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
				'code'         => 'platron',
				'title'      => !$this->config->get('platron_payment_name') ? $this->language->get('text_title') : $this->config->get('platron_payment_name'),
				'sort_order' => $this->config->get('platron_sort_order')
			);
		}

		return $method_data;
	}

    public function make($scriptName, $arrReq, $secret_word) {

        ksort($arrReq);

        array_unshift($arrReq, $scriptName);
        array_push($arrReq, $secret_word);

        $sig = implode(';', $arrReq);

        return md5($sig);

    }

    public function checkSig($signature, $scriptName, $arrReq, $secret_word) {

        return (string)$signature === $this->make($scriptName, $arrReq, $secret_word);

    }

}
?>