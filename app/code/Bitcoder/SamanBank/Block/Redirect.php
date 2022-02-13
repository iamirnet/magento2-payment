<?php

namespace Bitcoder\SamanBank\Block;

use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

/**
 * Class Redirect
 * @package Bitcoder\SamanBank\Block
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
    protected $samanbank_log;
    protected $customer_session;

    /**
     * @var $order Order
     */
    protected $order;
    protected $response;
    protected $sendToBankUrl = "";
    private $namespace = 'http://interfaces.core.sw.bps.com/';

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
        $data = array('MerchantID' => $this->getMerchantID(),
            'Amount' => $this->getOrderPrice(),
            'CallbackURL' => $this->getCallBackUrl(),
            'OrderId' => $this->getOrderId());
        $response['state'] = true;
        $response = array_merge($response, $data);
        return $response;

    }

    public function getOrderId()
    {

        return isset($_COOKIE['order_id']) ? $_COOKIE['order_id'] : false;
    }

    public function getMerchantID()
    {
        return $this->getConfig('merchant_id');
    }

    private function getConfig($value)
    {
        return $this->_scopeConfig->getValue('payment/samanbank/' . $value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    private function getOrder()
    {

        return $this->_orderFactory->create()->load($this->getOrderId());
    }

    public function getExtraAmount()
    {
        return $this->getConfig('extra');
    }

    public function getCallBackUrl()
    {
        return $this->_urlBuilder->getUrl('samanbank/index/callback');
    }

    function changeStatus($status)
    {
        $order = $this->getOrder();
        $order->setStatus($status);
        $order->save();
    }

    public function getBeforeOrderStatus()
    {
        return $this->getConfig('order_status');
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

    public function getDescription()
    {
        return $this->getConfig('description');
    }

    public function verifySettleTransaction()
    {
        $data = $this->getRequest()->getParams();
        $order = $this->getOrder();

        //check for hacked =>if we have log with state
        $response['state'] = false;
        $response['msg'] = "";

        if (!$order->getData()) {
            $response['msg'] = "این تراکنش قبلا اعتبار سنجی شده است.";

        } else {
            $State = $data['State'];
            $RefNum = $data['RefNum'];
            $MerchantCode = $this->getMerchantID();
            if (isset($State) && $State == "OK") {

                $soapclient = new \SoapClient('https://verify.sep.ir/Payments/ReferencePayment.asmx?WSDL');
                $res = $soapclient->VerifyTransaction($RefNum, $MerchantCode);

                if ($res <= 0) {
                    // Transaction Failed
                    $response['state'] = false;
                    $response['msg'] = "ترکانش ناموفق بوده است.";
                    return array("status" => false, "msg" => $res);
                } else {
                    // Transaction Successful
                    $response['state'] = true;
                    $response['msg'] = __('تراکنش با موفقیت ثبت شد . شماره تراکنش : %1', $RefNum);
                    $this->addTransactionToOrder($order, array_merge(['msg' => $response['msg']], $data));
                }
            } else {
                $response['state'] = false;
                $response['msg'] = "ترکانش ناموفق بوده است.";

            }
            if ($response['state']) {
                $this->changeStatus($this->getAfterOrderStatus());
            } else {
                $this->changeStatus(Order::STATE_CANCELED);
            }
            //unset order id
            $this->removeOrderId();
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
        $payment->setTransactionId($paymentData['RefNum']);
        $transaction = $payment->addTransaction("capture", null, false, $paymentData['msg']);
        $transaction
            ->setMethod('SamanBank')
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setPreparedMessage($paymentData['msg']);
        $transaction->setParentTxnId($paymentData['RefNum']);
        $transaction->setIsClosed(true);
        $transaction->setAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $paymentData);
        $transaction->save();
        $order->save();
    }
}

