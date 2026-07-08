<?php
namespace Digidirect\AbstractEntity\Ui\Component\Form;

use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity;

class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return AbstractEntity::ID_FIELD_NAME;
    }
}
