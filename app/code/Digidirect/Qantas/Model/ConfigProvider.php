<?php

/**
 * Created By : Rohan Hapani
 */

namespace Digidirect\Qantas\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface {

    public function getConfig() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $items = $cart->getQuote()->getAllItems();

        $qffPoints = [];
        $config = [];
        foreach ($items as $item) {

            $qty = $item->getQty();
            $price = $item->getPrice();
            $total = $price * $qty;
            $totals = str_replace('"', "", $total);
            $qffBase = $item->getProduct()->getQffBase();
            $qffBonusPoints = $item->getProduct()->getQffBonusPoints();

            if ($qffBase !== NULL && $qffBonusPoints !== NULL) {
                $sumOfPoints = $qffBase + $qffBonusPoints;
                $totalPoints = $totals * $sumOfPoints;
                $qffPoints[] = $totalPoints;

            } else {
                if ($qffBase != NULL) {
                    $totalPoints = $qffBase * $total;
                    $qffPoints[] = $totalPoints;
                } else {
                    $totalPoints = $totals * 1;
                    $qffPoints[] = $totalPoints;
                }
            }
            $qffTotalPoints = array_sum($qffPoints);
        }

        $config['qantas_total_points'] = number_format($qffTotalPoints);
        $config['qantas_bonus_points'] = $qffBonusPoints;
        $config['qantas_base_points'] = $qffBase;
        return $config;
    }
}
