<?php

namespace Digidirect\Checkout\Plugin;

class DefaultItemPlugin
{

    public function afterGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        $result,
        \Magento\Quote\Model\Quote\Item $item
            )
    {
        $this->item = $item;
        $data['item_subtotal'] = '$'.number_format((float)(($item->getQty() * 1) * ($item->getCalculationPrice())), 2, '.', '');
        //$data['qantas_points_new'] = round(($item->getQty() * 1) * ($item->getCalculationPrice()));
        $data['qantas_points_new'] = $this->getQffPoints();
        
        return \array_merge(
            $result,
            $data
        );
    }
    
    public function getQffPoints() {

        $qty = $this->item->getQty() * 1;

        if ($this->item->getPrice() == 0) {

            $promotionPoints = 0;
        } else {

            $finalProductPrice = $this->item->getProduct()->getFinalPrice();

            if ($this->item->getProduct()->offsetExists("qff_bonus_points") && $this->item->getProduct()->offsetExists("qff_base")) {

                $qff_bonus_points = $this->item->getProduct()->getQffBonusPoints();

                $qff_base_points = $this->item->getProduct()->getQffBase();

                $pointsSum = $qff_bonus_points + $qff_base_points;

                $promotionPoints = number_format($pointsSum * ($qty * $finalProductPrice));
            } else {
                if ($this->item->getProduct()->offsetExists("qff_base")) {

                    $qff_base_points = $this->item->getProduct()->getQffBase();

                    $promotionPoints = number_format($qff_base_points * ($qty * $finalProductPrice));
                } else {

                    $promotionPoints = number_format(($qty * $finalProductPrice));
                }
            }
        }
        return $promotionPoints;
    }

}