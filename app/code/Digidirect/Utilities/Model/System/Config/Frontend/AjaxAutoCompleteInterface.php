<?php
namespace Digidirect\Utilities\Model\System\Config\Frontend;

/**
 * Interface AjaxAutoCompleteInterface
 * @package Digidirect\Utilities\Model\System\Config\Frontend
 */
interface AjaxAutoCompleteInterface
{
    /**
     * @return string
     */
    public function getAjaxUrl();

    /**
     * @return mixed
     */
    public function getCurrentId();

    /**
     * @param mixed $id
     * @return mixed
     */
    public function getNameById($id);
}
