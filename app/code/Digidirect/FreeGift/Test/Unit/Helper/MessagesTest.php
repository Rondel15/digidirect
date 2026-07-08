<?php

namespace Digidirect\FreeGift\Test\Unit\Helper;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * Messages helper
     *
     * @var \Digidirect\FreeGift\Helper\Messages
     */
    protected $_messagesHelper;

    /**
     * Config helper
     *
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_configHelper;

    /**
     * Request mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * Messages manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_configHelper = $this->getMockBuilder('Digidirect\FreeGift\Helper\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_messagesHelper = $this->_objectManager->getObject('Digidirect\FreeGift\Helper\Messages', [
            '_messageManager' => $this->_messageManager,
            '_configHelper' => $this->_configHelper,
        ]);

        $this->_request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $requestProperty = new \ReflectionProperty('Digidirect\FreeGift\Helper\Messages', '_request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_messagesHelper, $this->_request);
    }

    /**
     * Test case show error message when disabled
     *
     * @return void
     */
    public function testShowErrorMessageWhenDisabled()
    {
        $this->_configHelper->expects($this->any())
            ->method('isDisplayErrorMessages')
            ->willReturn(false);

        $this->assertFalse($this->_messagesHelper->showMessage('Error', 1));
    }

    /**
     * Test case show success message when disabled
     *
     * @return void
     */
    public function testShowSuccessMessageWhenDisabled()
    {
        $this->_configHelper->expects($this->any())
            ->method('isDisplaySuccessMessages')
            ->willReturn(false);

        $this->assertFalse($this->_messagesHelper->showMessage('Success', 0));
    }

    /**
     * Test case for showing duplicate message
     *
     * @return void
     */
    public function testShowDuplicateMessage()
    {
        $this->_configHelper->expects($this->any())
            ->method('isDisplaySuccessMessages')
            ->willReturn(true);

        $this->_messageManager->expects($this->any())
            ->method('getMessages')
            ->willReturn([
                new \Magento\Framework\DataObject(['text' => 'Success Message']),
                new \Magento\Framework\DataObject(['text' => 'Error Message']),
            ]);

        $this->assertFalse($this->_messagesHelper->showMessage('Success Message', 0));
    }

    /**
     * Test case for show debug error message
     *
     * @return void
     */
    public function testShowErrorMessageDebug()
    {
        $this->_configHelper->expects($this->any())
            ->method('isDisplayErrorMessages')
            ->willReturn(true);

        $this->_messageManager->expects($this->any())
            ->method('getMessages')
            ->willReturn([]);

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('debug')
            ->willReturn(true);

        $this->_messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Error Message');

        $this->assertTrue($this->_messagesHelper->showMessage('Error Message', 1));
    }

    /**
     * Test case for show success message
     *
     * @return void
     */
    public function testShowSuccessMessage()
    {
        $this->_configHelper->expects($this->any())
            ->method('isDisplaySuccessMessages')
            ->willReturn(true);

        $this->_messageManager->expects($this->any())
            ->method('getMessages')
            ->willReturn([]);

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('debug')
            ->willReturn(false);

        $this->_messageManager->expects($this->never())
            ->method('addErrorMessage');

        $this->assertTrue($this->_messagesHelper->showMessage('Success Message', 0));
    }
}
