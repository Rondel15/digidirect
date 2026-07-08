<?php
namespace Digidirect\AbstractEntity\Ui\Component\Control;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Ui\Component\Control\Action;

class MassAction extends Action
{
    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $params = $config['params'] ?? [];
        $params[AbstractEntityInterface::ATTRIBUTE_SET_ID] =
            $this->getContext()->getRequestParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);

        $config['url'] = $this->getContext()->getUrl($config['action'], $params);
        $this->setData('config', (array)$config);
        parent::prepare();
    }
}
