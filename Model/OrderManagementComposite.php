<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys
     * @copyright Copyright (c) 2022 Hippiemonkeys (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class OrderManagementComposite
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_ACTIVE = 'active';

        /**
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface $orderManagements
         */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config,
            array $orderManagements
        )
        {
            parent::__construct($logger, $config);
            $this->_orderManagements = $orderManagements;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            foreach($this->getOrderManagements() as $orderManagement)
            {
                $orderManagement->processOrder($order);
            }
            return $order->getCode();
        }

        /**
         * @inheritdoc
         */
        protected function canProcessOrder(): bool
        {
            return $this->getConfig()->getFlag(self::CONFIG_ACTIVE);
        }

        /**
         * Order Managements property
         *
         * @var \Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface[]
         */
        private $_orderManagements;

        /**
         * Gets Order Managements
         *
         * @return \Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface[]
         */
        protected function getOrderManagements(): array
        {
            return $this->_orderManagements;
        }
    }
?>