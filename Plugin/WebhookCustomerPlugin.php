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

    class WebhookCustomerPlugin
    {
        public function beforeProcessWebhookEvent(
            OrderManagementInterface $orderManagement,
            string $event_type,
            string $event_time,
            OrderInterface $order
        )
        {
            $customer = $order->getCustomer();
            if($customer !== null)
            {
                $customer->setSkroutzId((string) $customer->getId());
                $customer->setId(null);
            }

            return [$event_type, $event_time, $order];
        }
    }
?>