<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

/**
 * @since 4.0.0
 */
interface TemplateInterface
{
    /**
     * @return string
     */
    public function getHtml(): string;

    /**
     * @param string $html
     * @return \Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface
     */
    public function setHtml(string $html): TemplateInterface;

    /**
     * @return string
     */
    public function getCss(): string;

    /**
     * @param string $css
     * @return \Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface
     */
    public function setCss(string $css): TemplateInterface;
}
