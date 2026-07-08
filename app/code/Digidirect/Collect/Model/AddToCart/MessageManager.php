<?php

namespace Digidirect\Collect\Model\AddToCart;

use Magento\Framework\Message\Manager;

/**
 * Class MessageManager
 * @package Digidirect\Collect\Model\AddToCart
 */
class MessageManager extends Manager
{

    /**
     * To change the "alternative text" to "exception message" in an exception,
     * you need to throw follow collect exception with desirable message:
     * throw new \Digidirect\Collect\Model\AddToCart\CollectException($msg);
     *
     * @param \Exception $exception
     * @param null $alternativeText
     * @param null $group
     * @return Manager
     */
    public function addException(\Exception $exception, $alternativeText = null, $group = null)
    {
        if ($exception instanceof \Digidirect\Collect\Model\AddToCart\CollectException) {
            $alternativeText = $exception->getMessage();
        }

        return parent::addException($exception, $alternativeText, $group);
    }

    /**
     * To change the "alternative text" to "exception message" in an exception,
     * you need to throw follow collect exception with desirable message:
     * throw new \Digidirect\Collect\Model\AddToCart\CollectException($msg);
     *
     * @param \Exception $exception
     * @param null $alternativeText
     * @param null $group
     * @return Manager
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText = null, $group = null)
    {
        if ($exception instanceof \Digidirect\Collect\Model\AddToCart\CollectException) {
            $alternativeText = $exception->getMessage();
        }

        return parent::addExceptionMessage($exception, $alternativeText, $group);
    }
}
