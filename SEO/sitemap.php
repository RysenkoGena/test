<? $_SERVER['DOCUMENT_ROOT'] = __DIR__."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
$root = '<urlset xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
$urlset = new SimpleXMLElement($root);
//echo __DIR__."/../sitemap.xml";
$y = 0;
$pages = [
    "Главная" => [
        "loc" => "https://yugkabel.ru/",
        "lastmod" => "",
        "priority" => "1.0"
    ],
    "О Компании" => [
        "loc" => "https://yugkabel.ru/about/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Наши объекты" => [
        "loc" => "https://yugkabel.ru/nashi-obekty/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Вакансии" => [
        "loc" => "https://yugkabel.ru/about/vakansii.php",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Покупателям" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Возвраты" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/vozvrat-tovara.php",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Программа лояльности" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/bonus/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Вопрос ответ" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/question/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Каталоги производителей" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/catalogs/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Способы оплаты" => [
        "loc" => "https://yugkabel.ru/payment/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Доставка" => [
        "loc" => "https://yugkabel.ru/delivery/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Обучения" => [
        "loc" => "https://yugkabel.ru/shop/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Контакты" => [
        "loc" => "https://yugkabel.ru/contacts/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Распродажа" => [
        "loc" => "https://yugkabel.ru/special/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Акции" => [
        "loc" => "https://yugkabel.ru/notices/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Производители" => [
        "loc" => "https://yugkabel.ru/brands/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Регистрация" => [
        "loc" => "https://yugkabel.ru/reg/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Новости" => [
        "loc" => "https://yugkabel.ru/news/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Оферта" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/bonus/publichnaya-oferta-programmy-loyalnosti-yugkabel-plyus.php",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Каталог" => [
        "loc" => "https://yugkabel.ru/products/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Акции для ЮЛ" => [
        "loc" => "https://yugkabel.ru/notices/dlya-yurlits/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Свет" => [
        "loc" => "https://yugkabel.ru/svet/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Опросы" => [
        "loc" => "https://yugkabel.ru/votes/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Акции для ИМ" => [
        "loc" => "https://yugkabel.ru/notices/dlya-im/",
        "lastmod" => "",
        "priority" => "0.9"
    ],
    "Приведи друга" => [
        "loc" => "https://yugkabel.ru/pokupatelyam/privedi-druga.php",
        "lastmod" => "",
        "priority" => "0.9"
    ],
];
$unicality = [];
foreach ($pages as $page){
    if(!in_array($page["loc"], $unicality)){
        $y++;
        $unicality[] = $page["loc"];
        $url = $urlset->addChild('url');
        $loc = $url->addChild("loc", $page["loc"]);
        //if($page["lastmod"]) $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"]))); //<lastmod>2022-08-03T09:26:21+01:00</lastmod>
        if ($page["priority"])
            $priority = $url->addChild("priority", $page["priority"]);
    }

}
//debug($unicality);


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++товары========================================================
$sectFilter = Array("IBLOCK_ID"=>4, "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $urlText = "https://yugkabel.ru/products/".$obEl["CODE"]."/";
    if(!in_array($urlText, $unicality)) {
        $y++;
        $unicality[] = $page["loc"];
        $url = $urlset->addChild('url');
        $loc = $url->addChild("loc", $urlText); //<loc>https://yugkabel.ru/products/46303/</loc>
        $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"]))); //<lastmod>2022-08-03T09:26:21+01:00</lastmod>
        //if($i++ > 2) break;
    }
}

//++++++++++++++++++++++++++++++++++++++++++++++ разделы ===============================================
$sectFilter = Array("IBLOCK_ID"=>4, "ACTIVE"=>"Y");
$list = CIBlockSection::GetList(Array("SORT"=>"ASC"),$sectFilter, false, ["ID", "XML_ID", "TIMESTAMP_X", "CODE"]);
//echo "Найдено элементов: ".$list2->SelectedRowsCount();
$i = 0;
While($obEl = $list->GetNext()){
    $y++;
    //$obEl["CODE"] = trim($obEl["CODE"]);
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/products/".$obEl["CODE"]."/"); //<loc>https://yugkabel.ru/products/46303/</loc>
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"]))); //<lastmod>2022-08-03T09:26:21+01:00</lastmod>
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Новости ===================================================
$sectFilter = Array("IBLOCK_ID"=>3, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/news/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}
//+++++++++++++++++++++++++++++++++++++++++++++++ Акции ===================================================
$iBlock = 11;
$sectFilter = Array("IBLOCK_ID"=>$iBlock, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/notices/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Акции для ИМ  ===================================================
$iBlock = 24;
$sectFilter = Array("IBLOCK_ID"=>$iBlock, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/notices/dlya-im/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Акции для Юрлиц ===================================================
$iBlock = 23;
$sectFilter = Array("IBLOCK_ID"=>$iBlock, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/notices/dlya-yurlits/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Производители ===================================================
$iBlock = 8;
$sectFilter = Array("IBLOCK_ID"=>$iBlock, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/brands/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Контакты ===================================================
$sectFilter = Array("IBLOCK_ID"=>15, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/contacts/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}

//+++++++++++++++++++++++++++++++++++++++++++++++ Наши объекты ===================================================
$sectFilter = Array("IBLOCK_ID"=>17, ["LOGIC" => "OR", ["DATE_ACTIVE_TO"=>false], [">DATE_ACTIVE_TO"=>ConvertTimeStamp(time(),"FULL")]], "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "TIMESTAMP_X", "CODE"));
$i=0;
While($obEl = $list->GetNext()){
    $y++;
    $url = $urlset->addChild('url');
    $loc = $url->addChild("loc", "https://yugkabel.ru/nashi-obekty/".$obEl["CODE"]."/");
    $lastmod = $url->addChild("lastmod", date("Y-m-d\\TH:i:sP", strtotime($obEl["TIMESTAMP_X"])));
    //if($i++ > 2) break;
}


if(!$urlset->asXML("sitemap.xml"))
    echo "Ошибка записи файла sitemap.xml";
else {
    //Header('Content-type: text/xml');
    //print_r($urlset->asXML());
    rename("sitemap.xml", __DIR__."/../sitemap.xml");
    echo "файл sitemap.xml сгенерирован".PHP_EOL;
    lg("Sitemap.xml: сгенерировано страниц: ".$y);
}