<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Hippiemonkeys\SkroutzSmartCart\Model\PickupWindow as AbstractModel,
        Hippiemonkeys\SkroutzSmartCartWebhook\Model\ResourceModel\PickupWindow as ResourceModel;

    class PickupWindow
    extends AbstractModel
    {
        /**
         * @inheritdoc
         * @todo This logic need imporvement.
         *       A different resource model with a virtualtype repository on the order managers should be a fine change for a future update
         */
        protected function _construct()
        {
            $this->_init(ResourceModel::class);
        }
    }
?>