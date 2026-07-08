<?php
namespace Digidirect\AddressVerification\Setup;

use Digidirect\AddressVerification\Model\CountryAddress\Source\Provider\AttributeProvider;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute;
use Digidirect\AddressVerification\Model\ResourceModel\ImportReport;
use Digidirect\AddressVerification\Model\ResourceModel\Location;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Digidirect\AddressVerification\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * UpgradeData constructor.
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(\Magento\Framework\Json\EncoderInterface $jsonEncoder)
    {
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->context = $context;

        if ($this->compareVersions('1.0.2')) {
            $this->createAuNzAttributeSettings();
        }
    }

    /**
     * Add country address used attributes values for AU and NZ
     * @return $this
     */
    protected function createAuNzAttributeSettings()
    {
        $this->setup->getConnection()->insertMultiple(
            CountryAddressAttribute::MAIN_TABLE,
            [
                [
                    'country_code' => 'AU',
                    'attributes' => $this->jsonEncoder->encode(
                        [
                            AttributeProvider::ATTRIBUTE_POSTCODE,
                            AttributeProvider::ATTRIBUTE_REGION,
                            AttributeProvider::ATTRIBUTE_SUBURB
                        ]
                    ),
                ],
                [
                    'country_code' => 'NZ',
                    'attributes' => $this->jsonEncoder->encode(
                        [
                            AttributeProvider::ATTRIBUTE_POSTCODE,
                            AttributeProvider::ATTRIBUTE_SUBURB
                        ]
                    ),
                ]
            ]
        );
        $this->setup->getConnection()->update(Location::MAIN_TABLE, ['country_code' => 'AU']);
        $this->setup->getConnection()->update(ImportReport::MAIN_TABLE, ['country_code' => 'AU']);
        return $this;
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function compareVersions($version)
    {
        return version_compare($this->context->getVersion(), $version, '<');
    }
}
