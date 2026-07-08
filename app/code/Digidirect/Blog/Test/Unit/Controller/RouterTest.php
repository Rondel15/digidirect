<?php
namespace Digidirect\Blog\Test\Unit\Controller;

use Digidirect\Utilities\Test\Unit\Library;
use Magento\Framework\DataObject;

/**
 * Class RouterTest
 */
class RouterTest extends Library
{
    /**
     * Test blog router
     */
    public function testMatch()
    {
        $url = 'blogpost/post-first';
        $request = $this->getMockObjectWithoutConstructor('Magento\Framework\App\Request\Http');
        $request->method('getPathInfo')->willReturn($url);
        $request->method('setModuleName')->willReturnSelf();
        $request->method('setControllerName')->willReturnSelf();
        $request->method('setActionName')->willReturnSelf();
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $actionFactory = $this->getMock('Magento\Framework\App\ActionFactory', [], [], '', false);
        $actionFactory->expects($this->once())->method('create')->with(
            'Magento\Framework\App\Action\Forward'
        )->will(
            $this->returnValue(
                $this->getMockForAbstractClass('Magento\Framework\App\Action\AbstractAction', [], '', false)
            )
        );

        $dataHelperMock = $this->getMockObjectWithoutConstructor(
            'Digidirect\Blog\Helper\Data',
            ['isModuleEnabled', 'getGeneralSettingsConfig']
        );
        $dataHelperMock->method('isModuleEnabled')->willReturn(true);
        $dataHelperMock->method('getGeneralSettingsConfig')->willReturn('');
        $eventManagerMock = $this->getMockObjectWithoutConstructor('Magento\Framework\Event\Manager');
        $storeManagerMock = $this->getMockObjectWithoutConstructor('Magento\Store\Model\StoreManager', ['getStore']);
        $responseMock = $this->getMockObjectWithoutConstructor('Magento\Framework\App\Response');
        $categoryRepositoryMock = $this->getMockObjectWithoutConstructor('Digidirect\Blog\Model\CategoryRepository');
        $postRepositoryMock = $this->getMockObjectWithoutConstructor(
            'Digidirect\Blog\Model\PostRepository',
            ['getPostIdByUrlKey']
        );
        $postRepositoryMock->method('getPostIdByUrlKey')->willReturn(1);
        $storeObject = new DataObject();
        $storeObject->setId(1);
        $storeManagerMock->method('getStore')->willReturn($storeObject);

        $router = $helper->getObject(
            'Digidirect\Blog\Controller\Router',
            [
                'actionFactory' => $actionFactory,
                'eventManager'  => $eventManagerMock,
                'storeManager'  => $storeManagerMock,
                'responseMock'  => $responseMock,
                'categoryRepository'  => $categoryRepositoryMock,
                'postRepository'  => $postRepositoryMock,
                'dataHelper'    => $dataHelperMock,
            ]
        );
        $this->assertInstanceOf('Magento\Framework\App\Action\AbstractAction', $router->match($request));
    }
}
