<?php

class Wooplatnica
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize plugin and hooks
     */
    public function __construct()
    {
        load_plugin_textdomain('wooplatnica', false, basename(dirname(__FILE__)).'/languages');

        $this->options = get_option('woocommerce_uplatnica_settings');
        $paths         = wp_upload_dir();
        $this->path    = str_replace('uploads', 'uplatnice', $paths['path']);

        add_filter('woocommerce_payment_gateways', array($this, 'add_wooplatnica_gateway_class'));
        if ($this->options && $this->options['enabled'] === 'yes') {
            add_filter('woocommerce_email_attachments', array($this, 'attach_pdf'), 10, 3);
            add_action('woocommerce_thankyou_uplatnica', array($this, 'thankyou_page'));
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 4);
        }

        add_shortcode('uplatnica', [$this, 'register_uplatnica_shortcode']);
        add_shortcode('ipsqr', [$this, 'register_ipsqr_shortcode']);

        add_filter('admin_footer_text', [$this, 'replace_footer_admin']);
    }

    public function replace_footer_admin()
    {
        echo '<em>Uplatnice generiše s &hearts; <a href="https://wordpress.org/plugins/wooplatnica/" target="_blank">Wooplatnica</a> plugin.</em> ';
    }

    /**
     * @param array    $attachments
     * @param string   $type
     * @param WC_Order $order
     *
     * @return array
     * @throws Exception
     */
    function attach_pdf($attachments, $type, $order)
    {
        if (!$order) {
            return $attachments;
        }

        $orderMethod = get_post_meta($order->get_id(), '_payment_method', true);

        if ($orderMethod === 'uplatnica' && $this->options['enabled'] === 'yes' && $this->is_uplatnica_email($type)) {

            if (!is_dir($this->path)) {
                $created = mkdir($this->path, 0755, true);

                if (!$created) {
                    throw new Exception('Could not create directory '.$this->path);
                }

                file_put_contents($this->path.'/index.html', 'Nothing to see here.');
            }

            $fileName = $this->path.'/'.$order->get_id().'-'.sanitize_title($order->get_billing_first_name()).'-'.sanitize_title($order->get_billing_last_name()).'.pdf';
            $qrCode   = ($this->options['qr_code'] === 'yes') ? $this->options['qr_code_opis'] : false;
            $ipsLogo  = ($this->options['ips_logo'] === 'yes');
            $podaci   = $this->podaci_za_uplatnicu($order);
            $header   = $this->replace($this->options['pdf_header'], $order);
            $footer   = $this->replace($this->options['pdf_footer'], $order);

            $uplatnica = Uplatnica::napravi()
                ->uplatilac($podaci['uplatilac'])
                ->primalac($podaci['primalac'])
                ->svrha($podaci['svrha'])
                ->sifra($podaci['sifra'])
                ->valuta($podaci['valuta'])
                ->iznos($podaci['iznos'])
                ->racun($podaci['racun'])
                ->model($podaci['model'])
                ->pozivNaBroj($podaci['poziv_na_broj'])
                ->telefon($podaci['telefon']);

            UplatnicaPDF::napravi($uplatnica, $qrCode, $ipsLogo, $header, $footer)
                ->generisi($fileName);

            $attachments[] = $fileName;
        }

        return $attachments;
    }

    /**
     * @param string   $string
     * @param WC_Order $order
     *
     * @return mixed
     */
    protected function replace($string, $order)
    {
        return str_replace([
            '%order%',
            '%date%',
            '%year%',
            '%month%',
            '%day%',
            '%products%',
        ], [
            $order->get_id(),
            date('d.m.Y.'),
            date('Y'),
            date('m'),
            date('d'),
            $this->product_list($order),
        ], $string);
    }

    /**
     * @param WC_Order $order
     *
     * @return string
     */
    protected function product_list($order)
    {
        $items  = $order->get_items();
        $titles = [];

        foreach ($items as $item) {
            $titles[] = $item->get_name();
        }

        if (count($titles) === 1) {
            return $titles[0];
        }

        $last = array_pop($titles);

        return implode(', ', $titles).sprintf(' %s ', $this->options['veznik'] ?? 'i').$last;
    }

    /**
     * @param array $methods
     *
     * @return array
     */
    public function add_wooplatnica_gateway_class($methods)
    {
        $methods[] = WC_Gateway_Wooplatnica::class;

        return $methods;
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order
     * @param bool     $sent_to_admin
     * @param bool     $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text, $email)
    {
        if (!$sent_to_admin && 'uplatnica' === $order->get_payment_method() && $order->has_status('on-hold')) {
            if ($this->options['instructions'] && $this->is_uplatnica_email($email->id)) {
                echo wpautop(wptexturize(do_shortcode($this->options['instructions']))).PHP_EOL;
            }
        }
    }

    /**
     * Output for the order received page.
     *
     * @param int $order_id
     */
    public function thankyou_page($order_id)
    {
        if ($content = ($this->options['thank_you'] ?: $this->options['description'])) {
            echo wpautop(wptexturize(wp_kses_post(do_shortcode($content))));
        }
    }

    protected function shortcode_get_order($atts)
    {
        if ($id = $atts['order']) {
            return wc_get_order($id);
        } elseif ($order_key = $_GET['key'] ?? false) {
            return wc_get_order(wc_get_order_id_by_order_key($order_key));
        } elseif ($id = $_GET['order'] ?? false) {
            return wc_get_order($id);
        } else {
            return wc_get_order($this->get_last_order_id());
        }
    }

    protected function podaci_za_uplatnicu(WC_Order $order)
    {
        $order  = apply_filters('wooplatnica_order', $order);
        $ukupno = apply_filters('wooplatnica_cena', $order->get_total());

        $results = [
            'primalac'      => trim($this->options['primalac']) ?: get_bloginfo('name')."\n".get_bloginfo('description'),
            'racun'         => $this->options['racun'],
            'svrha'         => $this->replace($this->options['svrha'], $order),
            'sifra'         => $this->options['sifra'] ?: '189',
            'valuta'        => $this->options['valuta'] ?: $order->get_currency(),
            'model'         => $this->options['model'],
            'poziv_na_broj' => $this->replace($this->options['poziv_na_broj'], $order),
            'uplatilac'     => $order->get_billing_first_name().' '.$order->get_billing_last_name()."\n".$order->get_billing_address_1().($order->get_billing_address_2() ? ' '.$order->get_billing_address_2() : '')."\n".$order->get_billing_postcode().' '.$order->get_billing_city(),
            'telefon'       => ($this->options['platilac_tel'] === 'yes') ? $order->get_billing_phone() : false,
            'iznos'         => $ukupno,
        ];

        return $results;
    }


    public function register_uplatnica_shortcode($atts, $text = '')
    {
        $atts = shortcode_atts([
            'order'  => null,
            'width'  => null,
            'height' => null,
            'alt'    => null,
            'title'  => null,
            'class'  => null,
            'align'  => 'none',
        ], $atts);

        $order  = $this->shortcode_get_order($atts);
        $podaci = $this->podaci_za_uplatnicu($order);

        $uplatnica = Uplatnica::napravi()
            ->uplatilac($podaci['uplatilac'])
            ->primalac($podaci['primalac'])
            ->svrha($podaci['svrha'])
            ->sifra($podaci['sifra'])
            ->valuta($podaci['valuta'])
            ->iznos($podaci['iznos'])
            ->racun($podaci['racun'])
            ->model($podaci['model'])
            ->pozivNaBroj($podaci['poziv_na_broj'])
            ->telefon($podaci['telefon'])
            ->generisi();

        $html = '<img class="wooplatnica uplatnica" src="'.$uplatnica->encode('data-url').'"';
        unset($atts['order']);

        foreach ($atts as $att => $value) {
            if ($value)
                $html .= ' '.$att.'="'.urlencode($value).'"';
        }

        $html .= ' />';

        if ($text) {
            $html = do_shortcode("[caption width=".($atts['width'] ?? 650)." align=align".($atts['align'] ?? 'none')."]$html $text"."[/caption]");
        }

        return $html;
    }

    public function register_ipsqr_shortcode($atts, $text = '')
    {
        $atts = shortcode_atts([
            'order'  => null,
            'size'   => 512,
            'width'  => null,
            'height' => null,
            'alt'    => null,
            'title'  => null,
            'class'  => null,
        ], $atts);

        if ($atts['size'] > 547) {
            return "<p><strong>Greška!</strong> Veličina QR kôda ne može biti veća od 547px!</p>";
        }

        $order  = $this->shortcode_get_order($atts);
        $podaci = $this->podaci_za_uplatnicu($order);

        $ipsQr = IpsQrLocal::napravi()
            ->uplatilac($podaci['uplatilac'])
            ->primalac($podaci['primalac'])
            ->svrha($podaci['svrha'])
            ->sifra($podaci['sifra'])
            ->valuta($podaci['valuta'])
            ->iznos($podaci['iznos'])
            ->racun($podaci['racun'])
            ->model($podaci['model'])
            ->pozivNaBroj($podaci['poziv_na_broj'])
            ->telefon($podaci['telefon'])
            ->generisi();

        $html = "<img class=\"wooplatnica ipsqr\" src=\"".$ipsQr->encode('data-url')."\"";
        unset($atts['order']);
        unset($atts['size']);

        foreach ($atts as $att => $value) {
            if ($value)
                $html .= ' '.$att.'="'.urlencode($value).'"';
        }

        $html .= ' />';

        if ($text) {
            $html = do_shortcode("[caption width=".($atts['width'] ?? $atts['size'])." align=align".($atts['align'] ?? 'none')."]$html $text"."[/caption]");
        }

        return $html;
    }

    protected function get_last_order_id()
    {
        global $wpdb;
        $statuses = array_keys(wc_get_order_statuses());
        $statuses = implode("','", $statuses);
        $results  = $wpdb->get_col("
            SELECT MAX(ID) FROM {$wpdb->prefix}posts
            WHERE post_type LIKE 'shop_order'
            AND post_status IN ('$statuses')
        ");
        return reset($results);
    }

    protected function is_uplatnica_email(string $type)
    {
        return in_array($type, [
            'customer_on_hold_order',
            'customer_invoice'
        ]);
    }


}