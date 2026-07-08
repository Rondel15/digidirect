<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Time implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];

        for ($i = 0; $i < 24; ++$i) {
            $hour = $i;
            $suffix = ' AM';
            if ($hour > 12) {
                $hour -= 12;
                $suffix = ' PM';
            }

            if ($hour < 10) {
                $hour = '0' . $hour;
            }

            $result[] = [
                'label' => $hour . ':00' . $suffix,
                'value' => $i * 60,
            ];
            $result[] = [
                'label' => $hour . ':30' . $suffix,
                'value' => $i * 60 + 30,
            ];
        }

        return $result;
    }
}
