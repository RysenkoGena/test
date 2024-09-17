<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$sections=[];
if(isset($_GET["section"]) && $_GET["section"] != ""){
    $sectionCode = $_GET["section"];

    $res = CIBlockSection::GetList([], array('IBLOCK_ID' => 4, 'CODE' => $sectionCode));
    $section = $res->Fetch();
    $sectionId = $section["ID"];
    echo "<h1>".$section["NAME"]."</h1>";

    $res = CIBlockSection::GetByID($_GET["GID"]);
    $sections = ["SECTION_CODE" => $sectionCode, "INCLUDE_SUBSECTIONS" => "Y"];
}
?>
<form><label for="section">код папки: </label>
    <input name="section" value="<?=$sectionCode?>" onchange="this.form.submit()">
    <? if(isset($_GET["showText"])) $checked = " checked";
    ?>
    <label for="showText">Вывести на экран: </label><input type="checkbox" name="showText" <?=$checked?>  onchange="this.form.submit()">
</form>
<br>
<a href="seo.csv">Скачать CSV-файл</a><br><br>

<?php
$iBlockId = 24; // акции для ИМ
$urlText = "https://yugkabel.ru/notices/dlya-im/";
$razdel = "акции для ИМ";

$iBlockId = 4; // товары
$urlText = "https://yugkabel.ru/products/";
$txt_razdel = "Товары";

showSEO($iBlockId, $urlText, $txt_razdel, $sections, $sectionId);

function showSEO($iBlockId, $urlText, $txt_razdel, $sections, $sectionId){
    $arFilter = array('IBLOCK_ID' => $iBlockId, "ACTIVE" => "Y", "SECTION_ID" => $sectionId, "INCLUDE_SUBSECTIONS" => "Y");
    $arSelect = array('IBLOCK_ID', 'ID', 'NAME', 'SECTION_PAGE_URL', 'DETAIL_PICTURE');
    $rsSect = CIBlockSection::GetList(
        Array("SORT"=>"ASC"), //сортировка
        $arFilter, //фильтр (выше объявили)
        false, //выводить количество элементов - нет
        $arSelect //выборка вывода, нам нужно только название, описание, картинка
    );
    while ($ar_result = $rsSect->GetNext()){
        $items[] = $ar_result;
        $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($iBlockId, $ar_result["ID"]);
        $arSEO[$ar_result["ID"]] = $ipropSectionValues->getValues();
    }
    echo "<h2>Разделы"."</h2> Всего страниц: " . count($items);
    if(isset($_GET["showText"]))  echo "<table><tr><th>№</th><th>URL</th><th>Title</th><th>Description</th><th>H1</th><th>Описание</th></tr>";
    $csv = "№{url{Titile{Description{H1{Описание".PHP_EOL;
    $i = 0; $idRazdelov = [];
    //d($items);
    foreach ($items as $item) {
        $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($iBlockId, $item["ID"]);
        $arSEO = $ipropSectionValues->getValues();
        if(isset($_GET["showText"])) echo "<tr><td>".++$i."</td><td><a href='" . $urlText . $item["CODE"] . "'>" . $urlText. $item["CODE"] . "</a><td>".
              $arSEO["ELEMENT_META_TITLE"]."<td>".
              $arSEO["ELEMENT_META_DESCRIPTION"] . "<td>".
              $item["NAME"]."<td>".
              $item["DETAIL_TEXT"];
        $csv .= $i.
            "{".
            $urlText.
            $item["CODE"]."{".
            str_replace(array("\r\n", "\r", "\n"),"", $arSEO["ELEMENT_META_TITLE"])."{".
            str_replace(array("\r\n", "\r", "\n"),"", $arSEO["ELEMENT_META_DESCRIPTION"])."{".
            $item["NAME"]."{".
            str_replace(array("\r\n", "\r", "\n"),"",$item["DETAIL_TEXT"])."{".
            PHP_EOL;
        $idRazdelov[] = $item["ID"];
    }
    if(isset($_GET["showText"])) echo "</table><hr>";



    $arFilter = array('IBLOCK_ID' => $iBlockId, "ACTIVE" => "Y", $sections);
    $db_list = CIBlockElement::GetList([], $arFilter, false, false, []);

    $SEOCount = 0;
    $sections = [];
    while ($ar_result = $db_list->GetNext()) {
        $items[] = $ar_result;
        //echo $ar_result['ID'].' '.$ar_result['NAME'].': '.$ar_result['ELEMENT_CNT'].'<br>';
        $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(4, $ar_result['ID']);
        $arSEO = $ipropSectionValues->getValues();
        //if ($arSEO['SECTION_META_TITLE'] != false) echo $arSEO['SECTION_META_TITLE']."<br>";
    }
    echo "<h2>".$txt_razdel."</h2> Всего страниц: " . count($items);
    if(isset($_GET["showText"])) echo "<table><tr><th>№</th><th>URL</th><th>Title</th><th>Description</th><th>H1</th><th>Описание</th></tr>";
    foreach ($items as $section) {
        if(in_array($section['ID'], $idRazdelov)) continue;
        //debug($section);
        $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iBlockId, $section['ID']);
        $arSEO = $ipropSectionValues->getValues();
        if($iBlockId == 4) $detailText = $section["DETAIL_TEXT"];
        //debug($arSEO);
        if(isset($_GET["showText"])) echo "<tr><td>".++$i."</td><td><a href='" . $urlText . $section["CODE"] . "'>". $urlText . $section["CODE"] . "</a><td>".
            $arSEO["ELEMENT_META_TITLE"] . "<td>".
            $arSEO["ELEMENT_META_DESCRIPTION"] . "<td>" .
            $section["NAME"]."<td>".
            $detailText;
        $csv .= $i."{". $urlText . $section["CODE"]."{".str_replace(array("\r\n", "\r", "\n"),"", $arSEO["ELEMENT_META_TITLE"])."{".str_replace(array("\r\n", "\r", "\n"),"", $arSEO["ELEMENT_META_DESCRIPTION"])."{".$section["NAME"]."{".str_replace(array("\r\n", "\r", "\n"),"", $detailText).PHP_EOL;
        //break;
    }
    if(isset($_GET["showText"])) echo "</table>";
    //echo $csv;
    file_put_contents("seo.csv", $csv);
}

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