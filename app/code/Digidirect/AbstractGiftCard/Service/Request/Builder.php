<?php

namespace Digidirect\AbstractGiftCard\Service\Request;

abstract class Builder
{
    /**
     * @return string
     */
    public function getMessageId()
    {
        return md5(microtime());
    }
}
