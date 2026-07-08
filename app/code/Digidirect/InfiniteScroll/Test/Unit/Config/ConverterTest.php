<?php

namespace Digidirect\InfiniteScroll\Test\Unit\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Digidirect\InfiniteScroll\Model\Config\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \DOMDocument
     */
    protected $source;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->filePath = __DIR__ . '/_files/';
        $this->source = new \DOMDocument();
        $this->model = $this->objectManagerHelper->getObject(Converter::class);
    }

    public function testConvert()
    {
        $this->source->loadXML(file_get_contents($this->filePath . 'infinitescroll_config.xml'));
        $convertedFile = include $this->filePath . 'infinitescroll_config.php';
        $this->assertEquals($convertedFile, $this->model->convert($this->source));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Attribute handle is missed
     */
    public function testConvertThrowsExceptionWhenDomIsInvalid()
    {
        $this->source->loadXML(file_get_contents($this->filePath . 'infinitescroll_invalid_config.xml'));
        $this->model->convert($this->source);
    }
}
