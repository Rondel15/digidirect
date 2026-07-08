<?php

namespace Digidirect\Utilities\Plugin\Magento\Framework\Image\Adapter;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Framework\Image\Adapter\Gd2;

/**
 * Class Gd2Plugin
 * @package Digidirect\Utilities\Plugin\Magento\Framework\Image\Adapter
 */
class Gd2Plugin
{
    /**
     * @var WysiwygAllowedTypeSettings
     */
    protected $settings;

    /**
     * Gd2Plugin constructor.
     * @param WysiwygAllowedTypeSettings $helperSettings
     */
    public function __construct(WysiwygAllowedTypeSettings $helperSettings)
    {
        $this->settings = $helperSettings;
    }

    /**
     * @param Gd2      $subject
     * @param \Closure $proceed
     * @param string   $filename
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     */
    public function aroundOpen(Gd2 $subject, \Closure $proceed, $filename)
    {
        $pathInfo = pathinfo($filename);
        if (!$this->settings->isAdditionalFiletype($pathInfo)) {
            return $proceed($filename);
        }

        return;
    }

    /**
     * @param Gd2         $subject
     * @param \Closure    $proceed
     * @param null|string $destination
     * @param null|string $newName
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     */
    public function aroundSave(Gd2 $subject, \Closure $proceed, $destination = null, $newName = null)
    {
        $pathInfo = pathinfo($destination);
        if (!$this->settings->isAdditionalFiletype($pathInfo)) {
            return $proceed($destination, $newName);
        }

        return;
    }
}
