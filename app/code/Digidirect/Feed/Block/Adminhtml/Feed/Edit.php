<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use \Digidirect\Feed\Block\Adminhtml\AbstractEdit as EditContainer;
use Digidirect\Feed\Helper\Data as FeedHelper;

class Edit extends EditContainer
{
    /**
     * @var string
     */
    protected $_objectId = 'feed_id';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_feed';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FeedHelper
     */
    protected $dataHelper;

    /**
     * Edit constructor.
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
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_removeSaveButton();

        if ($this->getModel()->getId() > 0) {
            $previewUrl = $this->dataHelper->getFeedPreviewUrl($this->getModel());

            if ($this->getModel()->getFtp()) {
                $deliveryUrl = $this->dataHelper->getFeedDeliverUrl($this->getModel());
                $this->buttonList->add('delivery', [
                    'label' => __('Delivery Feed'),
                    'class' => 'delivery',
                    'onclick' => 'setLocation(\'' . $deliveryUrl . '\')'
                ], -100);
            }

            $this->buttonList->add('preview', [
                'label' => __('Preview Feed'),
                'class' => 'preview',
                'data_attribute' => [
                    'mage-init' => [
                        'feedPreview' => [
                            'url' => $previewUrl
                        ],
                    ]
                ]
            ], -100);

            $this->_addSaveSplitButton();

            if ($this->getModel()->getData(\Digidirect\Feed\Api\Data\FeedInterface::FILENAME)) {
                $this->buttonList->add('Generate', [
                    'label' => __('Generate'),
                    'class' => 'generate',
                    'onclick' => "require('uiRegistry').get('feed_export').generate()",
                ], 100);
            }
        }
    }

    /**
     * Return feed model
     *
     * @return \Digidirect\Feed\Model\Feed
     */
    public function getModel()
    {
        return $this->registry->registry('current_model');
    }
}
