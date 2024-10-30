=== Metaxy Shop Connector ===
Contributors: metaxysrl
Tags: ecommerce, connector, filters, woocommerce, amazon, ebay, vendite
Requires at least: 5.0
Tested up to: 6.2.0
Requires PHP: 5.6
License: GPL2
Stable tag: 1.2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integra il tuo negozio WooCommerce con i servizi di Metaxy Shop Connector.

== Description ==
Grazie a questo plugin è possibile integrare il tuo WooCommerce con i servizi di Metaxy Shop Connector. Con Metaxy Shop Connector potrai gestire da un'unica piattaforma gli e-commerce più importanti come Amazon, Ebay, Shopify, WooCommerce. Potrai seguire gli ordini, l'andamento delle vendite e gestire il tuo inventario.

== Installation ==
 
1. Carica il file .zip del plugin nella cartella /wp-content/plugins/ oppure caricalo direttamente dall'interfaccia di WordPress.
2. Vai nella pagina **Plugins** e attivalo.
 
== How to uninstall the plugin? =
 
Disattiva il plugin dalla lista plugin di WordPress e poi cliccla disinstalla. 
 
== Changelog ==
= 1.0 =
* Plugin released. 
= 1.0.1 =
* Add upload image extension fix. 
= 1.2.0 =
* Add delete and create media new endpoint '/wp-json/wc/media'.
* Delete Route: /wp-json/wc/media/<mediaId>
* Post Route: /wp-json/wc/media
* Json body parameters: url -> url of the media file, label -> the label name of the media (title)
* Return: mediaId inserted.
