<?php

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class UplatnicaPDF
{
    protected Uplatnica $uplatnica;
    protected $qr = false;
    protected $header;
    protected $footer;
    protected $ipsLogo;

    public function __construct(Uplatnica $uplatnica, $qrNatpis = false, $ipsLogo = true, $header = null, $footer = null)
    {
        $this->uplatnica = $uplatnica;
        $this->qr        = $qrNatpis;
        $this->header    = $header;
        $this->footer    = $footer;
        $this->ipsLogo   = $ipsLogo;
    }

    public function generisi(string $fileName): bool
    {
        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4-L',
            'fontDir'           => dirname(dirname(__FILE__)).'/assets',
            'default_font_size' => 15,
            'default_font'      => 'dejavusans',
        ]);


        if ($this->header) {
            $mpdf->setHeader($this->header);
        }

        $html = '<img src="'.$this->uplatnica->generisi()->encode('data-url').'" />';

        if ($this->qr !== false) {
            $qr   = IpsQrLocal::napravi($this->uplatnica);
            $html .= '<p>'.$this->qr.'</p>';
            $html .= '<img width=200 height=200 src="'.$qr->generisi()->encode('data-url').'" />';
            if ($this->ipsLogo) {
                $ipsLogo = base64_encode(file_get_contents(dirname(__FILE__).'/../assets/ips_logo.png'));
                $html    .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img width=67 height=45 src="data:image/png;base64,'.$ipsLogo.'" />';
            }
        }

        if (!empty($this->footer)) {
            $mpdf->setFooter(trim(str_replace('|', ' - ', $this->footer)));
        }

        $mpdf->WriteHTML($html);

        try {
            $this->napraviFolderZa($fileName);
        } catch (\Exception $e) {
            wp_die('Wooplatnica: '.$e->getMessage());
        }

        $mpdf->Output($fileName, Destination::FILE);
        return is_file($fileName);
    }

    protected function napraviFolderZa(string $filePath): bool
    {
        if (!is_dir(dirname($filePath))) {
            $dir = mkdir(dirname($filePath), 0755, true);

            if (!$dir) {
                throw new Exception(dirname($filePath).' could not be created! Check your folder permissions.');
            }
        }

        return true;
    }

    public static function napravi(Uplatnica $uplatnica, $qr = false, $ipsLogo = true, $header = null, $footer = null)
    {
        return new self($uplatnica, $qr, $ipsLogo, $header, $footer);
    }

}