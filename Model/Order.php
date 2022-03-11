<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys
     * @copyright Copyright (c) 2022 Hippiemonkeys (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Hippiemonkeys\SkroutzSmartCart\Model\Order as AbstractModel,
        Hippiemonkeys\SkroutzSmartCart\Model\ResourceModel\Order as ResourceModel;

    class Order
    extends AbstractModel
    {
        /**
         * @inheritdoc
         */
        public function setInvoiceDetails($invoiceDetails)
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