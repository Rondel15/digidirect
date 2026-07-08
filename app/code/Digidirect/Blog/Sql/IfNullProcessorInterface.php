<?php

namespace Digidirect\Blog\Sql;

interface IfNullProcessorInterface
{
    /**
     * @param mixed $column
     * @param mixed $value
     * @return mixed
     */
    public function processIfNull($column, $value);

    /**
     * @return string
     */
    public function getStoreViewSpecificTable(): string;

    /**
     * @return string
     */
    public function getDefaultContentPrefix(): string;
}
