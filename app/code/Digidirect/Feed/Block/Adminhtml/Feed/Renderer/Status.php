<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Digidirect\Feed\Model\Config;
use Digidirect\Feed\Model\Feed\Exporter;

/**
 * Status Grid Renderer
 */
class Status extends AbstractRenderer
{
    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * Status constructor.
     * @param Context $context
     * @param Exporter $exporter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Exporter $exporter,
        array $data = []
    ) {
        $this->exporter = $exporter;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $feed)
    {
        /** @var \Digidirect\Feed\Model\Feed $feed */
        $html = '';

        try {
            if ($feed->getIsActive()) {
                $handler = $this->exporter->getHandler($feed);
                if (in_array($handler->getStatus(), [Config::STATUS_COMPLETED, Config::STATUS_READY])) {
                    if ($feed->getUrl()) {
                        $html = $this->getStatusHtml('notice', 'Ready');
                    } else {
                        $html = $this->getStatusHtml('critical', 'Not generated');
                    }
                } elseif ($handler->getStatus() == Config::STATUS_PROCESSING) {
                    $html = $this->getStatusHtml('major', 'Processing', '');
                }
            } else {
                $html = $this->getStatusHtml('minor', 'Disabled');
            }
        } catch (\Exception $e) {
            $html = $this->getStatusHtml('critical', 'Error/Not completed');
        }

        return $html;
    }

    /**
     * Return status label (html)
     *
     * @param string $severity
     * @param string $title
     * @param string $message
     * @return string
     */
    protected function getStatusHtml($severity, $title, $message = null)
    {
        $html = '';
        $html .= sprintf('<span class="grid-severity-%s"><span>%s</span></span>', $severity, __($title));

        if ($message) {
            $html .= '<div class="state-message">' . $message . '</div>';
        }

        return $html;
    }
}
