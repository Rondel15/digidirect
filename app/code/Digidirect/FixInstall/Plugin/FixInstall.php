<?php

declare(strict_types=1);

namespace Digidirect\FixInstall\Plugin;

use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\View\SubscriptionInterface;

class FixInstall
{
    public function __construct(
        private \Magento\Framework\Mview\Config $mviewConfig
    ) {
    }

    public function aroundRemove(Subscription $subject, callable $proceed): SubscriptionInterface
    {
        if ($this->isShouldTerminateOperation($subject)) {
            return $subject;
        }

        return $proceed();
    }

    public function aroundCreate(Subscription $subject, callable $proceed, bool $save = true): SubscriptionInterface
    {
        if ($this->isShouldTerminateOperation($subject)) {
            return $subject;
        }

        return $proceed($save);
    }

    private function isShouldTerminateOperation(Subscription $subscription): bool
    {
        $subscriptionData = $this->mviewConfig->getView($subscription->getView()->getId());

        return empty($subscriptionData['subscriptions']);
    }
}