<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bitcoder\BehPardakht\Model;
use Magento\Framework\Pricing\PriceCurrencyInterface;


/**
 * Pay In Store payment method model
 */
class BehPardakht extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'behpardakht';
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_isOffline = false;
//
//
//    protected $_isGateway = false;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canOrder = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canAuthorize = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canCapture = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canCapturePartial = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canRefund = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canRefundInvoicePartial = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canVoid = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canUseInternal = false;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canUseCheckout = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canFetchTransactionInfo = true;
//
//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_canReviewPayment = true;
//
//    /**
//     * Website Payments Pro instance
//     *
//     * @var \Magento\Paypal\Model\Pro
//     */
//    /**
//     * Get instructions text from config
//     *
//     * @return string
//     */
//    /**
//     * @var \Magento\Store\Model\StoreManagerInterface
//     */
//    protected $_storeManager;
//
//    /**
//     * @var \Magento\Framework\UrlInterface
//     */
//    protected $_urlBuilder;
//
//    /**
//     * @var \Magento\Checkout\Model\Session
//     */
//    protected $_checkoutSession;
//
//    /**
//     * @var \Magento\Framework\Exception\LocalizedExceptionFactory
//     */
//    protected $_exception;
//    /**
//     * @var PriceCurrencyInterface
//     */
//    protected $priceCurrency;
//
//    /**
//     * @param \Magento\Framework\Model\Context $context
//     * @param \Magento\Framework\Registry $registry
//     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
//     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
//     * @param \Magento\Payment\Helper\Data $paymentData
//     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
//     * @param Logger $logger
//     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
//     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
//     * @param array $data
//     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
//     */
//    public function __construct(
//        \Magento\Framework\Model\Context $context,
//        \Magento\Framework\Registry $registry,
//        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
//        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
//        \Magento\Payment\Helper\Data $paymentData,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
//        \Magento\Payment\Model\Method\Logger $logger,
//        /* add by Amit Bera */
//        \Magento\Framework\UrlInterface $urlBuilder,
//        \Magento\Checkout\Model\Session $checkoutSession,
//        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
//        \Magento\Store\Model\StoreManagerInterface $storeManager,
//        PriceCurrencyInterface $priceCurrency,
//        /* add by Amit Bera */
//        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
//        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
//        array $data = []
//    ) {
//        parent::__construct(
//            $context,
//            $registry,
//            $extensionFactory,
//            $customAttributeFactory,
//            $paymentData,
//            $scopeConfig,
//            $logger,
//            $resource,
//            $resourceCollection,
//            $data
//        );
//
//
//
//        $this->_storeManager = $storeManager;
//        $this->_urlBuilder = $urlBuilder;
//        $this->_checkoutSession = $checkoutSession;
//        $this->_exception = $exception;
//    }
//
//    public function getInstructions()
//    {
//        return trim($this->getConfigData('instructions'));
//    }
//    public function getCheckoutRedirectUrl()
//    {
//        return $this->_urlBuilder->getUrl('behpardakht/index/index');
//    }


}
