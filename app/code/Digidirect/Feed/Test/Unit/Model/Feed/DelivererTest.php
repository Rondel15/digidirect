<?php

namespace Digidirect\Feed\Test\Unit\Model;

class DelivererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\Feed\Model\Feed\Deliverer
     */
    protected $_delivererModel;

    /**
     * @var \Digidirect\Feed\Model\Feed
     */
    protected $_feedMock;

    /**
     * @var \ReflectionMethod
     */
    protected $_uploadFile;

    /**
     * Set up the test
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_delivererModel = $this->_objectManager->getObject('Digidirect\Feed\Model\Feed\Deliverer');
        $this->_uploadFile = new \ReflectionMethod('Digidirect\Feed\Model\Feed\Deliverer', 'uploadFile');
        $this->_uploadFile->setAccessible(true);
    }

    /**
     * Test SFTP constants
     */
    public function testSftpConstants()
    {
        $this->_feedMock = $this->getMockBuilder('Digidirect\Feed\Model\Feed')
            ->disableOriginalConstructor()
            ->setMethods(['getFtpProtocol'])
            ->getMock();

        $this->_feedMock->expects($this->any())
            ->method('getFtpProtocol')
            ->willReturn('sftp');

        $feedFilename = 'feed.xml';
        $this->_uploadFile->invoke($this->_delivererModel, $this->_feedMock, $feedFilename, $feedFilename);

        $this->assertEquals(true, defined('NET_SFTP_LOCAL_FILE'));
        $this->assertEquals(true, defined('NET_SFTP_STRING'));
    }
}
