<?php

namespace Bitcoder\SamanBank\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\UrlInterface;
use Bitcoder\SamanBank\Block\Redirect;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrder();

        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        setcookie('order_id', $orderId->getId(), time()+3600, '/', $domain, false);


    }
}