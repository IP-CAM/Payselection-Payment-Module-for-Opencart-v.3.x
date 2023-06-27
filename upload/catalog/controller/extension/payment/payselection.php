<?php

class ControllerExtensionPaymentPayselection extends Controller
{

    

    public $urlWebForm = 'https://webform.payselection.com';
    public $urlAPI = 'https://gw.payselection.com';
    public $urlWidget = 'https://widget.payselection.com/lib/pay-widget.js';


    public function isSSL(){
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
        // Check if SSL

    }

    public function index()
    {
        $data['action'] = $this->url->link('extension/payment/payselection/payment','',true);
        $data['payselection_button_confirm'] = $this->language->get('payselection_button_confirm');
        // условия типа оплаты - виджет или ссылка, или через API ?!
       return $this->load->view('extension/payment/payselection', $data);
    }

    public function payment()
    {
       
        $this->load->model('checkout/order');
        $data['serviceId']  = $this->config->get('payment_payselection_serviceId');
        $data['key'] = $this->config->get('payment_payselection_openKey');
        $data['Currency'] = $this->config->get('payment_payselection_currency')==0?'RUB':'RUB';
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['OrderId'] = (int)$order_info['order_id'];
        $data['Comment'] = $order_info['comment']==""?"Оплата заказа ".$data['OrderId']:$order_info['comment'];
        $data['Amount'] = round($order_info['total'], 2);
        $data['returnUrl'] = $this->url->link('extension/payment/payselection/callback&orderid='.$data['OrderId']);
        $this->document->setTitle($this->language->get('payselection_text_title'));
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $urlCallback = $this->isSSL()?HTTPS_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid='.$data['OrderId']:HTTP_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid='.$data['OrderId'];
        $urlCallbackSuccess = $this->isSSL()?HTTPS_SERVER . 'index.php?route=checkout/success':HTTP_SERVER . 'index.php?route=checkout/success';
        $urlCallbackFail = $this->isSSL()?HTTPS_SERVER . 'index.php?route=checkout/failure':HTTP_SERVER . 'index.php?route=checkout/failure';
        $urlCallbackCancel = $this->isSSL()?HTTPS_SERVER . 'index.php?route=extension/payment/payselection/payment':HTTP_SERVER . 'index.php?route=extension/payment/payselection/payment';
        $data['ExtraData']=json_encode(array(
            'WebhookUrl' => $urlCallback,
            'SuccessUrl' => $urlCallbackSuccess,
            'DeclineUrl' => $urlCallbackFail,
            'FailUrl' => $urlCallbackFail,
            'CancelUrl' => $urlCallbackCancel,
        ));

         return $this->response->setOutput($this->load->view('extension/payment/payselectionview', $data));

    }

    public function callback()
    {   
       
        $stream = file_get_contents('php://input');
        $objectInput  = json_decode($stream);
        $requestUrl = $this->isSSL()?HTTPS_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid='.$objectInput->OrderId:HTTP_SERVER . 'index.php?route=extension/payment/payselection/callback&orderid='.$objectInput->OrderId;
        $verificationSTR = "POST\n".
        $requestUrl."\n".
        $this->config->get('payment_payselection_serviceId')."\n".
        $stream;
        $signMy = $this->getSignature($verificationSTR,$this->config->get('payment_payselection_key'));
        $signOut = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
         $this->log->write('hook start');
         $this->log->write($verificationSTR);
         $this->log->write($objectInput->OrderId);
         $this->log->write($signMy);
         $this->log->write($signOut);
         $this->log->write('hook end');

        if (isset($this->request->get['orderid']) && isset($objectInput->OrderId) && (int)$this->request->get['orderid']==(int)$objectInput->OrderId) {
            $order_id = $this->request->get['orderid'];
        } else {
            die('Illegal Access');
        }

        $this->load->model('checkout/order');
        if($signMy==$signOut && $objectInput->Event=='Payment'){
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
                 $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payselection_order_status_id'));
        }
    }
    }
    function getSignature($body, $secretKey)
    {
        $hash = hash_hmac('sha256', $body, $secretKey, false);
        return $hash;
    }
}