<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Hippiemonkeys\SkroutzSmartCart\Model\LineItem as AbstractModel;

    class LineItem
    extends AbstractModel
    {
        /**
         * @inheritdoc
         * @todo This logic need imporvement.
         *       A different resource model with a virtualtype repository on the order managers should be a fine change for a future update
         */
        public function setId($id)
        {
            if(is_numeric($id))
            {
                $this->setLocalId((int) $id);
            }
            else
            {
                $this->setSkroutzId((string) $id);
            }
        }
    }
?>