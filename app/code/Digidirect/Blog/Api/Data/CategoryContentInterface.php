<?php

namespace Digidirect\Blog\Api\Data;

/**
 * Information table methods
 */
interface CategoryContentInterface extends StoreViewSpecificInterface
{

    const CATEGORY_ID = 'information_category_id';
    const STORE_ID = 'information_store_id';

    /**
     * @return string
     */
    public function getMetaTitle(): string;

    /**
     * @return string
     */
    public function getMetaKeywords(): string;

    /**
     * @return string
     */
    public function getMetaDescription(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getStatus(): int;
}
