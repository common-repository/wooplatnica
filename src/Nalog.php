<?php

use Intervention\Image\Image;

abstract class Nalog
{
    public string $uplatilac = '';
    public string $svrha = '';
    public string $primalac = '';
    public string $telefon = '';
    public string $sifra = '';
    public string $valuta = 'RSD';
    public string $iznos = '';
    public string $racun = '';
    public string $model = '';
    public string $poziv_na_broj = '';

    public function uplatilac(string $uplatilac): self
    {
        $this->uplatilac = $uplatilac;
        return $this;
    }

    public function getUplatilac(): array
    {
        $parts = preg_split("/(\r\n|\n|\r)/", $this->uplatilac);
        $ime   = array_shift($parts);
        return [
            'ime'    => $ime,
            'adresa' => $parts,
        ];
    }

    public function svrha(string $svrha): self
    {
        $this->svrha = $svrha;
        return $this;
    }

    public function primalac(string $primalac): self
    {
        $this->primalac = $primalac;
        return $this;
    }

    public function telefon(string $tel): self
    {
        $this->telefon = $tel;
        return $this;
    }

    public function getPrimalac(): array
    {
        $parts = preg_split("/(\r\n|\n|\r)/", $this->primalac);
        $ime   = array_shift($parts);
        return [
            'ime'    => $ime,
            'adresa' => $parts,
        ];
    }

    public function sifra(string $sifra): self
    {
        $this->sifra = $sifra;
        return $this;
    }

    public function valuta(string $valuta): self
    {
        $this->valuta = $valuta;
        return $this;
    }

    public function iznos(string $iznos): self
    {
        $this->iznos = $iznos;
        return $this;
    }

    public function formatirajIznos(int $decimale = 2, string $decimalSeparator = ',', string $hiljadeSeparator = '.'): string
    {
        return number_format((float)$this->iznos, $decimale, $decimalSeparator, $hiljadeSeparator);
    }

    public function racun(string $racun): self
    {
        $this->racun = $racun;
        return $this;
    }

    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function pozivNaBroj(string $poziv_na_broj): self
    {
        $this->poziv_na_broj = $poziv_na_broj;
        return $this;
    }

    public static function napravi(?Nalog $from = null): self
    {
        if (!$from)
            return new static();

        return (new static())
            ->uplatilac($from->uplatilac)
            ->primalac($from->primalac)
            ->svrha($from->svrha)
            ->sifra($from->sifra)
            ->valuta($from->valuta)
            ->iznos($from->iznos)
            ->racun($from->racun)
            ->model($from->model)
            ->pozivNaBroj($from->poziv_na_broj);
    }

    abstract public function generisi(): Image;

}