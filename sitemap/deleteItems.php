<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$linksForDelete = file(__DIR__."/exclude.txt", FILE_IGNORE_NEW_LINES);

//d($linksForDelete);

$xml = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/sitemap.xml");

$xml = simplexml_load_string($xml);

d($xml->url[4]);
$j=0;
foreach($xml->url as $a => $b) {
    foreach ($b->loc as $c) {
        $links[] = $c;
    }
}
//debug($links);
$i = 0;
foreach ($linksForDelete as $link) {
    $links = [];
    foreach($xml->url as $a => $b) {
        foreach ($b->loc as $c) {
            $links[] = $c;
        }
    }
    if (in_array($link, $links)) {
        $key = array_search($link, $links);
        echo "удалить ".$link . " ". $key."<br>";
        //echo "ссылка Найдена<br>";
        unset($xml->url[$key]);
        //debug( $xml->xpath('//*[text()="'.$link.'"]'));
        //unset($xml->xpath('//*[text()="'.$link.'"]')[0]->{0});
    }
}
d($xml);
$output = $xml->asXML();
echo __DIR__."/sitemap.xml";

file_put_contents(__DIR__."/sitemap.xml", $output);
//d($links);


