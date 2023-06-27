<?php
class ModelExtensionPaymentPayselection extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/payselection');

        $method_data = array(
            'code'     => 'payselection',
            'title'    => $this->language->get('payselection_text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('payment_payselection_sort_order')
        );

        return $method_data;
    }
}