=== Wooplatnica ===
Contributors: avram, bloollllldesignlllllstudios, ivijanstefan
Tags: woocommerce, srbija, NBS IPS QR, serbia, uplatnica, opšta uplatnica, common invoice, common invoice slip
Requires at least: 6.0
Tested up to: 6.2
Requires PHP: 7.4
License: MIT
Stable tag: trunk
Version: 1.0
Donate link: https://paypal.me/avramator

WooCommerce payment gateway za generisanje opštih uplatnica i NBS IPS QR kôdova za uplate iz Srbije. 🇷🇸

== Description ==
WooCommerce payment gateway za generisanje opštih uplatnica i NBS IPS QR kôdova za uplate iz Srbije. 🇷🇸

* Po završetku porudžbine korisnik dobija email sa generisanom uplatnicom u PDF formatu.
* Sve uplatnice se čuvaju na serveru (u `wp-content/uplatnice` folderu).
* Opciono, PDF može sadržati i NBS IPS QR kôd za instant plaćanje mobilnim telefonom.

NOVO! Probajte ovaj dodatak brzo na [tastewp.com](https://tastewp.com/new?pre-installed-plugin-slug=woocommerce,wooplatnica&redirect=%2F&ni=true) jednim klikom!

== Installation ==
1. Raspakujte wooplatnica.zip u wp-content/plugins folder
2. Aktivirajte dodatak kroz WP admin panel
3. Otvorite `WooCommerce > Settings > Payments > Opšta uplatnica` da konfigurišete dodatak
4. To je to!

== Frequently Asked Questions ==
Q: Email ne stiže, zašto?
A: Folder `wp-content/plugins/wooplatnica/vendor/mpdf/mpdf/tmp/mpdf/ttfontdata` mora imati dozvole za upisivanje.

Q: Email i dalje ne stiže, zašto?
A: Morate pogledati PHP-ov error_log i obratiti se na forumu za podršku.

Q: Da li mogu da izmenim cenu prilikom generisanja uplatnice?
A: Da! Dodajte funkciju za filter `wooplatnica_cena` i iz te funkcije vratite izmenjenu cenu.

== Screenshots ==
1. Odabir načina plaćanja
2. Generisana PDF uplatnica
3. Generisani NBS IPS QR kôd

== Changelog ==

**1.0**
- tfpdf zamenjen mpdf bibliotekom
- generisanje QR kôda se dešava lokalno (ne više preko Google-a) i zahteva PHP gd ekstenziju
- dodati [ipsqr] i [uplatnica] shortcode-ovi (za "thank you" stranu)
- dodata opcija da se u PDF-u prikaže NBS IPS logo
- dodate PDF header i footer opcije
- minimalna potrebna verzija PHP-a je sada 7.4

**0.8.1**
- provera da li se šalje email vezan za order pre nego što pokušamo da prikačimo PDF

**0.8**
- dodata provera da li je WooCommerce aktiviran pre aktivacije ovog dodatka
- tFPDF ažuriran na poslednju verziju kako bi se izbegla magic quotes gpc greška
- ispravljene sitne greške prijavljene od strane korisnika u v0.7
- minimalna potrebna verzija PHP-a je sada 7.0

**0.7**
- dodato opciono generisanje NBS IPS QR kôda
- dodata opcija da uplatnica sadrži broj telefona platioca
- dodata podrška za PHP 8

**0.6**
- dodata podrška za %products% promenljivu u polju "svrha uplate"

**0.5**
- dodata podrška za drugu liniju adrese kod platioca i primaoca uplate

**0.4**
- ažuriran kod da isprati WooCommerce izmene

**0.3**
- dodati filteri `wooplatnica_cena` i `wooplatnica_order`, uz pomoc kojih mozete menjati konacnu cenu na uplatnici, odnosno citavu porudzbinu pri generisanju uplatnice
- "uplatilac" promenjeno u "platilac" na samom obrascu

**0.2**
- sitne izmene

**0.1**
- prva verzija
