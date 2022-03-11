<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys
     * @copyright Copyright (c) 2022 Hippiemonkeys (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Api\Data;

    use  Hippiemonkeys\SkroutzSmartCart\Api\Data\CustomerInterface as SkroutzSmartCartInvoiceDetailsInterface;

    interface InvoiceDetailsInterface
    extends SkroutzSmartCartInvoiceDetailsInterface
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