<?php
namespace Digidirect\CheckoutFields\Controller\Adminhtml\Field;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue
     */
    protected $orderFieldValue;

    /**
     * @var \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser
     */
    protected $parser;

    /**
     * Save constructor.
     *
     * @param \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue $orderFieldValue
     * @param \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $parser
     * @param Action\Context $context
     */
    public function __construct(
        \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue $orderFieldValue,
        \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $parser,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderFieldValue = $orderFieldValue;
        $this->parser = $parser;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $fieldId = $this->getRequest()->getParam('field_id');
        $fieldValue = $this->getRequest()->getParam('field_value');

        if (!$orderId || !$fieldId) {
            throw new LocalizedException(__('Missed required params: order_id or field_id'));
        }

        $fields = $this->parser->getAllFields();
        $field = $fields[$fieldId];
        $data = [
            'code' => $field['frontend_name'],
            'order_id' => $orderId,
            'value' => serialize($fieldValue),
            'field_id' => $fieldId,
        ];
        $this->orderFieldValue->saveCustomCheckoutValuesToOrder($data);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
            'success' => true,
            'newValue' => $fieldValue,
        ]);
    }
}
