<?php

namespace Digidirect\InvoiceIncrementId\Model;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class IncrementIdUpdater
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * IncremenIdUpdater constructor.
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param InvoiceInterface $invoice
     * @param string $incrementId
     * @return bool
     */
    public function update(InvoiceInterface $invoice, $incrementId)
    {
        if ($invoice->getId() && !empty($incrementId)) {
            $invoice->setIncrementId($incrementId);
            $this->invoiceRepository->save($invoice);
            return true;
        }
        return false;
    }
}
