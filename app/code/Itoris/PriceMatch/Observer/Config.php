<?php

namespace Itoris\PriceMatch\Observer;
use  Itoris\PriceMatch\Helper\Data as Helper;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{
    private $_request;
    private $_resource;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->_request = $request;
        $this->_resource = $resource;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $website = $observer->getEvent()->getWebsite();

        if($store){
            $scope = 'stores';
            $scopeId = $store;
        }elseif($website){
            $scope = 'websites';
            $scopeId = $website;
        }else{
            $scope = 'default';
            $scopeId = 0;
        }

        $fields = $this->_request->getParam("groups")["general"]["fields"];
        if(!isset($fields["group_list"])){
            $path = Helper::CONFIG_GROUP_LIST;
            $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $table  = $this->_resource->getTableName('core_config_data');
            $connection->delete($table,"path = '{$path}' AND scope_id = {$scopeId} AND scope = '{$scope}'");
            $connection->query("INSERT INTO {$table} (`scope`, `scope_id`, `path`, `value`) VALUES ('{$scope}',{$scopeId},'{$path}','-1')");
        }
    }
}