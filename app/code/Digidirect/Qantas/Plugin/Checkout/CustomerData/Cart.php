<?php

namespace Digidirect\Qantas\Plugin\Checkout\CustomerData;

class Cart {

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, array $result) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $items = $cart->getQuote()->getAllItems();

        $qffPoints = [];

        $qffTotalPoints = 0;

        $qffTotalPoints = 0;

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


        $result['minicart_qantas_points'] = number_format($qffTotalPoints);

        return $result;
    }
    //for redeploy
}
