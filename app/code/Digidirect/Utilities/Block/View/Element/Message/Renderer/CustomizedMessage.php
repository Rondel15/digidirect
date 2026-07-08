<?php
namespace Digidirect\Utilities\Block\View\Element\Message\Renderer;

use Magento\Framework\Message\AbstractMessage;

/**
 * Class CustomizedMessage
 * @package Digidirect\Utilities\Block\View\Element\Message\Renderer
 */
class CustomizedMessage extends AbstractMessage
{
    const TYPE = 'customized_message';

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
    }
}
