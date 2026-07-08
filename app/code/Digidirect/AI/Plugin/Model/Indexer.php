<?php
namespace Digidirect\AI\Plugin\Model;

use Digidirect\AI\Plugin\Model\Engine\Processor\Processor;
use Magento\Indexer\Model\Indexer as Subject;

/**
 * Class Indexer
 *
 * @package Digidirect\AI\Plugin\Controller\Checkout\Index
 */
class Indexer
{
    /**
     * Check if indexer is locked then block invalidate
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @return void
     */
    public function aroundInvalidate(Subject $subject, \Closure $proceed)
    {
        if ($subject->getState()->getStatus() !== Processor::INDEXER_STATUS_LOCKED) {
            $proceed();
        }
        return;
    }

    /**
     * Check if indexer is locked then block reindex row
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @param int $id
     * @return void
     */
    public function aroundReindexRow(Subject $subject, \Closure $proceed, $id)
    {
        if ($subject->getState()->getStatus() !== Processor::INDEXER_STATUS_LOCKED) {
            $proceed($id);
        }
        return;
    }

    /**
     * Check if indexer is locked then block reindex rows
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @param [] $ids
     * @return void
     */
    public function aroundReindexList(Subject $subject, \Closure $proceed, $ids)
    {
        if ($subject->getState()->getStatus() !== Processor::INDEXER_STATUS_LOCKED) {
            $proceed($ids);
        }
        return;
    }
}
