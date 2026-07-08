<?php
namespace Digidirect\Feed\Test\Unit\Export\Liquid\Tag;

class TagForTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\Feed\Export\Liquid\Tag\TagFor
     */
    protected $_tagFor;

    /**
     * @var \Digidirect\Feed\Export\Liquid\Template
     */
    protected $_template;

    /**
     * @var \Digidirect\Feed\Export\Liquid\Context
     */
    protected $_contextMock;

    /**
     * Set up the test
     */
    protected function setUp()
    {
        $tokens = 'feed_tokens';
        $this->_template = new \Digidirect\Feed\Export\Liquid\Template;
        $this->_tagFor = new \Digidirect\Feed\Export\Liquid\Tag\TagFor('product in context.products', $tokens);
        $this->_contextMock = $this->getMockBuilder('Digidirect\Feed\Export\Liquid\Context')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test feed length
     */
    public function testExecute()
    {
        $feedLength = 100;
        $index = new \ReflectionProperty('Digidirect\Feed\Export\Liquid\Tag\TagFor', 'index');
        $index->setAccessible(true);
        $index->setValue($this->_tagFor, 0);

        $length = new \ReflectionProperty('Digidirect\Feed\Export\Liquid\Tag\TagFor', 'length');
        $length->setAccessible(true);
        $length->setValue($this->_tagFor, $feedLength);

        $this->_tagFor->execute($this->_contextMock);

        $this->assertEquals($feedLength, $index->getValue($this->_tagFor));
        $this->assertEquals($feedLength, $length->getValue($this->_tagFor));
    }
}
