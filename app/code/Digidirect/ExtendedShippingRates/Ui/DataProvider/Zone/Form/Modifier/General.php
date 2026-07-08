<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Zone\Form\Modifier;

use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method as MethodController;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory as CarrierCollectionFactory;
use Digidirect\ExtendedShippingRates\Model\ZoneFactory;

/**
 * Data provider for main panel
 */
class General extends AbstractModifier
{
    const KEY_SUBMIT_URL = 'submit_url';

    const GENERAL_FIELDSET_NAME = 'general';
    const FIELD_CARRIER_ID_NAME = 'carrier_id';

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var CarrierCollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param ZoneFactory $zoneFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CarrierCollectionFactory $carrierCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        ZoneFactory $zoneFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CarrierCollectionFactory $carrierCollectionFactory,
        RequestInterface $request
    ) {
        parent::__construct($arrayManager, $urlBuilder, $zoneFactory, $coreRegistry, $storeManager);
        $this->carrierCollectionFactory = $carrierCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        // Add submit (save) url to the config
        $actionParameters = [];
        $submitUrl = $this->urlBuilder->getUrl(
            'digidirect_extendedshippingrates/extendedshippingrates_zone/save',
            $actionParameters
        );
        $data = array_replace_recursive(
            $data,
            [
                'config' => [
                    self::KEY_SUBMIT_URL => $submitUrl,
                ]
            ]
        );

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->meta;
    }
}
