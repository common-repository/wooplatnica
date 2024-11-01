<?php

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class Uplatnica extends Nalog
{
    private Image $image;

    public function generisi(): Image
    {
        $manager = new ImageManager();

        $this->image = $manager->make(dirname(__DIR__).'/assets/nalog-za-uplatu.jpg');

        $this->tekst($this->getUplatilac()['ime'].($this->telefon ? ' ('.$this->telefon.')' : ''), 30, 60)
            ->tekst($this->getUplatilac()['adresa'][0]??'', 30, 77)
            ->tekst($this->getUplatilac()['adresa'][1]??'', 30, 94)

            ->tekst($this->svrha??'', 30, 140)

            ->tekst($this->getPrimalac()['ime'], 30, 217)
            ->tekst($this->getPrimalac()['adresa'][0]??'', 30, 234)
            ->tekst($this->getPrimalac()['adresa'][1]??'', 30, 251)

            ->tekst($this->sifra, 430, 73)
            ->tekst($this->valuta, 500, 73)
            ->tekst('= '.$this->formatirajIznos(), 575, 73)

            ->tekst($this->racun, 430, 126)

            ->tekst($this->model, 430, 172)
            ->tekst($this->poziv_na_broj, 500, 172);

        return $this->image;
    }

    protected function tekst(string $tekst, int $x, int $y): self
    {
        $this->image->text($tekst, $x, $y, function($font) {
            $font->file(dirname(__DIR__).'/assets/DejaVuSans.ttf');
            $font->size(15);
            $font->color('#000000');
        });

        return $this;
    }
}