<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Api;

    use Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface;

    interface OrderManagementInterface
    {
        /**
         * Process event
         *
         * @param string $event_type
         * @param string $event_time
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface $order
         *
         * @return string
         */
        function processEvent(string $event_type, string $event_time, OrderInterface $order): string;

        /**
         * Process order
         *
         * @param \Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface $order
         * @return string
         */
        function processOrder(OrderInterface $order): string;
    }
?>