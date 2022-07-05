<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\CustomerRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\AcceptOptionsRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\RejectOptionsRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface,
        Magento\ConfigurableProduct\Api\LinkManagementInterface,
        Magento\Store\Model\StoreManagerInterface,
        Magento\Catalog\Api\ProductRepositoryInterface,
        Magento\Quote\Model\QuoteFactory,
        Magento\Quote\Model\QuoteManagement,
        Magento\Sales\Model\Service\OrderService,
        Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepositoryInterface,
        Magento\Quote\Model\Quote\Address\RateFactory as QuoteRateFactory,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType,
        Hippiemonkeys\SkroutzSmartCart\Exception\NoSuchEntityException;

    class OrderMagentoOrderCreateManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_ACTIVE                   = 'active',
            CONFIG_ACTIVE_CREATE_ORDER      = 'active_create_order',
            CONFIG_DEFAULT_ORDER_COUNTRY    = 'default_order_country',
            CONFIG_DEFAULT_ORDER_EMAIL      = 'default_order_email',
            CONFIG_DEFAULT_ORDER_TELEPHONE  = 'default_order_telephone',
            CONFIG_DEFAULT_ORDER_FAX        = 'default_order_fax',
            CONFIG_NEW_ORDER_STORE_ID       = 'new_order_store_id',
            CONFIG_NEW_ORDER_STATUS_CODE    = 'new_order_status_code',

            FORMAT_STREET                   = '%s %u';

        /**
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface $addressRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\CustomerRepositoryInterface $customerRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface $invoiceDetailsRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\AcceptOptionsRepositoryInterface $acceptOptionsRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\RejectOptionsRepositoryInterface $rejectOptionsRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface $orderRepository
         * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
         * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
         * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
         * @param \Magento\Sales\Model\Service\OrderService $orderService
         * @param \Magento\Sales\Api\OrderRepositoryInterface $magentoOrderRepository
         * @param \Magento\Quote\Model\Quote\Address\RateFactory $quoteRateFactory
         * @param \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement
         * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
         */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config,
            AddressRepositoryInterface $addressRepository,
            CustomerRepositoryInterface $customerRepository,
            InvoiceDetailsRepositoryInterface $invoiceDetailsRepository,
            AcceptOptionsRepositoryInterface $acceptOptionsRepository,
            RejectOptionsRepositoryInterface $rejectOptionsRepository,
            OrderRepositoryInterface $orderRepository,
            StoreManagerInterface $storeManager,
            ProductRepositoryInterface $productRepository,
            QuoteFactory $quoteFactory,
            QuoteManagement $quoteManagement,
            OrderService $orderService,
            MagentoOrderRepositoryInterface $magentoOrderRepository,
            QuoteRateFactory $quoteRateFactory,
            LinkManagementInterface $linkManagement,
            ConfigurableType $configurableType
        )
        {
            parent::__construct($logger, $config);
            $this->_addressRepository       = $addressRepository;
            $this->_customerRepository      = $customerRepository;
            $this->_addressRepository       = $addressRepository;
            $this->_orderRepository         = $orderRepository;
            $this->_storeManager            = $storeManager;
            $this->_productRepository       = $productRepository;
            $this->_quoteFactory            = $quoteFactory;
            $this->_quoteManagement         = $quoteManagement;
            $this->_orderService            = $orderService;
            $this->_quoteRateFactory        = $quoteRateFactory;
            $this->_magentoOrderRepository  = $magentoOrderRepository;
            $this->_linkManagement          = $linkManagement;
            $this->_configurableType        = $configurableType;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            $config             = $this->getConfig();
            $orderRepository    = $this->getOrderRepository();
            try
            {
                $persistedOrder = $orderRepository->getByCode( $order->getCode() );
                $order->setId( $persistedOrder->getId() );
                $order->setMagentoOrder( $persistedOrder->getMagentoOrder() );
            }
            catch(NoSuchEntityException $exception)
            {
                /** Order doesnt exist in the first place */
            }

            $magentoOrder = $order->getMagentoOrder();
            if(!$magentoOrder)
            {
                $storeId = $config->getData(self::CONFIG_NEW_ORDER_STORE_ID);
                $store = $this->getStoreManager()->getStore( $storeId ? $storeId : null);
                $quote = $this->getQuoteFactory()->create();
                $quote->setStore($store);
                $quote->setCurrency();

                $productRepository  = $this->getProductRepository();
                $linkManagement     = $this->getLinkManagement();
                $configurableType   = $this->getConfigurableType();
                foreach($order->getLineItems() as $lineItem)
                {
                    $product = $productRepository->getById($lineItem->getShopUid());
                    if($product->getTypeId() === ConfigurableType::TYPE_CODE)
                    {
                        foreach($linkManagement->getChildren($product->getSku()) as $childProduct)
                        {
                            foreach($configurableType->getConfigurableAttributes($product) as $superAttribute)
                            {
                                $attributeCode = $superAttribute->getProductAttribute()->getAttributeCode();
                                if($childProduct->getAttributeText($attributeCode) === $lineItem->getSize()->getShopValue())
                                {
                                    $product = $childProduct;
                                    $lineItem->setShopUid((int) $childProduct->getEntityId());
                                }
                            }
                        }
                    }

                    $product->setPrice($lineItem->getUnitPrice());
                    $quote->addProduct($product, $lineItem->getQuantity());
                }

                $customer           = $order->getCustomer();
                $customerAddress    = $customer->getAddress();
                $quoteAddressData   = [
                    'firstname'             => $customer->getFirstName(),
                    'lastname'              => $customer->getLastName(),
                    'street'                => sprintf(self::FORMAT_STREET, $customerAddress->getStreetName(), $customerAddress->getStreetNumber()),
                    'city'                  => $customerAddress->getCity(),
                    'country_id'            => $config->getData(self::CONFIG_DEFAULT_ORDER_COUNTRY),
                    'region'                => $customerAddress->getRegion(),
                    'postcode'              => $customerAddress->getZip(),
                    'telephone'             => $config->getData(self::CONFIG_DEFAULT_ORDER_TELEPHONE),
                    'fax'                   => $config->getData(self::CONFIG_DEFAULT_ORDER_FAX),
                    'save_in_address_book'  => 1
                ];

                $quote->setCustomerFirstname( $customer->getFirstName() );
                $quote->setCustomerLastname( $customer->getLastName() );
                $quote->setCustomerEmail( $config->getData(self::CONFIG_DEFAULT_ORDER_EMAIL) );
                $quote->setCustomerIsGuest(true);

                $quote->getBillingAddress()->addData($quoteAddressData);
                $quoteRate = $this->getQuoteRateFactory()->create();
                $quoteRate->setCode('freeshipping_freeshipping')->getPrice(0);
                $quoteShippingAddress = $quote->getShippingAddress();
                $quoteShippingAddress->addData($quoteAddressData);
                $quoteShippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('freeshipping_freeshipping');
                $quoteShippingAddress->addShippingRate($quoteRate);

                $quote->setPaymentMethod('cashondelivery');
                $quote->save();
                $quote->getPayment()->importData(['method' => 'cashondelivery']);
                $quote->collectTotals()->save();

                $magentoOrder = $this->getQuoteManagement()->submit($quote);
                if($magentoOrder)
                {
                    $order->setMagentoOrder($magentoOrder);
                    $magentoOrder->setStatus(
                        $config->getData(self::CONFIG_NEW_ORDER_STATUS_CODE)
                    );
                    $this->getMagentoOrderRepository()->save($magentoOrder);
                    $orderRepository->save($order);
                }
            }
            return $order->getCode();
        }

        /**
         * @inheritdoc
         */
        protected function canProcessOrder(): bool
        {
            $config = $this->getConfig();
            return $config->getFlag(self::CONFIG_ACTIVE) && $config->getFlag(self::CONFIG_ACTIVE_CREATE_ORDER);
        }

        /**
         * Address Repository property
         *
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface
         */
        private $_addressRepository;

        /**
         * Gets Address Repository
         *
         * @return \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface
         */
        protected function getAddressRepository() : AddressRepositoryInterface
        {
            return $this->_addressRepository;
        }

        /**
         * Customer Repository property
         *
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\CustomerRepositoryInterface
         */
        private $_customerRepository;

        /**
         * Gets Customer Repository
         *
         * @return \Hippiemonkeys\SkroutzSmartCart\Api\CustomerRepositoryInterface
         */
        protected function getCustomerRepository() : CustomerRepositoryInterface
        {
            return $this->_customerRepository;
        }

        /**
         * Magento Order Repository property
         *
         * @var \Magento\Sales\Api\OrderRepositoryInterface
         */
        private $_magentoOrderRepository;

        /**
         * Gets Magento Order Repository
         *
         * @return \Magento\Sales\Api\OrderRepositoryInterface
         */
        protected function getMagentoOrderRepository() : MagentoOrderRepositoryInterface
        {
            return $this->_magentoOrderRepository;
        }

        /**
         * Order Repository Interface
         *
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface
         */
        private $_orderRepository;

        /**
         * Gets Order Repository
         *
         * @return \Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface
         */
        protected function getOrderRepository() : OrderRepositoryInterface
        {
            return $this->_orderRepository;
        }

        /**
         * Store Manager property
         *
         * @var \Magento\Store\Model\StoreManagerInterface
         */
        private $_storeManager;

        /**
         * Gets Store Manager
         *
         * @return \Magento\Store\Model\StoreManagerInterface
         */
        protected function getStoreManager() : StoreManagerInterface
        {
            return $this->_storeManager;
        }

        /**
         * Product Repository property
         *
         * @var \Magento\Catalog\Api\ProductRepositoryInterface
         */
        private $_productRepository;

        /**
         * Gets Product Repository
         *
         * @return \Magento\Catalog\Api\ProductRepositoryInterface
         */
        protected function getProductRepository() : ProductRepositoryInterface
        {
            return $this->_productRepository;
        }

        /**
         * Quote Factory property
         *
         * @var \Magento\Quote\Model\QuoteFactory
         */
        private $_quoteFactory;

        /**
         * Gets Quote Factory
         *
         * @return \Magento\Quote\Model\QuoteFactory
         */
        protected function getQuoteFactory() : QuoteFactory
        {
            return $this->_quoteFactory;
        }

        /**
         * Quote Management property
         *
         * @var \Magento\Quote\Model\QuoteManagement
         */
        private $_quoteManagement;

        /**
         * Gets Quote Management
         *
         * @return \Magento\Quote\Model\QuoteManagement
         */
        protected function getQuoteManagement() : QuoteManagement
        {
            return $this->_quoteManagement;
        }

        /**
         * Quote Rate Factory property
         *
         * @var \Magento\Quote\Model\Quote\Address\RateFactory
         */
        private $_quoteRateFactory;

        /**
         * Gets Quote Rate Factory
         *
         * @return \Magento\Quote\Model\Quote\Address\RateFactory
         */
        protected function getQuoteRateFactory() : QuoteRateFactory
        {
            return $this->_quoteRateFactory;
        }

        /**
         * Link Management property
         *
         * @var \Magento\ConfigurableProduct\Api\LinkManagementInterface
         */
        private $_linkManagement;

        /**
         * Gets Link Management
         *
         * @return \Magento\ConfigurableProduct\Api\LinkManagementInterface
         */
        protected function getLinkManagement() : LinkManagementInterface
        {
            return $this->_linkManagement;
        }

        /**
         * Configurable Type property
         *
         * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
         */
        private $_configurableType;

        /**
         * Gets Configurable Type
         *
         * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable
         */
        protected function getConfigurableType() : ConfigurableType
        {
            return $this->_configurableType;
        }
    }
?>