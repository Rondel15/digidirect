<?php

namespace Digidirect\Feed\Model\Dynamic;

use Magento\Framework\Model\AbstractModel as FrameworkAbstractModel;

/**
 * @method string getName()
 * @method string getCode()
 */
abstract class AbstractModel extends FrameworkAbstractModel
{
    /**
     * code max length
     */
    const CODE_MAX_LENGTH = 32;
}
