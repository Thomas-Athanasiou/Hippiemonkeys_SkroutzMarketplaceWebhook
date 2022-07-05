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
        Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface,
        Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Hippiemonkeys\SkroutzSmartCart\Exception\NoSuchEntityException;

    class OrderCustomerManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_SAVE_INVOICE_DETAILS_DATA = 'save_customer_data';

        /**
          * @param \Psr\Log\LoggerInterface $logger,
          * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config,
          * @param \Hippiemonkeys\SkroutzSmartCart\Api\AddressRepositoryInterface $addressRepository,
          * @param \Hippiemonkeys\SkroutzSmartCart\Api\CustomerRepositoryInterface $customerRepository,
          * @param \Hippiemonkeys\SkroutzSmartCart\Api\OrderRepositoryInterface $orderRepository
          */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config,
            AddressRepositoryInterface $addressRepository,
            CustomerRepositoryInterface $customerRepository,
            OrderRepositoryInterface $orderRepository
        )
        {
            parent::__construct($logger, $config);
            $this->_addressRepository   = $addressRepository;
            $this->_customerRepository  = $customerRepository;
            $this->_orderRepository     = $orderRepository;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            $customer = $order->getCustomer();
            if($customer)
            {
                $address = $customer->getAddress();
                $customerRepository = $this->getCustomerRepository();
                try
                {
                    $persistentCustomer = $customerRepository->getBySkroutzId(
                        $customer->getSkroutzId()
                    );
                    $customer->setLocalId( $persistentCustomer->getLocalId() );
                    $persistentAddress = $persistentCustomer->getAddress();
                    if($persistentAddress && $address)
                    {
                        $address->setId( $persistentAddress->getId() );
                    }
                }
                catch(NoSuchEntityException $exception)
                {
                    /** Customer doesnt exist in the first place */
                }
                if($address)
                {
                    $this->getAddressRepository()->save($address);
                }
                $customer->setAddress($address);
                $customerRepository->save($customer);
                $order->setCustomer($customer);

                $orderRepository = $this->getOrderRepository();
            }

            try
            {
                $order->setId( $orderRepository->getByCode( $order->getCode() )->getId() );
            }
            catch(NoSuchEntityException $exception)
            {
                /** Order Doesnt exist in the first place */
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
            return $config->getFlag(self::CONFIG_ACTIVE) && $config->getFlag(self::CONFIG_SAVE_CUSTOMER_DATA);
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