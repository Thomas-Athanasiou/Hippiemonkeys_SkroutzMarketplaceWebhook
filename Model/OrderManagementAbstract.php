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

    abstract class OrderManagementAbstract
    implements OrderManagementInterface
    {
        /**
         * Processes Given Order
         *
         * @param \Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface $order
         *
         * @return string
         */
        public abstract function processOrder(OrderInterface $order): string;

        /**
         * Checks wether the management service can process any orders
         *
         * @return bool
         */
        protected abstract function canProcessOrder(): bool;

        /**
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config
        )
        {
            $this->_logger      = $logger;
            $this->_config      = $config;
            $this->_eventType   = '';
            $this->_eventTime   = '';
        }

        /**
         * @inheritdoc
         */
        public function processEvent(string $event_type, string $event_time, OrderInterface $order): string
        {
            $result = '';
            $this->setEventType($event_type);
            $this->setEventTime($event_time);
            if($this->canProcessOrder())
            {
                $result = $this->processOrder($order);
            };
            return $result;
        }

        /**
         * Config property
         *
         * @var \Hippiemonkeys\Core\Api\Helper\ConfigInterface
         */
        private $_config;

        /**
         * Gets Config
         *
         * @return \Hippiemonkeys\Core\Api\Helper\ConfigInterface
         */
        protected function getConfig(): ConfigInterface
        {
            return $this->_config;
        }


        /**
         * Logger property
         *
         * @var \Psr\Log\LoggerInterface
         */
        private $_logger;


        /**
         * Gets Logger
         *
         * @return \Psr\Log\LoggerInterface
         */
        protected function getLogger(): LoggerInterface
        {
            return $this->_logger;
        }

        /**
         * Event Type property
         *
         * @var string
         */
        private $_eventType;

        /**
         * Gets Event Type
         *
         * @return string
         */
        protected function getEventType(): string
        {
            return $this->_eventType;
        }

        /**
         * Sets Event Type
         *
         * @param string $eventType
         */
        private function setEventType(string $eventType): void
        {
            $this->_eventType = $eventType;
        }

        /**
         * Event Time property
         *
         * @var string
         */
        private $_eventTime;

        /**
         * Gets Event Time
         *
         * @return string
         */
        protected function getEventTime(): string
        {
            return $this->_eventTime;
        }

        /**
         * Sets Event Time
         *
         * @param string $eventTime
         */
        private function setEventTime(string $eventTime): void
        {
            $this->_eventTime = $eventTime;
        }
    }
?>