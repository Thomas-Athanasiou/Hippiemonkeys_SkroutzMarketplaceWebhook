<?php
    /**
     * @Thomas-Athanasiou
     *
     * @author Thomas Athanasiou {thomas@hippiemonkeys.com}
     * @link https://hippiemonkeys.com
     * @link https://github.com/Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE All Rights Reserved.
     * @license http://www.gnu.org/licenses/ GNU General Public License, version 3
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model;

    use Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\SkroutzMarketplace\Model\OrderManagement as ParentOrderManagement,
        Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface;

    class OrderManagement
    extends ParentOrderManagement
    implements OrderManagementInterface
    {
        /**
         * {@inheritdoc}
         */
        public function processWebhookEvent(string $event_type, string $event_time, OrderInterface $order): void
        {
            $this->setEventType($event_type);
            $this->setEventTime($event_time);
            $this->processOrder($order);
        }

        /**
         * Event Type property
         *
         * @access private
         *
         * @var string
         */
        private $_eventType;

        /**
         * Gets Event Type
         *
         * @access protected
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
         * @access private
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
         * @access private
         *
         * @var string
         */
        private $_eventTime;

        /**
         * Gets Event Time
         *
         * @access protected
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
         * @access private
         *
         * @param string $eventTime
         */
        private function setEventTime(string $eventTime): void
        {
            $this->_eventTime = $eventTime;
        }
    }
?>