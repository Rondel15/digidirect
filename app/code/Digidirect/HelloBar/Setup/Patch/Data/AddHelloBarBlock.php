<?php
namespace Digidirect\HelloBar\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddHelloBarBlock implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $blockData = [
            'title' => 'Hello Bar',
            'identifier' => 'hello-bar',
            'content' => '<div class="hellobar-slider">
  <div class="hellobar-slides">
     <div class="hellobar-slide">For a limited time, shop <b>tax-free</b> on selected products in certain states -- online and in-store. <a href="#">Learn More ></a></div>
     <div class="hellobar-slide">Free shipping on orders over $99!</div>
     <div class="hellobar-slide">Shop tax-free on select items — limited time only!</div>
     <div class="hellobar-slide">Join digiClub for exclusive deals and early access.</div>
  </div>
</div>',
            'is_active' => 1,
            'stores' => [0]
        ];

        $this->blockFactory->create()->setData($blockData)->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return null;
    }

}
