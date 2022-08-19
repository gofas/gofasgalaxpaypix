/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14641
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14644
 * @version		0.1.0
 */

$(document).ready(function () {
    var system_url = $("#system_url").val();
    var invoice_id = $("#invoice_id").val();
    var get_url = "modules/gateways/gofasgalaxpaypix/includes/callback.php";
    setInterval(function () {
        $.get(
            system_url+get_url,
            {invoice_id : invoice_id},
            function (data) {
                console.log('get: ' + system_url + get_url + '?=' + invoice_id + ' | status: ' + data);
                if( data == "payedPix" )  { // Debug
                    window.location.reload();
                }
                if( data == "captured" )  { // Debug
                    window.location.reload();
                }
            }
        );
    }, 1000); // Every 1 second
})