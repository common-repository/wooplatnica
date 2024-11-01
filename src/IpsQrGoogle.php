<?php

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class IpsQrGoogle extends IpsQr
{
    public function generisi(): Image
    {
        return (new ImageManager)->make(wp_remote_get($this->ipsQrUrl())['body']);
    }

    protected function ipsQrUrl($size = 512): string
    {
        return "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chld=L|0&chl="
            .urlencode($this->ipsQrKod());
    }
}