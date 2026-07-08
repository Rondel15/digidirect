<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Animation extends Base
{
    public function toOptionHash()
    {
        return [
            ''               => __('None'),
            'fadeIn'         => __('Fade In'),
            'fadeInDown'     => __('Fade In (Down)'),
            'fadeInLeft'     => __('Fade In (Left)'),
            'fadeInRight'    => __('Fade In (Right)'),
            'fadeInUp'       => __('Fade In (Up)'),
            'fadeInDownBig'  => __('Slide In (Down) - Default'),
            'fadeInLeftBig'  => __('Slide In (Left)'),
            'fadeInRightBig' => __('Slide In (Right)'),
            'fadeInUpBig'    => __('Slide In (Up)'),
            'zoomIn'         => __('Zoom In'),
            'flip3d_hor'     => __('3D Flip (horizontal)'),
        ];
    }
}
