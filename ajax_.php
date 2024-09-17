<?php
require_once('simple_html_dom.php');
$html = file_get_contents("https://ya.ru");

//echo $html;
$dom = str_get_html($html);

print_r($dom);