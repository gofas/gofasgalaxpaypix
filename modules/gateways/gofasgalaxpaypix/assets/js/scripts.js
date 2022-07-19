/**
 * Módulo Juno PIX para WHMCS
 * @copyright	2020 Gofas Software
 * @see			https://gofas.net/?p=14128
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14299
 * @version		1.0.0
 */

$(document).ready(function () {
    var system_url = $("#system_url").val();
    var invoice_id = $("#invoice_id").val();
    var get_url = "modules/gateways/gofasjunopix/includes/callback.php";
    setInterval(function () {
        $.get(
            system_url+get_url,
            {invoice_id : invoice_id},
            function (data) {
               // console.log('get: ' + system_url + get_url + '?=' + invoice_id + ' | status: ' + data);
                if( data == "Unpaid" )  { // Debug
                   //console.log("Aguardando..." ); // Debug
                }
                if( data == "Paid" )  { // Debug
                    window.location.reload();
                }
            }
        );
    }, 1000); // Every 1 second
})