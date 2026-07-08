<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Method\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\EditTabLabels;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method;
use Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory;
use Digidirect\ExtendedShippingRates\Ui\DataProvider\Method\Form\Modifier\AbstractModifier as MethodModifier;
use Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface;

/**
 * Class Labels
 */
class Labels extends EditTabLabels
{
    /**
     * @var MethodFactory
     */
    protected $methodFactory;

    /**
     * @var MethodRepositoryInterface
     */
    protected $methodRepository;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param MethodFactory $methodFactory
     * @param MethodRepositoryInterface $methodRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        MethodFactory $methodFactory,
        MethodRepositoryInterface $methodRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->methodFactory = $methodFactory;
        $this->dataFormPart = MethodModifier::FORM_NAME;
        $this->methodRepository = $methodRepository;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Method $method */
        $method = $this->_coreRegistry->registry(Method::CURRENT_METHOD);
        if (!$method) {
            $id = $this->getRequest()->getParam('id');
            $method = $this->methodRepository->getById($id);
        }

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('method_');

        if (!$this->_storeManager->isSingleStoreMode()) {
            $labels = $method->getStoreLabels();
            $this->_createStoreSpecificFieldset($form, $labels);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
