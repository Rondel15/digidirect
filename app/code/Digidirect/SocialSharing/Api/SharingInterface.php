<?php

namespace Digidirect\SocialSharing\Api;

/**
 * Interface SharingInterface
 * @package Digidirect\SocialSharing\Api
 */
interface SharingInterface
{
    /**
     * @return string
     */
    public function getSharingUrl();

    /**
     * @return bool
     */
    public function validateEntity();
}
