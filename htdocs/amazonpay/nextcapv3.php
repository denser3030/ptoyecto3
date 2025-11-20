<?php
error_reporting(0);
header('Content-Type: application/json');

// Substitua pela sua chave de API do CapMonster
$API_KEY = "next_e633fd24f49e032c2fe4dda4ba111377fe";

// Obter os parâmetros via GET
$WEBSITE_URL = isset($_GET['website_url']) ? $_GET['website_url'] : null;
$WEBSITE_KEY = isset($_GET['website_key']) ? $_GET['website_key'] : null;

// Verificar se os parâmetros necessários foram fornecidos
if (!$WEBSITE_URL || !$WEBSITE_KEY) {
    echo json_encode(array(
        "success" => false,
        "message" => "Parâmetros 'website_url' e 'website_key' são obrigatórios."
    ));
    exit;
}

/**
 * Função para enviar requisições POST com cURL
 *
 * @param string $url A URL para onde a requisição será enviada
 * @param array $data Os dados a serem enviados na requisição
 * @return array A resposta decodificada em formato associativo ou erro
 */
function sendPostRequest($url, $data) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Accept: application/json",
       "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    $resp = curl_exec($curl);

    if(curl_errno($curl)){
        $error_msg = curl_error($curl);
    }
    curl_close($curl);
    
    if(isset($error_msg)){
        return array("error" => $error_msg);
    }
    
    return json_decode($resp, true);
}

/**
 * Função para criar uma tarefa de reCAPTCHA v2 Proxyless
 *
 * @param string $API_KEY Sua chave de API do CapMonster
 * @param string $WEBSITE_URL A URL do site que implementa o reCAPTCHA v2
 * @param string $WEBSITE_KEY A chave do site (site key) do reCAPTCHA v2
 * @return array A resposta da API do CapMonster
 */
function createTask($API_KEY, $WEBSITE_URL, $WEBSITE_KEY) {
    $url = "https://api.nextcaptcha.com/createTask";
    $websitedata = file_get_contents('websitedata.txt');
    $data = array(
        "clientKey" => $API_KEY,
        "task" => array(
            "type" => "RecaptchaV3TaskProxyless",
            "websiteURL" => $WEBSITE_URL,
            "websiteKey" => $WEBSITE_KEY,
            "pageAction" => "PERFORM_TRANSACTION",
            "websiteInfo" => $websitedata
        )
    );
    
    return sendPostRequest($url, $data);
}

/**
 * Função para obter o resultado de uma tarefa
 *
 * @param string $API_KEY Sua chave de API do CapMonster
 * @param string $taskId ID da tarefa a ser verificada
 * @return array A resposta da API do CapMonster
 */
function getTaskResult($API_KEY, $taskId) {
    $url = "https://api.nextcaptcha.com/getTaskResult";
    
    $data = array(
        "clientKey" => $API_KEY,
        "taskId" => $taskId
    );
    
    return sendPostRequest($url, $data);
}

// Criação da tarefa para resolver o reCAPTCHA v2
$createResponse = createTask($API_KEY, $WEBSITE_URL, $WEBSITE_KEY);

if(isset($createResponse['error'])){
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao criar a tarefa",
        "details" => $createResponse['error']
    ));
    exit;
}

if($createResponse['errorId'] !== 0){
    echo json_encode(array(
        "success" => false,
        "message" => "Erro na resposta da criação da tarefa",
        "errorId" => $createResponse['errorId']
    ));
    exit;
}

$taskId = $createResponse['taskId'];

$maxAttempts = 120;
$attempt = 0;
$delay = 1;

while($attempt < $maxAttempts){
    sleep($delay);
    $resultResponse = getTaskResult($API_KEY, $taskId);
    
    if(isset($resultResponse['error'])){
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao obter o resultado da tarefa",
            "details" => $resultResponse['error']
        ));
        exit;
    }
    
    if($resultResponse['errorId'] !== 0){
        echo json_encode(array(
            "success" => false,
            "message" => "Erro na resposta da verificação da tarefa",
            "errorId" => $resultResponse['errorId']
        ));
        exit;
    }
    
    if($resultResponse['status'] === "ready"){
        // Tarefa concluída com sucesso
        echo json_encode(array(
            "success" => true,
            "taskId" => $taskId,
            "gRecaptchaResponse" => $resultResponse['solution']['gRecaptchaResponse']
        ));
        exit;
    }
    
    $attempt++;
}

echo json_encode(array(
    "success" => false,
    "message" => "Tempo limite atingido sem obter a resposta da tarefa."
));
?>
