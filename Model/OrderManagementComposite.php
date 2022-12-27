<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class OrderManagementComposite
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_ACTIVE = 'active';

        /**
         * Constructor
         *
         * @access public
         *
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface[] $orderManagements
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
         * @var \Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface[]
         */
        private $_orderManagements;

        /**
         * Gets Order Managements
         *
         * @return \Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface[]
         */
        protected function getOrderManagements(): array
        {
            return $this->_orderManagements;
        }
    }
?>