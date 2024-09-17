<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$newlinks = file(__DIR__."/list.csv");

//d($newlinks);

$xml = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/sitemap.xml");

//echo $xml;

$xml = simplexml_load_string($xml);

//d($xml);
foreach($xml->url as $a => $b) {
    foreach ($b->loc as $c)
     $links[] = $c;
}

foreach ($newlinks as $link)
    if(!in_array($link, $links)) {
        $url = $xml->addChild('url');
        $url->addChild("loc", trim($link));
        //$lastmod = $xml->addChild('lastmod'); # пока не понятно где брать дату создания страницы
    }
//d($xml);
$output = $xml->asXML();
echo __DIR__."/sitemap.xml";

file_put_contents(__DIR__."/sitemap.xml", $output);
//d($links);


