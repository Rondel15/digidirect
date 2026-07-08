<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Model;

abstract class SenderAbstract
{
    protected $transportBuilder;
    protected $helperData;
    protected $priceCurrency;
    protected $storeFactory;
    protected $productRepository;

    public function __construct
    (
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Itoris\PriceMatch\Helper\Data $helperData,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreFactory $storeFactory
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->helperData = $helperData;
        $this->storeFactory = $storeFactory;
        $this->productRepository = $productRepository;
    }

    public function send($item, $method)
    {
        if( !(\Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\PriceMatch\Helper\Data')->isEnabled()) ){
            return;
        }

        $templateVar = $this->getFommatVar($item, $method);
        if(!$templateVar) return;

        $toSender = $templateVar['email'];
        $storeId = $templateVar['store_id'];
        $sender = $this->getFromSender($item);

        $template = $this->getTemplateSender($storeId);
        $templateVarsItem = $templateVar['send_vars'];

        $this->sendEmail($sender, $toSender, $template,  $templateVarsItem, $storeId);
    }

    abstract protected function getFommatVar($item, $method);

    abstract protected function getFromSender($storeId);

    abstract protected function getTemplateSender($storeId);

    protected function sendEmail($sender, $toSender, $template,  $templateVars, $storeId)
    {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($toSender, $templateVars['customer_name'])
            ->getTransport();

        $transport->sendMessage();
    }

    protected function formatPrice($price, $storeId)
    {
        return $this->priceCurrency->format(
            $price,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId
        );
    }
}