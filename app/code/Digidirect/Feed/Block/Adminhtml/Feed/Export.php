<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Helper\Data as FeedHelper;

class Export extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FeedHelper
     */
    protected $dataHelper;

    /**
     * Export constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedHelper $dataHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        $exportUrl = $this->dataHelper->getFeedExportUrl($this->getFeed());
        $progressUrl = $this->dataHelper->getFeedProgressUrl();

        return [
            "*" => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'feed_export' => [
                            'component' => 'Digidirect_Feed/js/feed/export',
                            'config' => [
                                'exportUrl' => $exportUrl,
                                'id' => $this->getFeed()->getId(),
                            ],

                            'children' => [
                                'progress' => [
                                    'component' => 'Digidirect_Feed/js/feed/progress',
                                    'config' => [
                                        'url' => $progressUrl,
                                        'id' => $this->getFeed()->getId(),
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return \Digidirect\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->registry->registry('current_model');
    }
}
