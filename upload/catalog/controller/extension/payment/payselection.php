<?php

class ControllerExtensionPaymentPayselection extends Controller
{



    public $urlWebForm = 'https://webform.payselection.com';
    public $urlAPI = 'https://gw.payselection.com';
    public $urlWidget = 'https://widget.payselection.com/lib/pay-widget.js';


    public function isSSL()
    {
        if ((isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) || $_SERVER['SERVER_PORT'] == 443) {
            return true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            return true;
        } else {
            return false;
        }
    }

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/payment/payselection');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/payselection.twig')) {
            $this->have_template = true;
        }
    }

    public function index()
    {
        $data['action'] = $this->url->link('extension/payment/payselection/payment', '', true);
        $data['payselection_button_confirm'] = $this->language->get('payselection_button_confirm');

        return $this->load->view('extension/payment/payselection', $data);
    }

    public function payment()
    {

        $this->load->model('checkout/order');
        $this->load->model('account/order');

        $methodForPay = $this->config->get('payment_payselection_mode');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_totals = $this->model_checkout_order->getOrderTotals($this->session->data['order_id']);

        // print_r($order_info);
        // print_r($order_totals);
        // exit();

        $shipping_cost = '';
        $shipping_name = '';

        $coupounvalue = 0;
        
        foreach($order_totals as $item){
            if($item['code']=='shipping'){
                $shipping_cost =    (float)  sprintf("%.2f", round($item['value'], 2));
                $shipping_name =    $item['title'];
            }
            if($item['code']=='coupon'){
                $coupounvalue =    (float) $item['value'];
            }
        }

       


      
        
        $data['hasFiskalization'] = $this->config->get('payment_payselection_fiscalization');
        $data['payType'] = $this->config->get('payment_payselection_stage') == 'one' ? 'Pay' : 'Block';
        $data['serviceId']  = $this->config->get('payment_payselection_serviceId');
        $data['key'] = $this->config->get('payment_payselection_openKey');
        $data['Currency'] = $this->config->get('payment_payselection_currency') == 0 ? 'RUB' : 'RUB';
        $data['OrderId'] = (int)$order_info['order_id'];
        $data['Comment'] = $order_info['comment'] == "" ? "Оплата заказа " . $data['OrderId'] : $order_info['comment'];
        $data['Amount'] =  sprintf("%.2f", round($order_info['total'], 2));
        $data['returnUrl'] = $this->url->link('extension/payment/payselection/callback&orderid=' . $data['OrderId']);

        $data['clientName'] = $order_info['payment_firstname'];
        $data['clientEmail'] = isset($order_info['email']) && $order_info['email'] != "" ? $order_info['email'] : '';
        $data['clientPhone'] = isset($order_info['telephone']) && $order_info['telephone'] != "" ? $order_info['telephone'] : '';
        $data['companyEmail'] = $this->config->get('payment_payselection_email');
        $data['companyInn'] = $this->config->get('payment_payselection_inn');
        $data['companyPaymentAdres'] = $this->config->get('payment_payselection_area');

        switch ($this->config->get('payment_payselection_taxSystem')) {
            case 0:
                $data['companySNO'] = 'osn';
                break;
            case 1:
                $data['companySNO'] = 'usn_income';
                break;
            case 2:
                $data['companySNO'] = 'usn_income_outcome';
                break;
            case 3:
                $data['companySNO'] = 'envd';
                break;
            case 4:
                $data['companySNO'] = 'esn';
                break;
            case 5:
                $data['companySNO'] = 'patent';
                break;
            default:
                $data['companySNO'] = 'osn';
                break;
        }

        switch ($this->config->get('payment_payselection_taxType')) {
            case 0:
                $data['companyVAT'] = 'none';
                break;
            case 1:
                $data['companyVAT'] = 'vat0';
                break;
            case 2:
                $data['companyVAT'] = 'vat10';
                break;
            case 4:
                $data['companyVAT'] = 'vat110';
                break;
            case 6:
                $data['companyVAT'] = 'vat20';
                break;
            case 7:
                $data['companyVAT'] = 'vat120';
                break;
            default:
                $data['companyVAT'] = 'osn';
                break;
        }




        $urlCallback = $this->isSSL() ? HTTPS_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid=' . $data['OrderId'] : HTTP_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid=' . $data['OrderId'];
        $urlCallbackSuccess = $this->isSSL() ? HTTPS_SERVER . 'index.php?route=checkout/success' : HTTP_SERVER . 'index.php?route=checkout/success';
        $urlCallbackFail = $this->isSSL() ? HTTPS_SERVER . 'index.php?route=checkout/failure' : HTTP_SERVER . 'index.php?route=checkout/failure';
        $urlCallbackCancel = $this->isSSL() ? HTTPS_SERVER . 'index.php?route=extension/payment/payselection/payment' : HTTP_SERVER . 'index.php?route=extension/payment/payselection/payment';
        $data['ExtraData'] = json_encode(array(
            'WebhookUrl' => $urlCallback,
            'SuccessUrl' => $urlCallbackSuccess,
            'DeclineUrl' => $urlCallbackFail,
            'FailUrl' => $urlCallbackFail,
            'CancelUrl' => $urlCallbackCancel,
        ));

        if ($methodForPay == "widget") {

            if ((int) $data['hasFiskalization']) {


                $order_products = $this->model_account_order->getOrderProducts($data['OrderId']);


                $items = [];
                
                $allitem = 0;
                foreach ($order_products as $product) {

                    $allitem+=(float) $product['quantity'];
                }

                $couponmin =  $coupounvalue/$allitem;


                foreach ($order_products as $product) {
                    $items[] = [
                        "name" => $product['name'],
                        "price" => (float)  round($product['price'] + (float)$couponmin , 2),
                        "quantity" => (float) $product['quantity'],
                        "sum" =>(float)   round($product['total'] + (float) $couponmin*(float) $product['quantity'] , 2) ,
                        "payment_method" => "full_payment",
                        "payment_object" => "commodity",
                        "vat" => [
                            "type" => $data['companyVAT'],
                    
                        ]
                    ];
                }

                if($shipping_cost!=''){
                    $items[] = [
                        "name" => $shipping_name,
                        "price" => $shipping_cost,
                        "quantity" => 1,
                        "sum" =>$shipping_cost,
                        "payment_method" => "full_payment",
                        "payment_object" => "service",
                        "vat" => [
                            "type" => 'none'
                        ]
                    ]; 
                }

                $userData["ReceiptData"] = [
                    "timestamp" => date('d.m.Y H:i:s'),
                    "external_id" => $data['OrderId'] . "",
                    "receipt" => [
                        "client" => [
                            "name" =>  $data['clientName'],
                            "email" => $data['clientEmail'],
                            "phone" => $data['clientPhone']
                        ],
                        "company" => [
                            "email" =>  $data['companyEmail'],
                            "sno" => $data['companySNO'],
                            "inn" => $data['companyInn'],
                            "payment_address" => $data['companyPaymentAdres']
                        ],
                        "items" => $items,
                        "payments" => [
                            [
                                "type" => 1,
                                "sum" => (float)  sprintf("%.2f", round($data['Amount'], 2)),
                            ]

                        ],
                        "total" => (float)  sprintf("%.2f", round($data['Amount'], 2)),

                    ]
                ];
                $data['ReceiptData'] =  json_encode($userData["ReceiptData"]);
            }

            $this->document->setTitle($this->language->get('payselection_text_title'));
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            return $this->response->setOutput($this->load->view('extension/payment/payselectionview', $data));
        } else {
            $secretKey = $data['key'];

            $userData = [
                "MetaData" => [
                    "PaymentType" => strval($data['payType']),
                    "TypeLink" => "Reusable",
                    "PreviewForm" => true
                ],
                "PaymentRequest" => [
                    "OrderId" => $data['OrderId'] . "",
                    "Amount" => $data['Amount'],
                    "Currency" => $data['Currency'],
                    "Description" => $data['Comment'],
                    "RebillFlag" => false,
                    "ExtraData" => [
                        'WebhookUrl' => $urlCallback,
                        'SuccessUrl' => $urlCallbackSuccess,
                        'DeclineUrl' => $urlCallbackFail,
                        'FailUrl' => $urlCallbackFail,
                        'CancelUrl' => $urlCallbackCancel,
                    ]
                ],
            ];

            if ((int) $data['hasFiskalization']) {


                $order_products = $this->model_account_order->getOrderProducts($data['OrderId']);

                $items = [];

                $allitem = 0;
                foreach ($order_products as $product) {

                    $allitem+=(float) $product['quantity'];
                }

                $couponmin =  $coupounvalue/$allitem;


                foreach ($order_products as $product) {
                    $items[] = [
                        "name" => $product['name'],
                        "price" => (float)  round($product['price'] + (float)$couponmin , 2),
                        "quantity" => (float) $product['quantity'],
                        "sum" =>(float)   round($product['total'] + (float) $couponmin*(float) $product['quantity'] , 2) ,
                        "payment_method" => "full_payment",
                        "payment_object" => "commodity",
                        "vat" => [
                            "type" => $data['companyVAT'],
                           
                        ]
                    ];
                
                }

                if($shipping_cost!=''){
                    $items[] = [
                        "name" => $shipping_name,
                        "price" => $shipping_cost,
                        "quantity" => 1,
                        "sum" =>$shipping_cost,
                        "payment_method" => "full_payment",
                        "payment_object" => "service",
                        "vat" => [
                            "type" => 'none'
                        ]
                    ]; 
                }


                $userData["ReceiptData"] = [
                    "timestamp" => date('d.m.Y H:i:s'),
                    "external_id" => $data['OrderId'] . "",
                    "receipt" => [
                        "client" => [
                            "name" =>  $data['clientName'],
                            "email" => $data['clientEmail'],
                            "phone" => $data['clientPhone']
                        ],
                        "company" => [
                            "email" =>  $data['companyEmail'],
                            "sno" => $data['companySNO'],
                            "inn" => $data['companyInn'],
                            "payment_address" => $data['companyPaymentAdres']
                        ],
                        "items" => $items,
                        "payments" => [
                            [
                                "type" => 1,
                                "sum" =>(float)  sprintf("%.2f", round($data['Amount'], 2)),
                            ]

                        ],
                        "total" =>(float)  sprintf("%.2f", round($data['Amount'], 2)),

                    ]
                ];
            }



            $jsonData = json_encode($userData);


            $signature = $this->getSignature($jsonData, $secretKey);


            $url = 'https://webform.payselection.com/webpayments/create';


            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'X-SITE-ID: ' . $data['serviceId'],
                    'X-REQUEST-ID: ' . $data['OrderId'],
                    'X-REQUEST-SIGNATURE: ' . $signature,
                    'Content-Length: ' . strlen($jsonData)
                )
            );


            $response = curl_exec($ch);


            curl_close($ch);

            if ($response === false) {
            }

            if (strripos($response, "widget.payselection.com") !== false) {

                header('Location: ' . str_replace('"', '', $response));
                exit;
            } else {
                echo 'ошибка оплаты заказа, вернитесь назад и проверьте данные';
            }
        }
    }

    public function callback()
    {

        $stream = file_get_contents('php://input');
        $objectInput  = json_decode($stream);
        $requestUrl = $this->isSSL() ? HTTPS_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid=' . $objectInput->OrderId : HTTP_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid=' . $objectInput->OrderId;
        $verificationSTR = "POST\n" .
            $requestUrl . "\n" .
            $this->config->get('payment_payselection_serviceId') . "\n" .
            $stream;
        $signMy = $this->getSignature($verificationSTR, $this->config->get('payment_payselection_key'));
        $signOut = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
        // $this->log->write('hook start');
        // $this->log->write($objectInput);
        // $this->log->write($verificationSTR);
        // $this->log->write($objectInput->OrderId);
        // $this->log->write($signMy);
        // $this->log->write($signOut);
        // $this->log->write('hook end');



        if (isset($this->request->get['orderid']) && isset($objectInput->OrderId) && (int)$this->request->get['orderid'] == (int)$objectInput->OrderId) {
            $order_id = $this->request->get['orderid'];
        } else {
            die('Illegal Access');
        }

        $this->load->model('checkout/order');
        if ($signMy == $signOut && ($objectInput->Event == 'Payment' || $objectInput->Event == 'Block' || $objectInput->Event == 'Fail' || $objectInput->Event == 'Cancel' || $objectInput->Event == 'Refund')) {
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                if ($objectInput->Event == 'Payment') {
                    if((float) $order_info['total'] > (float)$objectInput->Amount) { 
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status_id'),"Сумма подтвержденной транзакции меньше чем сумма заказа. Оплачено".$objectInput->Amount);
                    }else{
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status_id'));
                    }
                } elseif ($objectInput->Event == 'Block') {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status2_id'));
                } elseif ($objectInput->Event == 'Cancel') {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status3_id'));
                } elseif ($objectInput->Event == 'Fail') {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status4_id'));
                } elseif ($objectInput->Event == 'Refund') {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status5_id'));
                }
            }
        }
    }
    function getSignature($body, $secretKey)
    {
        $hash = hash_hmac('sha256', $body, $secretKey, false);
        return $hash;
    }
}
