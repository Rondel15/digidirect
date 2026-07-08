<?php
namespace Digidirect\Localization\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
    /**
     * Australian ISO code
     */
    const AU_ISO = 'AU';

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Australian states
     * @var array
     */
    protected $_australianRegions = [
        'ACT'   => 'Australian Capital Territory',
        'NSW'   => 'New South Wales',
        'NT'    => 'Northern Territory',
        'QLD'   => 'Queensland',
        'SA'    => 'South Australia',
        'TAS'   => 'Tasmania',
        'VIC'   => 'Victoria',
        'WA'    => 'Western Australia'
    ];

    /**
     * InstallData constructor.
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->_installAustralianRegions();
    }

    /**
     * Install Australians states
     * @return void
     */
    protected function _installAustralianRegions()
    {
        $australia = $this->_countryFactory->create()->loadByCode(self::AU_ISO);
        $regions = $australia->getRegions();
        if ($regions->getSize() == 0) {
            foreach ($this->_australianRegions as $code => $region) {
                $_region = $this->_regionFactory->create();
                $_region->setData([
                    'country_id' => $australia->getCountryId(),
                    'code' => $code,
                    'default_name' => $region,
                    'name' => $region
                ]);
                $_region->save();
            }
        }
    }
}
