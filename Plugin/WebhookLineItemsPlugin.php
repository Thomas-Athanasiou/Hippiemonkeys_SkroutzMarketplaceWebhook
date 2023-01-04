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

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Plugin;

    use Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface;

    class WebhookLineItemsPlugin
    {
        /**
         * Before Process Webhook Event
         *
         * @access public
         *
         * @param \Hippiemonkeys\SkroutzMarketplaceWebhook\Api\OrderManagementInterface $orderManagement
         * @param string $eventType
         * @param string $eventTime
         * @param \Hippiemonkeys\SkroutzMarketplace\Api\Data\OrderInterface $order
         *
         * @return mixed[]
         */
        public function beforeProcessWebhookEvent(OrderManagementInterface $orderManagement, string $eventType, string $eventTime, OrderInterface $order): array
        {
            $lineItems = $order->getLineItems();
            foreach ($lineItems as $lineItem)
            {
                $id = (string) $lineItem->getId();
                if($id !== '')
                {
                    $lineItem->setSkroutzId($id);
                    $lineItem->setId(null);
                }
            }

            return [$eventType, $eventTime, $order];
        }
    }
?>