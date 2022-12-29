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
        public function beforeProcessWebhookEvent(
            OrderManagementInterface $orderManagement,
            string $event_type,
            string $event_time,
            OrderInterface $order
        )
        {
            $lineItems = $order->getLineItems();
            foreach ($lineItems as $lineItem)
            {
                $lineItem->setSkroutzId((string) $lineItem->getId());
                $lineItem->setId(null);
            }

            return [$event_type, $event_time, $order];
        }
    }
?>