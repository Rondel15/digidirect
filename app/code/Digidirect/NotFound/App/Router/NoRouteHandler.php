<?php
namespace Digidirect\NotFound\App\Router;

class NoRouteHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    public function process(\Magento\Framework\App\RequestInterface $request)
    {
        if(empty($request->getPathInfo()))
        {
            return false;
        }
        $requestValue = ltrim($request->getPathInfo(), '/');
        $request->setParam('q', $requestValue);
        $request->setModuleName('catalogsearch')->setControllerName('result')->setActionName('index');
        return true;
    }
}