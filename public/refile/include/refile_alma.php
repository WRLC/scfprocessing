<?php
include_once 'apikey.php';

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}

function almaRequest($url, $method = 'GET', $body = null, $headers = array())
{
    $ch = curl_init();

    if (!$ch) {
        return array(
            'ok' => false,
            'status' => 0,
            'body' => '',
            'error' => 'Unable to initialize cURL.'
        );
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    } elseif ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($responseBody === false) {
        return array(
            'ok' => false,
            'status' => $statusCode,
            'body' => '',
            'error' => $curlError !== '' ? $curlError : 'Unknown cURL error.'
        );
    }

    return array(
        'ok' => ($statusCode >= 200 && $statusCode < 300),
        'status' => $statusCode,
        'body' => $responseBody,
        'error' => ''
    );
}

function loadXmlFromResponse($response)
{
    if (!is_array($response) || empty($response['ok']) || trim($response['body']) === '') {
        return false;
    }

    return @simplexml_load_string($response['body']);
}

function findItemXmlByBarcode($barcodeInput, $apiKey)
{
    $candidateBarcodes = array();

    $barcodeInput = trim((string)$barcodeInput);

    if ($barcodeInput === '') {
        return array(
            'ok' => false,
            'url' => '',
            'xml' => false,
            'error' => 'Blank barcode.'
        );
    }

    $barcodeWithX = $barcodeInput;
    if (substr($barcodeWithX, -1) !== 'X') {
        $barcodeWithX .= 'X';
    }
    $candidateBarcodes[] = $barcodeWithX;

    if (strtolower(substr($barcodeInput, 0, 1)) === 'p' && strlen($barcodeInput) === 6) {
        $candidateBarcodes[] = $barcodeInput . 'X';
    }

    $barcodeWithoutX = str_replace('X', '', $barcodeWithX);
    if ($barcodeWithoutX !== '') {
        $candidateBarcodes[] = $barcodeWithoutX;
    }

    $candidateBarcodes = array_values(array_unique($candidateBarcodes));

    foreach ($candidateBarcodes as $candidateBarcode) {
        $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode='
            . rawurlencode($candidateBarcode)
            . '&apikey=' . rawurlencode($apiKey);

        $response = almaRequest($url, 'GET');
        $xml = loadXmlFromResponse($response);

        if ($xml !== false && isset($xml->item_data->barcode)) {
            return array(
                'ok' => true,
                'url' => $url,
                'xml' => $xml,
                'error' => ''
            );
        }
    }

    return array(
        'ok' => false,
        'url' => '',
        'xml' => false,
        'error' => 'Item record does not exist.'
    );
}