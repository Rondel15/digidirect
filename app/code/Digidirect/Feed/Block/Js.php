<?php

namespace Digidirect\Feed\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\Template;

class Js extends Template
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * {@inheritdoc}
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function _toHtml()
    {
        $baseUrl = $this->urlBuilder->getUrl('digidirect_feed/report/click');

        $initObject = [
            'feedReport' => [
                'url' => $baseUrl
            ]
        ];

        return '<div data-mage-init=\'' . json_encode($initObject) . '\'></div>';
    }
}
