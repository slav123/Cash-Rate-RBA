<?php
require 'simple_html_dom.php';

function scrape($url) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'morph.io scraper',
    ]);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function save_sqlite(array $unique_keys, array $data, $table = 'data') {
    $db = new PDO('sqlite:data.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = array_keys($data);
    $colDefs = array_map(function ($c) {
        return '"' . $c . '" TEXT';
    }, $cols);

    $sql = 'CREATE TABLE IF NOT EXISTS "' . $table . '" (' . implode(', ', $colDefs);
    if ($unique_keys) {
        $sql .= ', UNIQUE(' . implode(', ', $unique_keys) . ')';
    }
    $sql .= ')';
    $db->exec($sql);

    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $insert = 'INSERT OR REPLACE INTO "' . $table . '" (' . implode(', ', $cols) . ') VALUES (' . $placeholders . ')';
    $db->prepare($insert)->execute(array_values($data));
}

$url = 'https://www.rba.gov.au/statistics/cash-rate/';
$html = scrape($url);

$dom = new simple_html_dom();
$dom->load($html);
$ret = $dom->find('#datatable tr');

foreach ($ret as $row) {
    $effective_date = null;
    $change = null;
    $cash_rate = null;

    if ($obj_effective_date = $row->find('th', 0)) {
        $effective_date = $obj_effective_date->plaintext;
    }

    if ($obj_change = $row->find('td', 0)) {
        $change = $obj_change->plaintext;
    }

    if ($obj_cash_rate = $row->find('td', 1)) {
        $cash_rate = $obj_cash_rate->plaintext;
    }

    if ($effective_date && $change !== null) {
        save_sqlite(array('effective_date'), array(
            'effective_date' => date('Y-m-d', strtotime($effective_date)),
            'change'         => $change,
            'cash_rate'      => $cash_rate
        ));
    }
}