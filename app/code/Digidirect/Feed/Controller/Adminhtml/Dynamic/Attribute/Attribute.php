<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Encoder as JsonEncoder;
use Digidirect\Feed\Helper\Output as OutputHelper;

class Attribute extends Action
{
    /**
     * @var JsonEncoder
     */
    protected $jsonEncoder;

    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * Attribute constructor.
     * @param Context $context
     * @param JsonEncoder $jsonEncoder
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        Context $context,
        JsonEncoder $jsonEncoder,
        OutputHelper $outputHelper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->outputHelper = $outputHelper;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $attribute = $this->getRequest()->getParam('attribute');

        $result = [
            'operators' => $this->outputHelper->getAttributeOperators($attribute),
            'attributeType' => 'select',
            'values' => $this->outputHelper->getAttributeValues($attribute),
        ];

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->representJson($this->jsonEncoder->encode($result));
    }

    public function _processUrlKeys()
    {
        return true;
    }
}
