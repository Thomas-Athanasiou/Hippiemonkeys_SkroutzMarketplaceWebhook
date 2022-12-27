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
        Hippiemonkeys\SkroutzMarketplace\Api\CustomerRepositoryInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface,
        Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface,
        Hippiemonkeys\SkroutzMarketplace\Exception\NoSuchEntityException;

    class OrderCustomerManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_SAVE_INVOICE_DETAILS_DATA = 'save_customer_data';

        /**
          * @param \Psr\Log\LoggerInterface $logger,
          * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config,
          * @param \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface $addressRepository,
          * @param \Hippiemonkeys\SkroutzMarketplace\Api\CustomerRepositoryInterface $customerRepository,
          * @param \Hippiemonkeys\SkroutzMarketplace\Api\OrderRepositoryInterface $orderRepository
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
            $this->_addressRepository = $addressRepository;
            $this->_customerRepository = $customerRepository;
            $this->_orderRepository = $orderRepository;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            $customer = $order->getCustomer();
            if($customer !== null)
            {
                $address = $customer->getAddress();
                $customerRepository = $this->getCustomerRepository();
                try
                {
                    $persistentCustomer = $customerRepository->getBySkroutzId(
                        $customer->getSkroutzId()
                    );
                    $customer->setId( $persistentCustomer->getId() );
                    $persistentAddress = $persistentCustomer->getAddress();
                    if($persistentAddress !== null && $address !== null)
                    {
                        $address->setId( $persistentAddress->getId() );
                    }
                }
                catch(NoSuchEntityException)
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
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface
         */
        private $_addressRepository;

        /**
         * Gets Address Repository
         *
         * @return \Hippiemonkeys\SkroutzMarketplace\Api\AddressRepositoryInterface
         */
        protected function getAddressRepository() : AddressRepositoryInterface
        {
            return $this->_addressRepository;
        }

        /**
         * Customer Repository property
         *
         * @var \Hippiemonkeys\SkroutzMarketplace\Api\CustomerRepositoryInterface
         */
        private $_customerRepository;

        /**
         * Gets Customer Repository
         *
         * @return \Hippiemonkeys\SkroutzMarketplace\Api\CustomerRepositoryInterface
         */
        protected function getCustomerRepository() : CustomerRepositoryInterface
        {
            return $this->_customerRepository;
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