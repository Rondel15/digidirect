<?php

namespace Digidirect\DigiClubMember\Observer;
   
use Magento\Framework\View\Page\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;


class CheckLogin implements ObserverInterface
{
    protected Config $config;
    
    protected $request;
    

    public function __construct(
        Config $config,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->config = $config;
        $this->request = $request;
    }

    public function execute(EventObserver $observer){
        $name = $observer->getFullActionName();
        $isDigiclubRedirect = $this->request->getParam('digiclub');
        if(($name == "customer_account_login") && ($isDigiclubRedirect)) {
            $this->config->addBodyClass("digiclub-redirect");
        }
    }
}