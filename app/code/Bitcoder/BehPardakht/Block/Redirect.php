<?php

namespace Bitcoder\BehPardakht\Block;

use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

/**
 * Class Redirect
 * @package Bitcoder\BehPardakht\Block
 *  =>t id
 * =>username
 * =>password
 */
class Redirect extends \Magento\Framework\View\Element\Template
{

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $messageManager;
    protected $redirectFactory;
    protected $catalogSession;
    protected $customer_session;

    /**
     * @var $order Order
     */
    protected $order;
    protected $response;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Session $customer_session,
        RedirectFactory $redirectFactory,
        \Magento\Framework\App\Response\Http $response,

        Template\Context $context,
        array $data
    )
    {
        $this->customer_session = $customer_session;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->response = $response;

        parent::__construct($context, $data);

    }

    public function sendToBank()
    {

        if (!$this->getOrderId()) {
            $this->response->setRedirect($this->_urlBuilder->getUrl(''));

            return "";
        }
        $response['state'] = true;
        $response['msg'] = "";
        $InvoiceID = time() . '10000' . (string)$this->getOrderId();
        $localDate = date('Ymd');
        $localTime = date('Gis');
        $parameters = array(
            'terminalId' => $this->getTerminalID(),
            'userName' => $this->getUserName(),
            'userPassword' => $this->getUserPassword(),
            'orderId' => $InvoiceID,
            'amount' => $this->getOrderPrice(),
            'localDate' => $localDate,
            'localTime' => $localTime,
            'additionalData' => '',
            'callBackUrl' => $this->getCallBackUrl(),
            'payerId' => 0);
        $client = new \SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        $namespace = 'http://interfaces.core.sw.bps.com/';
        $result = $client->bpPayRequest($parameters, $namespace);
        $res = explode(',', $result->return);
        if ($res[0] == "0") {
            $response['state'] = true;
            $response['msg'] = $res[1];
        } else {
            $response['msg'] = 'امکان اتصال وجود ندارد ، لطفاً دوباره تلاش کنید.';
            $response['state'] = false;
        }
        return $response;

    }

    public function getOrderId()
    {

        return isset($_COOKIE['order_id']) ? $_COOKIE['order_id'] : false;
    }

    public function getOrderPrice()
    {
        $extra = 1;
        if ($this->useToman()) {
            $extra = 10;
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        $amount = $order->getGrandTotal() * $extra;
        $extra_amount = $amount * $this->getExtraAmount() / 100;
        return (int)$amount + (int)$extra_amount;
    }

    public function useToman()
    {
        return $this->getConfig('isirt');
    }

    private function getConfig($value)
    {
        return $this->_scopeConfig->getValue('payment/behpardakht/' . $value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private function getOrder()
    {

        return $this->_orderFactory->create()->load($this->getOrderId());
    }

    function changeStatus($status)
    {
        $order = $this->getOrder();
        $order->setStatus($status);
        $order->save();
    }

    public function getExtraAmount()
    {
        return $this->getConfig('extra');
    }

    public function getCallBackUrl()
    {
        return $this->_urlBuilder->getUrl('behpardakht/index/callback');
    }

    public function getDescription()
    {
        return $this->getConfig('description');
    }

    public function getBeforeOrderStatus()
    {
        return $this->getConfig('order_status');
    }

    public function getTerminalID()
    {
        return $this->getConfig('terminal_id');
    }

    public function getUserName()
    {
        return $this->getConfig('user_name');
    }

    public function getUserPassword()
    {
        return $this->getConfig('user_password');
    }

    public function getFormData($paramter)
    {
        return $this->getConfig($paramter);
    }

    public function countPrice($order_item)
    {
        $price = 0;
        foreach ($order_item as $_item) {
            $price += $_item->getPrice();
        }
        return $price;
    }

    public function verifySettleTransaction()
    {
        $data = $this->getRequest()->getParams();
        $order = $this->getOrder();


        //check for hacked =>if we have log with state
        $response['state'] = false;
        $response['msg'] = "";

        if ($order->getData() && isset($data['SaleReferenceId'])) {
            $orderId = $data['SaleOrderId'];
            $verifySaleOrderId = $data['SaleOrderId'];
            $verifySaleReferenceId = (float)$data['SaleReferenceId'];

            $client = new \SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            $namespace = 'http://interfaces.core.sw.bps.com/';
            $parameters = array(
                'terminalId' => $this->getTerminalID(),
                'userName' => $this->getUserName(),
                'userPassword' => $this->getUserPassword(),
                'orderId' => $orderId,
                'saleOrderId' => $verifySaleOrderId,
                'saleReferenceId' => $verifySaleReferenceId);
            $result = $client->bpVerifyRequest($parameters, $namespace);
            $VerifyAnswer = explode(',', $result->return);

            if ($VerifyAnswer[0] == '0') {
                // Call the SOAP method
                $result = $client->bpSettleRequest($parameters, $namespace);
                $SetlleAnswer = explode(',', $result->return);
                if ($SetlleAnswer[0] == '0') {
                    $Pay_Status = 'OK';
                }
            }
            if ($VerifyAnswer[0] <> '0' AND $VerifyAnswer[0] != '') {
                $result = $client->bpInquiryRequest($parameters, $namespace);
                $InquiryAnswer = explode(',', $result->return);
                if ($InquiryAnswer[0] == '0') {
                    // Call the SOAP method
                    $result = $client->bpSettleRequest($parameters, $namespace);
                    $SetlleAnswer = $result;
                } else {
                    // Call the SOAP method
                    $result = $client->bpReversalRequest($parameters, $namespace);
                }
            }
            if ($Pay_Status == 'OK') {
                $response['state'] = true;
                $response['msg'] = __('تراکنش با موفقیت ثبت شد . شماره تراکنش : %1', $verifySaleReferenceId);
                $this->addTransactionToOrder($order, array_merge(['msg' => $response['msg']], $data));
                $this->changeStatus($this->getAfterOrderStatus());
            } else {
                $response['state'] = false;
                $response['msg'] = "ترکانش ناموفق بوده است.";
                $this->changeStatus(Order::STATE_CANCELED);
            }
            //unset order id
            $this->removeOrderId();
        } else {
            $response['msg'] = "ترکانش ناموفق بوده است و یا این تراکنش قبلا اعتبار سنجی شده است.";
            $response['state'] = false;

        }
        return $response;
    }

    public function getAfterOrderStatus()
    {
        return $this->getConfig('after_order_status');
    }

    function removeOrderId()
    {
        setcookie("order_id", "", time() - 3600, "/");
    }

    public function addTransactionToOrder($order, $paymentData = array())
    {

        $payment = $order->getPayment();
        $payment->setTransactionId($paymentData['SaleReferenceId']);
        $transaction = $payment->addTransaction("capture", null, false, $paymentData['msg']);
        $transaction
            ->setMethod('BehPardakht')
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setPreparedMessage($paymentData['msg']);
        $transaction->setParentTxnId($paymentData['SaleReferenceId']);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $paymentData);
        $transaction->save();
        $order->save();
    }
}

