<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DETAIL_PICTURE", "PROPERTY_VIDEO", "PROPERTY_textButton", "PROPERTY_HREFBUTTON");
$arFilter = Array("IBLOCK_ID"=>26, "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
?>

<div class="container">
    <div class = "slider">
        <div id = "slider_line" class="slider_line">
            <?while($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();?>
                <div class="div-cadr">
                    <?if($arFields["PROPERTY_VIDEO_VALUE"]){?>
                        <video src="<?=CFile::GetPath($arFields["PROPERTY_VIDEO_VALUE"])?>" playsinline="" autoplay="" loop="" class = "cadr" muted></video>
                    <?} else{?>
                        <img src="<?=CFile::GetPath($arFields["DETAIL_PICTURE"])?>" class = "cadr">
                    <?}
                        if($arFields["PROPERTY_TEXTBUTTON_VALUE"]){?>
                            <a href="<?=$arFields["PROPERTY_HREFBUTTON_VALUE"]?>"><div class="knopka"><?=$arFields["PROPERTY_TEXTBUTTON_VALUE"]?></div></a>
                        <?}
                    ?>
                </div>
            <?}?>
        </div>
        <button class="button slider-prev">
            <svg width="26" height="58" viewBox="0 0 26 58" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24.7827 58.6958L1.30446 30.0001L24.7827 1.30449" stroke="currentColor" stroke-width="1.5"></path></svg>
        </button>
        <button class="button slider-next">
            <svg width="26" height="58" viewBox="0 0 26 58" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24.7827 58.6958L1.30446 30.0001L24.7827 1.30449" stroke="currentColor" stroke-width="1.5"></path></svg>
        </button>
    </div>
</div>
<?
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
while($ob = $res->GetNextElement()){
    $arFields = $ob->GetFields();
    //debug($arFields);
    //debug(CFile::GetPath($arFields["PROPERTY_VIDEO_VALUE"]));
}
?>

<div class="lenta">
    <?
    $arSelect = Array("ID", "NAME", "DETAIL_PICTURE", "PROPERTY_HREF");
    $arFilter = Array("IBLOCK_ID"=>27, "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
    while($ob = $res->GetNextElement()){
        $arFields = $ob->GetFields();
    ?>
    <div class="lenta-item">
        <a href="<?=$arFields["PROPERTY_HREF_VALUE"]?>" style="display: block; position: relative;">
        <div class="lenta-image">
            <img src="<?=CFile::GetPath($arFields["DETAIL_PICTURE"])?>">
        </div>
        <div class="lenta-text">
            <?=$arFields["NAME"]?>
        </div>
        </a>

    </div>
    <?}?>
</div>

<?
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
while($ob = $res->GetNextElement()){
    //debug($ob);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
