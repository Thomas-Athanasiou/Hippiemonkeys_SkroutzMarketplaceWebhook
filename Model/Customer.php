<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzMarketplaceWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzMarketplaceWebhook\Model;

    use Hippiemonkeys\SkroutzMarketplace\Model\Customer as AbstractModel;

    class Customer
    extends AbstractModel
    {
        /**
         * @inheritdoc
         * @todo This logic need imporvement.
         *       A different resource model with a virtualtype repository on the order managers should be a fine change for a future update
         */
        public function setId($id)
        {
            $this->setSkroutzId((string) $id);
        }
    }
?>