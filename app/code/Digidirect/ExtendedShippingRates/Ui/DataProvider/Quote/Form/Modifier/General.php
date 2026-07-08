<?php
namespace Digidirect\ExtendedShippingRates\Ui\DataProvider\Quote\Form\Modifier;

use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($arrayManager, $urlBuilder, $coreRegistry, $storeManager);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        // Add submit (save) url to the config
        $actionParameters = [];
        $submitUrl = $this->urlBuilder->getUrl(
            'digidirect_extendedshippingrates/extendedshippingrates_quote/save',
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
