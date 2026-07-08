<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

class Tabs extends WidgetTabs
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Tabs constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Feed Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        if ($this->getFeed()->getId() > 0) {
            $this->addTab('general_section', [
                'label' => __('Feed Information'),
                'title' => __('Feed Information'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\General')->toHtml(),
            ]);

            if ($this->getFeed()->isCsv()) {
                $this->addTab('csv_section', [
                    'label' => __('Content Settings'),
                    'title' => __('Content Settings'),
                    'content' => $this->getLayout()
                        ->createBlock('\Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Csv')->toHtml(),
                ]);
            }

            if ($this->getFeed()->isXml() && $this->getFeed()->getType()) {
                $this->addTab('xml_section', [
                    'label' => __('Content Settings'),
                    'title' => __('Content Settings'),
                    'content' => $this->getLayout()
                        ->createBlock('\Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Xml')->toHtml(),
                ]);
            }

            $this->addTab('filter_section', [
                'label' => __('Filters'),
                'title' => __('Filters'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\Rule')->toHtml(),
            ]);

            $this->addTab('ga_section', [
                'label' => __('Google Analytics'),
                'title' => __('Google Analytics'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\Ga')->toHtml(),
            ]);

            $this->addTab('cron_section', [
                'label' => __('Scheduled Task'),
                'title' => __('Scheduled Task'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\Cron')->toHtml(),
            ]);

            $this->addTab('ftp_section', [
                'label' => __('FTP Settings'),
                'title' => __('FTP Settings'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\Ftp')->toHtml(),
            ]);

            $this->addTab('additional_section', [
                'label' => __('Additional'),
                'title' => __('Additional'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\Additional')->toHtml(),
            ]);

            $this->addTab('history_section', [
                'label' => __('History'),
                'title' => __('History'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\History')->toHtml(),
            ]);
        } else {
            $this->addTab('general_section', [
                'label' => __('Settings'),
                'title' => __('Settings'),
                'content' => $this->getLayout()
                    ->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\NewTab')->toHtml(),
            ]);
        }

        return parent::_beforeToHtml();
    }

    /**
     * Current Feed Model
     *
     * @return \Digidirect\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->registry->registry('current_model');
    }
}
