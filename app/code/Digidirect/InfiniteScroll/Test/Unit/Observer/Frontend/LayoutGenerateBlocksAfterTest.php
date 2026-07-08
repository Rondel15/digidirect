<?php
namespace Digidirect\InfiniteScroll\Test\Observer\Frontend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LayoutGenerateBlocksAfterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Digidirect\InfiniteScroll\Observer\Frontend\LayoutGenerateBlocksAfter
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $_layout;

    /**
     * @var \Digidirect\InfiniteScroll\Model\Handler
     */
    protected $_handler;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_handler = $this->getMock(
            'Digidirect\InfiniteScroll\Model\Handler',
            ['handleRequest'],
            [],
            '',
            false
        );

        $this->_layout = $this->getMock(
            'Magento\Framework\View\Layout',
            [],
            [],
            '',
            false
        );

        $this->_observer = (new ObjectManager($this))->getObject(
            'Digidirect\InfiniteScroll\Observer\Frontend\LayoutGenerateBlocksAfter',
            [
                'handler' => $this->_handler
            ]
        );
    }

    /**
     * @return void
     */
    public function testHandleRequest()
    {
        $this->_handler->expects($this->once())->method('handleRequest')->willReturn(false);
        $this->_observer->execute(
            new \Magento\Framework\Event\Observer([
                'full_action_name' => 'module_controller_action',
                'layout' => $this->_layout
            ])
        );
    }
}
