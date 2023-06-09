<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Intelligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model\ResourceModel;

    use Hippiemonkeys\SkroutzMarketplace\Model\ResourceModel\LineItemRejectionReason as AbstractResource;

    class LineItemRejectionReason
    extends AbstractResource
    {
        /**
         * @inheritdoc
         */
        protected function _construct()
        {
            parent::_construct();
            $this->_isPkAutoIncrement = false;
        }
    }
?>