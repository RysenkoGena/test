<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$iBlockId = 24;
$arFilter = Array('IBLOCK_ID' => $iBlockId,);
$db_list = CIBlockElement::GetList([], $arFilter, false, false, []);
//$db_list->NavStart(20);
//echo $db_list->NavPrint($arIBTYPE["SECTION_NAME"]);
$SEOCount = 0; $sections = [];
while($ar_result = $db_list->GetNext()){
    $sections[] = $ar_result;
    /*echo $ar_result['ID'].' '.$ar_result['NAME'].': '.$ar_result['ELEMENT_CNT'].'<br>';
    $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(4, $ar_result['ID']);
    $arSEO = $ipropSectionValues->getValues();
    if ($arSEO['SECTION_META_TITLE'] != false) echo $arSEO['SECTION_META_TITLE']."<br>";*/
}
echo "Всего элементов: ".count($sections)."<table>";
foreach ($sections as $section){

    $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iBlockId, $section['ID']);
    $arSEO = $ipropSectionValues->getValues();
    //var_dump($arSEO["ELEMENT_META_KEYWORDS"]);
    //break;
    if($arSEO["ELEMENT_META_KEYWORDS"]){
        $SEOCount++;
        echo "<tr><td>".$section['NAME'].': '.$section['ELEMENT_CNT'].'<td>';
        echo $arSEO['ELEMENT_META_KEYWORDS'];
        $ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($iBlockId, $section['ID']);
        //Установить шаблон для элемента
        $ipropTemplates->set(array(
            "ELEMENT_META_KEYWORDS" => "",
        ));
        /*$bs = new CIBlockElement;
        $arLoadProductArray = Array(
            "IPROPERTY_TEMPLATES"   => array(
                "ELEMENT_META_KEYWORDS" => "",
            )
        );*/
        //$res = $el->Update($PRODUCT_ID, $arLoadProductArray);
        //$res = $bs->Update($section['ID'], $arLoadProductArray);
        //echo "<br>".$section['ID'];
        //d($res);
        //if($SEOCount > 10) break;*/
    }
}
echo "</table>c заполненным keywords: ".$SEOCount."<br>";
/*
//echo $db_list->NavPrint($arIBTYPE["SECTION_NAME"]);

/*$SECTION_ID = $sec['ID'];
$IBLOCK_ID = 11;

//$ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID, $SECTION_ID);
$arSEO = $ipropSectionValues->getValues();
if ($arSEO['SECTION_META_TITLE'] != false) {
    $APPLICATION->SetPageProperty("title", $arSEO['SECTION_META_TITLE']);
}
if ($arSEO['SECTION_META_KEYWORDS'] != false) {
    $APPLICATION->SetPageProperty("keywords", $arSEO['SECTION_META_KEYWORDS']);
}
if ($arSEO['SECTION_META_DESCRIPTION'] != false) {*/


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>