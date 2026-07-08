<?php

namespace Digidirect\Feed\Model\ResourceModel\Dynamic;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as FrameworkAbstractDb;
use Magento\Framework\Stdlib\DateTime;

abstract class AbstractDb extends FrameworkAbstractDb
{
    /**
     * @var array
     */
    protected $_uniqueFields = [
        [
            'field' => 'code',
            'title' => 'The same code',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /**
         * @var $object \Digidirect\Feed\Model\Dynamic\AbstractModel
         */
        $validateParams = ['max' => $object::CODE_MAX_LENGTH];
        if (!\Zend_Validate::is($object->getData('code'), 'StringLength', $validateParams)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'An entity code must not be more than %1 characters.',
                    \Digidirect\Feed\Model\Dynamic\Attribute::CODE_MAX_LENGTH
                )
            );
        }

        return parent::_beforeSave($object);
    }
}
