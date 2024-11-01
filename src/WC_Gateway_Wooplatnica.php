<?php

class WC_Gateway_Wooplatnica extends WC_Payment_Gateway
{

    /**
     * WC_Gateway_Wooplatnica constructor.
     */
    public function __construct()
    {
        $this->init_settings();
        $this->init_form_fields();

        $this->id                 = 'uplatnica';
        $this->has_fields         = false;
        $this->method_title       = 'Opšta uplatnica';
        $this->method_description = 'Plaćanje opštom uplatnicom u poštama i bankama Srbije sa mogućnošću generisanja NBS IPS QR kôda.';
        $this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize gateway settings
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __('Aktivirano', 'wooplatnica'),
                'type'    => 'checkbox',
                'label'   => __('Uključi generisanje uplatnica', 'wooplatnica'),
                'default' => '',
            ),
            'title'       => array(
                'title'       => __('Naslov*', 'wooplatnica'),
                'type'        => 'text',
                'description' => __('Naslov ovog tipa plaćanja, prikazan kupcima.', 'wooplatnica'),
                'default'     => "Opšta uplatnica",
            ),
            'description' => array(
                'title'       => __('Opis*', 'wooplatnica'),
                'type'        => 'textarea',
                'description' => __('Opis koji vide kupci.', 'wooplatnica'),
                'default'     => "Dobićete opštu uplatnicu u PDF formatu na email koju možete iskoristiti za plaćanje.",
            ),
            'thank_you'   => array(
                'title'       => __('Thank you strana', 'wooplatnica'),
                'type'        => 'textarea',
                'description' => __('Opis koji vide kupci na "thank you" stranici po izvršenoj kupovini. Ako je prazno biće korišćen opis iz polja iznad.'), // <a href="'.admin_url($gum->license_page_link()).'">PRO korisnici</a> mogu koristiti posebne [uplatnica] i [ipsqr] kodove.', 'wooplatnica'),
                'default'     => "",
            ),

            'instructions'  => array(
                'title'       => __('Uputstvo', 'wooplatnica'),
                'type'        => 'textarea',
                'description' => __('Tekst email poruke koja se šalje kupcu.', 'wooplatnica'),
                'default'     => "U prilogu ove poruke ćete naći uplatnicu u PDF formatu koju možete iskoristiti za plaćanje.",
            ),
            'primalac'      => array(
                'title'       => __('Primalac*', 'wooplatnica'),
                'type'        => 'textarea',
                'description' => __('Puno ime osobe/firme, adresa u drugom i mesto u trećem redu.', 'wooplatnica'),
                'default'     => get_bloginfo('name')."\n".get_option('woocommerce_store_address')."\n".get_option('woocommerce_store_postcode').' '.get_option('woocommerce_store_city'),
            ),
            'platilac_tel'  => array(
                'title'       => __('Telefon platioca', 'wooplatnica'),
                'type'        => 'checkbox',
                'label'       => __('Uključi broj telefona platioca', 'wooplatnica'),
                'description' => __('Označite ako želite da uplatnica sadrži broj telefona platioca.', 'wooplatnica'),
                'default'     => '',
            ),
            'racun'         => array(
                'title'       => __('Broj računa*', 'wooplatnica'),
                'description' => __('Broj računa na koji se vrše uplate.', 'wooplatnica'),
                'type'        => 'text',
                'default'     => '',
            ),
            'svrha'         => array(
                'title'       => __('Svrha uplate*', 'wooplatnica'),
                'description' => __('Svrha uplate. Možete koristiti %order%, %date%, %year%, %month%, %day% i %products% promenljive.', 'wooplatnica'),
                'type'        => 'text',
                'default'     => 'Plaćanje porudžbine #%order%',
            ),
            'sifra'         => array(
                'title'   => __('Šifra plaćanja', 'wooplatnica'),
                'type'    => 'text',
                'default' => '189',
            ),
            'valuta'        => array(
                'title'   => __('Valuta*', 'wooplatnica'),
                'type'    => 'text',
                'default' => 'RSD',
            ),
            'model'         => array(
                'title'   => __('Model', 'wooplatnica'),
                'type'    => 'text',
                'default' => '',
            ),
            'poziv_na_broj' => array(
                'title'       => __('Poziv na broj', 'wooplatnica'),
                'description' => __('Poziv na broj. Možete koristiti %order%, %date%, %year%, %month% i %day% promenljive.', 'wooplatnica'),
                'type'        => 'text',
                'default'     => '%year%-%order%',
            ),
            'veznik'        => array(
                'title'       => __('Veznik', 'wooplatnica'),
                'type'        => 'text',
                'description' => __('Koristi se za spajanje proizvoda kada koristite %products% promenljivu.', 'wooplatnica'),
                'default'     => 'i',
            ),
            'pdf_header'        => array(
                'title'       => __('PDF header', 'wooplatnica'),
                'type'        => 'text',
                'description' => __('Header PDF dokumenta. Možete koristiti %order%, %date%, %year%, %month% i %day% promenljive. Koristite uspravne crte da podelite sadržaj, npr: levo|sredina|desno', 'wooplatnica'),
                'default'     => get_bloginfo('name').'|Nalog za uplatu #%order%|%date%',
            ),
            'pdf_footer'        => array(
                'title'       => __('PDF footer', 'wooplatnica'),
                'type'        => 'text',
                'description' => __('Footer PDF dokumenta. Možete koristiti %order%, %date%, %year%, %month% i %day% promenljive.', 'wooplatnica'),
                'default'     => 'Platite ovaj nalog elektronski i doprinesite očuvanju prirode tako što nećete štampati ovaj dokument.',
            ),
            'qr_code'       => array(
                'title'   => __('QR kôd', 'wooplatnica'),
                'type'    => 'checkbox',
                'label'   => __('Uključi NBS IPS QR kôd u generisani PDF. ', 'wooplatnica'),
                'default' => 'yes',
            ),
            'qr_code_opis'  => array(
                'title'       => __('QR uputstvo', 'wooplatnica'),
                'type'        => 'text',
                'description' => __('Kratko uputstvo za skeniranje QR kôda. Biće prikazano iznad QR kôda u generisanom PDF-u.', 'wooplatnica'),
                'default'     => 'Možete platiti i skeniranjem sledećeg NBS IPS QR kôda:',
            ),
            'ips_logo'       => array(
                'title'   => __('IPS logo', 'wooplatnica'),
                'type'    => 'checkbox',
                'label'   => __('Uključi NBS IPS logo u generisani PDF. ', 'wooplatnica'),
                'default' => 'yes',
            ),
        );
    }

    /**
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);

        $order->update_status('on-hold', __('Awaiting payment', 'woocommerce'));
        $woocommerce->cart->empty_cart();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

}