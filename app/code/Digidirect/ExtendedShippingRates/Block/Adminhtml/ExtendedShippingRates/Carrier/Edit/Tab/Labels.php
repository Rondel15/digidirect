<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Carrier\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\EditTabLabels;
use Digidirect\ExtendedShippingRates\Model\Carrier;
use Digidirect\ExtendedShippingRates\Model\CarrierFactory;
use Digidirect\ExtendedShippingRates\Ui\DataProvider\Carrier\Form\Modifier\AbstractModifier as CarrierModifier;
use Digidirect\ExtendedShippingRates\Api\CarrierRepositoryInterface;

/**
 * Class Labels
 */
class Labels extends EditTabLabels
{
    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var CarrierRepositoryInterface
     */
    protected $carrierRepository;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CarrierFactory $carrierFactory
     * @param CarrierRepositoryInterface $carrierRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CarrierFactory $carrierFactory,
        CarrierRepositoryInterface $carrierRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->carrierFactory = $carrierFactory;
        $this->dataFormPart = CarrierModifier::FORM_NAME;
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Carrier $carrier */
        $carrier = $this->_coreRegistry->registry(Carrier::CURRENT_CARRIER);
        if (!$carrier) {
            $id = $this->getRequest()->getParam('id');
            $carrier = $this->carrierRepository->getById($id);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('carrier_');

        if (!$this->_storeManager->isSingleStoreMode()) {
            $labels = $carrier->getStoreLabels();
            $this->_createStoreSpecificFieldset($form, $labels);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
