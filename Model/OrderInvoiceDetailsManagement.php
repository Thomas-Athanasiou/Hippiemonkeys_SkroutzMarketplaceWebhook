<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\InvoiceDetailsRepositoryInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface,
        Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Hippiemonkeys\SkroutzMarketplace\Exception\NoSuchEntityException;

    class OrderInvoiceDetailsManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_SAVE_INVOICE_DETAILS_DATA = 'save_invoice_details_data';

        /**
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface $addressRepository
         * @param \Hippiemonkeys\SkroutzMarketplace\Api\InvoiceDetailsRepositoryInterface $invoiceDetailsRepository
         * @param \Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface $orderRepository
         */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config,
            AddressRepositoryInterface $addressRepository,
            InvoiceDetailsRepositoryInterface $invoiceDetailsRepository,
            OrderRepositoryInterface $orderRepository
        )
        {
            parent::__construct($logger, $config);
            $this->_addressRepository           = $addressRepository;
            $this->_invoiceDetailsRepository    = $invoiceDetailsRepository;
            $this->_orderRepository             = $orderRepository;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            $orderRepository = $this->getOrderRepository();
            $invoiceDetails = $order->getInvoiceDetails();
            try
            {
                $persistentOrder = $orderRepository->getByCode( $order->getCode() );
                $order->setId( $persistentOrder->getId() );
                if($invoiceDetails !== null)
                {
                    $persistentInvoiceDetails = $persistentOrder->getInvoiceDetails();
                    if($persistentInvoiceDetails)
                    {
                        $invoiceDetails->setId( $persistentInvoiceDetails->getId() );
                    }

                    $address = $invoiceDetails->getAddress();
                    if($address !== null && $persistentInvoiceDetails !== null)
                    {
                        $persistentAddress = $persistentInvoiceDetails->getAddress();
                        if($persistentAddress !== null)
                        {
                            $address->setId( $persistentAddress->getId() );
                        }
                    }
                }
            }
            catch(NoSuchEntityException)
            {
                /** Order doesnt exist in the first place */
            }

            if($invoiceDetails !== null)
            {
                $address = $invoiceDetails->getAddress();
                if($address !== null)
                {
                    $this->getAddressRepository()->save($address);
                    $invoiceDetails->setAddress($address);
                }
                $this->getInvoiceDetailsRepository()->save($invoiceDetails);
                $order->setInvoiceDetails($invoiceDetails);
            }

            $orderRepository->save($order);

            return $order->getCode();
        }
        /**
         * @inheritdoc
         */
        protected function canProcessOrder(): bool
        {
            $config = $this->getConfig();
            return $config->getFlag(self::CONFIG_ACTIVE) && $config->getFlag(self::CONFIG_SAVE_INVOICE_DETAILS_DATA);
        }

        /**
         * Address Repository property
         *
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface
         */
        private $_addressRepository;

        /**
         * Gets Address Repository
         *
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface
         */
        protected function getAddressRepository() : AddressRepositoryInterface
        {
            return $this->_addressRepository;
        }

        /**
         * Invoice Details Repository property
         *
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\InvoiceDetailsRepositoryInterface
         */
        private $_invoiceDetailsRepository;

        /**
         * Gets Invoice Details Repository
         *
         * @return \Hippiemonkeys\SkroutzMarketplace\Api\InvoiceDetailsRepositoryInterface
         */
        protected function getInvoiceDetailsRepository() : InvoiceDetailsRepositoryInterface
        {
            return $this->_invoiceDetailsRepository;
        }

        /**
         * Order Repository property
         *
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface
         */
        private $_orderRepository;

        /**
         * Gets Order Repository
         *
         * @return \Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface
         */
        protected function getOrderRepository() : OrderRepositoryInterface
        {
            return $this->_orderRepository;
        }
    }
?>