<?php

abstract class IpsQr extends Nalog
{
    protected $azbuka = array(
        "а" => "a",
        "б" => "b",
        "в" => "v",
        "г" => "g",
        "д" => "d",
        "ђ" => "đ",
        "е" => "e",
        "ж" => "ž",
        "з" => "z",
        "и" => "i",
        "ј" => "j",
        "к" => "k",
        "л" => "l",
        "љ" => "lj",
        "м" => "m",
        "н" => "n",
        "њ" => "nj",
        "о" => "o",
        "п" => "p",
        "р" => "r",
        "с" => "s",
        "т" => "t",
        "ћ" => "ć",
        "у" => "u",
        "ф" => "f",
        "х" => "h",
        "ц" => "c",
        "ч" => "č",
        "џ" => "dž",
        "ш" => "š",
        "А" => "A",
        "Б" => "B",
        "В" => "V",
        "Г" => "G",
        "Д" => "D",
        "Ђ" => "Đ",
        "Е" => "E",
        "Ж" => "Ž",
        "З" => "Z",
        "И" => "I",
        "Ј" => "J",
        "К" => "K",
        "Л" => "L",
        "Љ" => "LJ",
        "М" => "M",
        "Н" => "N",
        "Њ" => "NJ",
        "О" => "O",
        "П" => "P",
        "Р" => "R",
        "С" => "S",
        "Т" => "T",
        "Ћ" => "Ć",
        "У" => "U",
        "Ф" => "F",
        "Х" => "H",
        "Ц" => "C",
        "Ч" => "Č",
        "Џ" => "DŽ",
        "Ш" => "Š",
    );

    public function ipsQrKod(): string
    {
        $racun   = preg_replace('/[^0-9]/', '', $this->racun);
        $prva3   = substr($racun, 0, 3);
        $ostatak = substr($racun, 3);

        if (strlen($ostatak) < 15) {
            $ostatak = str_pad($ostatak, 15, '0', STR_PAD_LEFT);
        }

        $racun    = $prva3.$ostatak;
        $primalac = $this->getPrimalac()['ime'];

        if ($adresa = $this->getPrimalac()['adresa'][0] ?? false) {
            $primalac .= "\n".$adresa;
        }

        if ($adresa2 = $this->getPrimalac()['adresa'][1] ?? false) {
            $primalac .= "\n".$adresa2;
        }

        $primalac = substr($this->preslovi($primalac), 0, 70);
        $iznos    = $this->formatirajIznos(2, ',', '');
        $platilac = $this->getUplatilac()['ime'].($this->telefon ? ' ('.$this->telefon.')' : '');

        if ($adresa = $this->getUplatilac()['adresa'][0] ?? false) {
            $platilac .= "\n".$adresa;
        }

        if ($adresa2 = $this->getUplatilac()['adresa'][1] ?? false) {
            $platilac .= "\n".$adresa2;
        }

        $platilac = substr($this->preslovi($platilac), 0, 70);
        $sifra    = (int)$this->sifra + 100;
        $svrha    = substr($this->preslovi($this->svrha), 0, 35);
        $model    = empty($this->model) ? '00' : $this->model;
        $poziv    = preg_replace('/[^0-9\-]/', '', $this->poziv_na_broj);

        if (empty($poziv)) {
            return "K:PR|V:01|C:1|R:{$racun}|N:{$primalac}|I:RSD{$iznos}|P:{$platilac}|SF:{$sifra}|S:{$svrha}";
        }

        return "K:PR|V:01|C:1|R:{$racun}|N:{$primalac}|I:RSD{$iznos}|P:{$platilac}|SF:{$sifra}|S:{$svrha}|RO:{$model}{$poziv}";
    }

    protected function preslovi(string $tekst): string
    {
        return str_replace(array_keys($this->azbuka), array_values($this->azbuka), $tekst);
    }

}