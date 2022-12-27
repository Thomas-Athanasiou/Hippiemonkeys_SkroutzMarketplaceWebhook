<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model;

    use Hippiemonkeys\SkroutzMarketplace\Api\Data\InvoiceDetailsInterface,
        Hippiemonkeys\SkroutzMarketplace\Model\Order as AbstractModel,
        Hippiemonkeys\SkroutzMarketplace\Model\ResourceModel\Order as ResourceModel;

    class Order
    extends AbstractModel
    {
        /**
         * @inheritdoc
         */
        public function setInvoiceDetails(?InvoiceDetailsInterface $invoiceDetails): Order
        {
            $invoiceDetailsId = $invoiceDetails ? $invoiceDetails->getId() : null;
            if(!$invoiceDetails || ($invoiceDetails && $invoiceDetailsId))
            {
                $this->setData(ResourceModel::FIELD_INVOICE_DETAILS_ID, $invoiceDetailsId);
            }
            return $this->setData(self::FIELD_INVOICE_DETAILS, $invoiceDetails);
        }
    }
?>