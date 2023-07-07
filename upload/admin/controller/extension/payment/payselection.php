<?php

class ControllerExtensionPaymentPayselection extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/payselection');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_payselection', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'));
        }


        $data['heading_title'] = $this->language->get('heading_title');


        $data['breadcrumbs'] = array();
        array_push(
            $data['breadcrumbs'],
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(
                'text' => $this->language->get('text_payment'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/payselection', 'user_token=' . $this->session->data['user_token'], 'SSL')
            )
        );


        $data['action'] = $this->url->link('extension/payment/payselection', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');


        $data['text_settings'] = $this->language->get('text_settings');


        $data['entry_status'] = $this->language->get('status');
        $data['status_enabled'] = $this->language->get('status_enabled');
        $data['status_disabled'] = $this->language->get('status_disabled');

        if (isset($this->request->post['payment_payselection_status'])) {
            $data['payment_payselection_status'] = $this->request->post['payment_payselection_status'];
        } else {
            $data['payment_payselection_status'] = $this->config->get('payment_payselection_status');
        }

        $data['entry_serviceId'] = $this->language->get('serviceId');
        $data['payment_payselection_serviceId'] = $this->config->get('payment_payselection_serviceId');
        $data['entry_openKey'] = $this->language->get('openKey');
        $data['payment_payselection_openKey'] = $this->config->get('payment_payselection_openKey');
        $data['entry_key'] = $this->language->get('key');
        $data['payment_payselection_key'] = $this->config->get('payment_payselection_key');

        $data['entry_mode'] = $this->language->get('mode');
        $data['mode_test'] = $this->language->get('mode_test');
        $data['mode_prod'] = $this->language->get('mode_prod');
        $data['payment_payselection_mode'] = $this->config->get('payment_payselection_mode');

        $data['entry_stage'] = $this->language->get('stage');
        $data['stage_one'] = $this->language->get('stage_one');
        $data['stage_two'] = $this->language->get('stage_two');
        $data['payment_payselection_stage'] = $this->config->get('payment_payselection_stage');
        $data['payment_payselection_fiscalization'] = $this->config->get('payment_payselection_fiscalization');

        $data['entry_order_status'] = $this->language->get('entry_order_status');

        if (isset($this->request->post['payment_payselection_order_status_id'])) {
            $data['payment_payselection_order_status_id'] = $this->request->post['payment_payselection_order_status_id'];
        } else {
            $data['payment_payselection_order_status_id'] = $this->config->get('payment_payselection_order_status_id');
        }
        if (isset($this->request->post['payment_payselection_order_status2_id'])) {
            $data['payment_payselection_order_status2_id'] = $this->request->post['payment_payselection_order_status2_id'];
        } else {
            $data['payment_payselection_order_status2_id'] = $this->config->get('payment_payselection_order_status2_id');
        }

        if (isset($this->request->post['payment_payselection_order_status3_id'])) {
            $data['payment_payselection_order_status3_id'] = $this->request->post['payment_payselection_order_status3_id'];
        } else {
            $data['payment_payselection_order_status3_id'] = $this->config->get('payment_payselection_order_status3_id');
        }


        if (isset($this->request->post['payment_payselection_order_status4_id'])) {
            $data['payment_payselection_order_status4_id'] = $this->request->post['payment_payselection_order_status4_id'];
        } else {
            $data['payment_payselection_order_status4_id'] = $this->config->get('payment_payselection_order_status4_id');
        }

        
        if (isset($this->request->post['payment_payselection_order_status5_id'])) {
            $data['payment_payselection_order_status5_id'] = $this->request->post['payment_payselection_order_status5_id'];
        } else {
            $data['payment_payselection_order_status5_id'] = $this->config->get('payment_payselection_order_status5_id');
        }

        
        $data['entry_order_status2'] = $this->language->get('entry_order_status2');
        $data['entry_order_status3'] = $this->language->get('entry_order_status3');
        $data['entry_order_status4'] = $this->language->get('entry_order_status4');
        $data['entry_order_status5'] = $this->language->get('entry_order_status5');
        $data['entry_order_fiscalization'] = $this->language->get('entry_order_fiscalization');
        $data['entry_order_fiscalization_on'] = $this->language->get('entry_order_fiscalization_on');
        $data['entry_order_fiscalization_off'] = $this->language->get('entry_order_fiscalization_off');
        $data['entry_order_fiscalization_coment'] = $this->language->get('entry_order_fiscalization_coment');
        $data['entry_order_email'] = $this->language->get('entry_order_email');
        $data['entry_order_inn'] = $this->language->get('entry_order_inn');
        $data['entry_order_area'] = $this->language->get('entry_order_area');

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['order_statuses2'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['order_statuses3'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['order_statuses4'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['order_statuses5'] = $this->model_localisation_order_status->getOrderStatuses();


        $data['entry_sortOrder'] = $this->language->get('entry_sortOrder');
        $data['payment_payselection_sort_order'] = $this->config->get('payment_payselection_sort_order');


        $data['entry_logging'] = $this->language->get('logging');
        $data['logging_enabled'] = $this->language->get('logging_enabled');
        $data['logging_disabled'] = $this->language->get('logging_disabled');
        $data['payment_payselection_logging'] = $this->config->get('payment_payselection_logging');

        $data['entry_currency'] = $this->language->get('entry_currency');
        $data['currency_list'] = array_merge(
            array(
                array(
                    'numeric' => 0,
                    'alphabetic' => $this->language->get('entry_currency_default')
                )
            ),
            $this->getCurrencyList()
        );
        $data['payment_payselection_currency'] = $this->config->get('payment_payselection_currency');

        $data['entry_ofdStatus'] = $this->language->get('entry_ofdStatus');
        $data['payment_payselection_ofd_status'] = $this->config->get('payment_payselection_ofd_status');
        $data['entry_ofd_enabled'] = $this->language->get('entry_ofd_enabled');
        $data['entry_ofd_disabled'] = $this->language->get('entry_ofd_disabled');

        $data['entry_taxSystem'] = $this->language->get('entry_taxSystem');
        $data['taxSystem_list'] = $this->getTaxSystemList();
        $data['payment_payselection_taxSystem'] = $this->config->get('payment_payselection_taxSystem');

        $data['entry_taxType'] = $this->language->get('entry_taxType');
        $data['taxType_list'] = $this->getTaxTypeList();
        $data['payment_payselection_taxType'] = $this->config->get('payment_payselection_taxType');
        $data['payment_payselection_email'] = $this->config->get('payment_payselection_email');
        $data['payment_payselection_inn'] = $this->config->get('payment_payselection_inn');
        $data['payment_payselection_area'] = $this->config->get('payment_payselection_area');



        $data['entry_paymentMethod'] = $this->language->get('entry_paymentMethod');
        $data['ffd_paymentMethodTypeList'] = $this->getPaymentMethodTypeList();
        $data['payment_payselection_paymentMethodType'] = $this->config->get('payment_payselection_paymentMethodType');


        $data['entry_paymentMethodDelivery'] = $this->language->get('entry_paymentMethodDelivery');
        $data['payment_payselection_paymentMethodTypeDelivery'] = $this->config->get('payment_payselection_paymentMethodTypeDelivery');


        $data['entry_paymentObject'] = $this->language->get('entry_paymentObject');
        $data['ffd_paymentObjectTypeList'] = $this->getPaymentObjectTypeList();
        $data['payment_payselection_paymentObjectType'] = $this->config->get('payment_payselection_paymentObjectType');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('extension/payment/payselection', $data));
    }


    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/payselection')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }





    private function getCurrencyList()
    {
        return [
            [
                'numeric' => 643,
                'alphabetic' => 'RUR'
            ],
            [
                'numeric' => 810,
                'alphabetic' => 'RUB'
            ],
            [
                'numeric' => 840,
                'alphabetic' => 'USD'
            ],
            [
                'numeric' => 933,
                'alphabetic' => 'BYN'
            ],
            [
                'numeric' => 978,
                'alphabetic' => 'EUR'
            ],
        ];
    }



    private function getTaxTypeList()
    {

        return [
            [
                'numeric' => 0,
                'alphabetic' => $this->language->get('entry_no_vat')
            ],
            [
                'numeric' => 1,
                'alphabetic' => $this->language->get('entry_vat0')
            ],
            [
                'numeric' => 2,
                'alphabetic' => $this->language->get('entry_vat10')
            ],
            [
                'numeric' => 4,
                'alphabetic' => $this->language->get('entry_vat10_110')
            ],

            [
                'numeric' => 6,
                'alphabetic' => $this->language->get('entry_vat20')
            ],
            [
                'numeric' => 7,
                'alphabetic' => $this->language->get('entry_vat20_120')
            ],
        ];
    }



    private function getTaxSystemList()
    {
        return [
            [
                'numeric' => 0,
                'alphabetic' => $this->language->get('entry_tax_system_1')
            ],
            [
                'numeric' => 1,
                'alphabetic' => $this->language->get('entry_tax_system_2')
            ],
            [
                'numeric' => 2,
                'alphabetic' => $this->language->get('entry_tax_system_3')
            ],
            [
                'numeric' => 3,
                'alphabetic' => $this->language->get('entry_tax_system_4')
            ],
            [
                'numeric' => 4,
                'alphabetic' => $this->language->get('entry_tax_system_5')
            ],
            [
                'numeric' => 5,
                'alphabetic' => $this->language->get('entry_tax_system_6')
            ],
        ];
    }


    private function getFFDVersionlist()
    {
        return [
            [
                'value' => 'v10',
                'title' => '1.00'
            ],
            [
                'value' => 'v105',
                'title' => '1.05'
            ],

        ];
    }


    private function getPaymentMethodTypeList()
    {
        return [
            [
                'numeric' => 1,
                'alphabetic' => $this->language->get('entry_payment_method_1')
            ],
            [
                'numeric' => 2,
                'alphabetic' => $this->language->get('entry_payment_method_2')
            ],
            [
                'numeric' => 3,
                'alphabetic' => $this->language->get('entry_payment_method_3')
            ],
            [
                'numeric' => 4,
                'alphabetic' => $this->language->get('entry_payment_method_4')
            ],
            [
                'numeric' => 5,
                'alphabetic' => $this->language->get('entry_payment_method_5')
            ],
            [
                'numeric' => 6,
                'alphabetic' => $this->language->get('entry_payment_method_6')
            ],
            [
                'numeric' => 7,
                'alphabetic' => $this->language->get('entry_payment_method_7')
            ],

        ];
    }


    private function getPaymentObjectTypeList()
    {
        return [
            [
                'numeric' => 1,
                'alphabetic' => $this->language->get('entry_payment_object_1')
            ],
            [
                'numeric' => 2,
                'alphabetic' => $this->language->get('entry_payment_object_2')
            ],
            [
                'numeric' => 3,
                'alphabetic' => $this->language->get('entry_payment_object_3')
            ],
            [
                'numeric' => 4,
                'alphabetic' => $this->language->get('entry_payment_object_4')
            ],
            [
                'numeric' => 5,
                'alphabetic' => $this->language->get('entry_payment_object_5')
            ],

            [
                'numeric' => 7,
                'alphabetic' => $this->language->get('entry_payment_object_7')
            ],

            [
                'numeric' => 9,
                'alphabetic' => $this->language->get('entry_payment_object_9')
            ],
            [
                'numeric' => 10,
                'alphabetic' => $this->language->get('entry_payment_object_10')
            ],

            [
                'numeric' => 12,
                'alphabetic' => $this->language->get('entry_payment_object_12')
            ],
            [
                'numeric' => 13,
                'alphabetic' => $this->language->get('entry_payment_object_13')
            ],
        ];
    }

    private function library($library)
    {
        $file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }
}
