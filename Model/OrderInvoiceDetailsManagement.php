<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Hippiemonkeys\SkroutzSmartCart\Exception\NoSuchEntityException;

    class OrderInvoiceDetailsManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_SAVE_INVOICE_DETAILS_DATA = 'save_invoice_details_data';

        /**
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface $addressRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface $invoiceDetailsRepository
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface $orderRepository
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
            $orderRepository    = $this->getOrderRepository();
            $invoiceDetails     = $order->getInvoiceDetails();
            try
            {
                $persistentOrder = $orderRepository->getByCode( $order->getCode() );
                $order->setId( $persistentOrder->getId() );
                if($invoiceDetails)
                {
                    $persistentInvoiceDetails = $persistentOrder->getInvoiceDetails();
                    if($persistentInvoiceDetails)
                    {
                        $invoiceDetails->setId( $persistentInvoiceDetails->getId() );
                    }

                    $address = $invoiceDetails->getAddress();
                    if($address && $persistentInvoiceDetails)
                    {
                        $persistentAddress = $persistentInvoiceDetails->getAddress();
                        if($persistentAddress)
                        {
                            $address->setId( $persistentAddress->getId() );
                        }
                    }
                }
            }
            catch(NoSuchEntityException $exception)
            {
                /** Order doesnt exist in the first place */
            }

            if($invoiceDetails)
            {
                $address = $invoiceDetails->getAddress();
                if($address)
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
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface
         */
        private $_addressRepository;

        /**
         * Gets Address Repository
         *
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface
         */
        protected function getAddressRepository() : AddressRepositoryInterface
        {
            return $this->_addressRepository;
        }

        /**
         * Invoice Details Repository property
         *
         * @var \Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface
         */
        private $_invoiceDetailsRepository;

        /**
         * Gets Invoice Details Repository
         *
         * @return \Hippiemonkeys\SkroutzSmartCart\Api\InvoiceDetailsRepositoryInterface
         */
        protected function getInvoiceDetailsRepository() : InvoiceDetailsRepositoryInterface
        {
            return $this->_invoiceDetailsRepository;
        }

        /**
         * Order Repository property
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
    }
?>