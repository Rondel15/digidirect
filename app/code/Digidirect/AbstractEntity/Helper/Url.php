<?php

namespace Digidirect\AbstractEntity\Helper;

use Digidirect\AbstractEntity\Model\AbstractEntity\UrlProcessor;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * Class Url
 * @package Digidirect\AbstractEntity\Helper
 */
class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * Url constructor.
     * @param Context $context
     * @param UrlFinderInterface $urlFinder
     * @param MagentoUrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        UrlFinderInterface $urlFinder,
        MagentoUrlFactory $urlFactory
    ) {
        $this->urlFinder = $urlFinder;
        $this->urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * Get abstract entity url
     *
     * @param int $aeId
     * @param int $storeId
     * @return string
     */
    public function getAbstractEntityUrl($aeId, $storeId)
    {
        $rewrite = $this->urlFinder->findOneByData([
            'entity_type' => UrlProcessor::URL_ENTITY_TYPE,
            'entity_id'   => $aeId,
            'store_id'   => $storeId
        ]);

        if ($rewrite) {
            return $this->getUrlInstance()->getUrl($rewrite->getRequestPath());
        }

        return $this->getUrlInstance()->getUrl(UrlProcessor::ROUTE_PATH, ['id' => $aeId]);
    }

    /**
     * Retrieve URL Instance
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }
}
