<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

use Magento\Framework\Module\Manager as ModuleManager;
use Plumrocket\Base\Model\OptionSource\AbstractSource;

/**
 * @since 4.1.3
 */
class ReCaptcha extends AbstractSource
{
    public const DEFAULT_CONFIG = 'default';
    public const CUSTOM_CONFIG = 'custom';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        ModuleManager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get ReCaptcha options.
     *
     * @return array
     */
    public function toOptionHash(): array
    {
        $options = [self::CUSTOM_CONFIG   => __('Custom Configuration')];

        if ($this->moduleManager->isEnabled('Magento_ReCaptchaUi')) {
            $options[self::DEFAULT_CONFIG] = __('Default Magento Configuration');
        }

        return $options;
    }
}
