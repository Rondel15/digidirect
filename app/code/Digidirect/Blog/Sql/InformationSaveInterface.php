<?php

namespace Digidirect\Blog\Sql;

interface InformationSaveInterface
{
    /**
     * @param \Digidirect\Blog\Model\Post $entity
     * @return mixed
     */
    public function saveInformation($entity);
}
