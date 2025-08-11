<?php
header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$xml = file_get_contents('20250618_ED807_full.xml');
$dom = new DOMDocument();
$dom->loadXML($xml);

$xpath = new DOMXPath($dom);
$xpath->registerNamespace('ns', 'urn:cbr-ru:ed:v2.0');

$entries = $xpath->query('//ns:BICDirectoryEntry');

$inserts = [];
foreach ($entries as $entry) {
    $bic = $entry->getAttribute('BIC');
    
    $participantInfo = $xpath->query('ns:ParticipantInfo', $entry)->item(0);
    if (!$participantInfo) continue;
    
    $name = $participantInfo->getAttribute('NameP');
    $post = $participantInfo->getAttribute('Ind');
    $city = $participantInfo->getAttribute('Nnp');
    $address = $participantInfo->getAttribute('Adr');
    $regnum = $participantInfo->getAttribute('RegN');
    $dateIn = $participantInfo->getAttribute('DateIn');
    
    $account = $xpath->query('ns:Accounts', $entry)->item(0);
    $ks = $account ? $account->getAttribute('Account') : '';
    
    // Очистка и экранирование данных
    $name = str_replace("'", "''", $name);
    $city = str_replace("'", "''", $city);
    $address = str_replace("'", "''", $address);
    
    $inserts[] = sprintf(
        "('%s', '%s', '%s', '%s', '%s', '%s', '', '', '%s', '%s', '', 0, '2025-06-18')",
        $name, $post, $city, $address, $bic, $ks, $dateIn, $regnum
    );
}

$sql = "INSERT INTO bank (name, post, city, address, bic, ks, tel, urls, date0, regnum, ogrn, status, upd) VALUES \n" . 
       implode(",\n", $inserts) . ";";

file_put_contents('bank_inserts.sql', $sql);
echo "SQL запросы сохранены в bank_inserts.sql";
