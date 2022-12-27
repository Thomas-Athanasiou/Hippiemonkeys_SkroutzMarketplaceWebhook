<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Api\Data;

    use  Hippiemonkeys\SkroutzMarketplace\Api\Data\CustomerInterface as SkroutzMarketplaceInvoiceDetailsInterface;

    interface InvoiceDetailsInterface
    extends SkroutzMarketplaceInvoiceDetailsInterface
    {
        /**
         * Gets Magento Id
         *
         * @return int
         */
        function getMagentoId(): int;

        /**
         * Sets Magento Id
         *
         * @param int $magentoId
         *
         * @returns $this
         */
        function setMagentoId(int $magentoId);
    }

?>