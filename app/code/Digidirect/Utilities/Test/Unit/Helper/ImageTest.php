<?php
namespace Digidirect\Utilities\Test\Unit\Helper;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Digidirect\Utilities\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\View\Config
     */
    protected $viewConfig;

    /**
     * Set up the test
     */
    protected function setUp()
    {
        $this->viewConfig = $this->getMockBuilder('Magento\Framework\View\Config')
            ->setMethods([
                'getViewConfig',
                'getVarValue',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->imageHelper = $this->objectManager->getObject(
            'Digidirect\Utilities\Helper\Image',
            [
                'viewConfig' => $this->viewConfig
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function testGetImageQualityPositive()
    {
        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => 'frontend'])
            ->willReturnSelf();

        $this->viewConfig->expects($this->once())
            ->method('getVarValue')
            ->with('Digidirect_Utilities', 'images_quality')
            ->willReturn([
                'product_base_image' => 95
            ]);

        $this->assertEquals(95, $this->imageHelper->getImageQualityByType('product_base_image'));
    }

    /**
     * @inheritdoc
     */
    public function testGetImageQualityDefault()
    {
        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => 'frontend'])
            ->willReturnSelf();

        $this->viewConfig->expects($this->once())
            ->method('getVarValue')
            ->with('Digidirect_Utilities', 'images_quality')
            ->willReturn([
                'default' => 100
            ]);

        $this->assertEquals(100, $this->imageHelper->getImageQualityByType('product_base_image'));
    }

    /**
     * @inheritdoc
     */
    public function testGetImageQualityNoConfig()
    {
        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => 'frontend'])
            ->willReturnSelf();

        $this->viewConfig->expects($this->once())
            ->method('getVarValue')
            ->with('Digidirect_Utilities', 'images_quality')
            ->willReturn([]);

        $this->assertFalse($this->imageHelper->getImageQualityByType('product_base_image'));
    }
}
