<?php
    /**
     * @author Thomas Athanasiou at Hippiemonkeys | @Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE (https://hippiemonkeys.com)
     * @package Hippiemonkeys_SkroutzSmartCartWebhook
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\SkroutzSmartCartWebhook\Model;

    use Psr\Log\LoggerInterface,
        Hippiemonkeys\SkroutzSmartCartWebhook\Api\OrderManagementInterface,
        Hippiemonkeys\SkroutzSmartCart\Api\Data\OrderInterface,
        Hippiemonkeys\Sales\Api\Helper\InvoiceHelperInterface,
        Hippiemonkeys\Sales\Api\Helper\ShipmentHelperInterface,
        Hippiemonkeys\Sales\Api\Helper\CreditMemoHelperInterface,
        Magento\Sales\Api\OrderManagementInterface as MagentoOrderManagementInterface,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class OrderMagentoOrderUpdateManagement
    extends OrderManagementAbstract
    implements OrderManagementInterface
    {
        protected const
            CONFIG_ACTIVE               = 'active',
            CONFIG_ACTIVE_UPDATE_ORDER  = 'active_update_order',
            CONFIG_SEND_INVOICE_EMAIL   = 'send_invoice_email',
            CONFIG_SEND_SHIPMENT_EMAIL  = 'send_shipment_email';

        /**
         * @param \Psr\Log\LoggerInterface $logger
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param \Hippiemonkeys\Sales\Api\Helper\InvoiceHelperInterface $invoiceHelper
         * @param \Hippiemonkeys\Sales\Api\Helper\ShipmentHelperInterface $shipmentHelper
         * @param \Magento\Sales\Api\OrderManagementInterface $magentoOrderManagement
         */
        public function __construct(
            LoggerInterface $logger,
            ConfigInterface $config,
            InvoiceHelperInterface $invoiceHelper,
            ShipmentHelperInterface $shipmentHelper,
            CreditMemoHelperInterface $creditMemoHelper,
            MagentoOrderManagementInterface $magentoOrderManagement
        )
        {
            parent::__construct($logger, $config);
            $this->_invoiceHelper           = $invoiceHelper;
            $this->_shipmentHelper          = $shipmentHelper;
            $this->_creditMemoHelper        = $creditMemoHelper;
            $this->_magentoOrderManagement  = $magentoOrderManagement;
        }

        /**
         * @inheritdoc
         */
        public function processOrder(OrderInterface $order): string
        {
            $magentoOrder = $order->getMagentoOrder();
            if($magentoOrder)
            {
                $logger = $this->getLogger();
                $config = $this->getConfig();
                switch($order->getState())
                {
                    case OrderInterface::STATE_RETURNED:
                    case OrderInterface::STATE_CANCELLED:
                    case OrderInterface::STATE_REJECTED:
                    case OrderInterface::STATE_EXPIRED:
                        if($magentoOrder->hasShipments())
                        {
                            $this->getCreditMemoHelper()->doCreditMemoRequest(
                                $magentoOrder,
                                false // TODO EMAIL
                            );
                        }
                        else if(!$order->isCanceled())
                        {
                            $this->getMagentoOrderManagement()->cancel($magentoOrder->getId());
                        }
                        break;
                    case OrderInterface::STATE_ACCEPTED:
                    case OrderInterface::STATE_DISPATCHED:
                    case OrderInterface::STATE_DELIVERED:
                        try
                        {
                            if(!$magentoOrder->hasInvoices())
                            {
                                $this->getInvoiceHelper()->doInvoiceRequest(
                                    $magentoOrder,
                                    $config->getFlag(self::CONFIG_SEND_INVOICE_EMAIL)
                                );
                            }
                        }
                        catch(\Exception $exception)
                        {
                            $logger->error($exception->getMessage());
                        }
                        try
                        {
                            if(!$magentoOrder->hasShipments())
                            {
                                $this->getShipmentHelper()->doShipmentRequest(
                                    $magentoOrder,
                                    new \Magento\Framework\DataObject(),
                                    $config->getFlag(self::CONFIG_SEND_SHIPMENT_EMAIL),
                                    false
                                );
                            }
                        }
                        catch(\Exception $exception)
                        {
                            $logger->error($exception->getMessage());
                        }
                        break;
                }
            }
            return $order->getCode();
        }

        /**
         * @inheritdoc
         */
        protected function canProcessOrder(): bool
        {
            $config = $this->getConfig();
            return $config->getFlag(self::CONFIG_ACTIVE) && $config->getFlag(self::CONFIG_ACTIVE_UPDATE_ORDER);
        }

        /**
         * Invoice Helper property
         *
         * @var \Hippiemonkeys\Sales\Api\Helper\InvoiceHelperInterface
         */
        private $_invoiceHelper;

        /**
         * Gets Invoice Helper
         *
         * @return \Hippiemonkeys\Sales\Api\Helper\InvoiceHelperInterface
         */
        protected function getInvoiceHelper() : InvoiceHelperInterface
        {
            return $this->_invoiceHelper;
        }

        /**
         * Credit Memo Helper property
         *
         * @var \Hippiemonkeys\Sales\Api\Helper\CreditMemoHelperInterface
         */
        private $_creditMemoHelper;

        /**
         * Gets Credit Memo Helper
         *
         * @return \Hippiemonkeys\Sales\Api\Helper\CreditMemoHelperInterface
         */
        protected function getCreditMemoHelper() : CreditMemoHelperInterface
        {
            return $this->_creditMemoHelper;
        }

        /**
         * Shipment Helper property
         *
         * @var \Hippiemonkeys\Sales\Api\Helper\ShipmentHelperInterface
         */
        private $_shipmentHelper;

        /**
         * Gets Shipment Helper
         *
         * @return \Hippiemonkeys\Sales\Api\Helper\ShipmentHelperInterface
         */
        protected function getShipmentHelper() : ShipmentHelperInterface
        {
            return $this->_shipmentHelper;
        }

        /**
         * Magento Order Management property
         *
         * @var \Magento\Sales\Api\OrderManagementInterface
         */
        private $_magentoOrderManagement;

        /**
         * Gets Magento Order Management
         *
         * @return \Magento\Sales\Api\OrderManagementInterface
         */
        protected function getMagentoOrderManagement() : MagentoOrderManagementInterface
        {
            return $this->_magentoOrderManagement;
        }
    }
?>