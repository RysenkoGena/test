<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список сохраненных корзин");
$arSelect = Array("ID", "XML_ID", "PROPERTY_PRODUCT_ID", "PROPERTY_NAME", "PROPERTY_COUNT", "PROPERTY_USER_ID", "PROPERTY_BASKET_NAME", "created_date", "PROPERTY_BASKET_ID");

/*if(isset($_GET["delete"]) && $_GET["delete"] != ""){ # удаление корзин
    $res = CIBlockElement::GetList([],['IBLOCK_ID' => 20, 'PROPERTY_BASKET_ID' => $_GET["delete"]], $arSelect);
    $codeIblockArr = [];
    while ($ar_res = $res->fetch()) {
        debug($ar_res);
        $codeIblockArr[]['ID'] = $ar_res['ID'];
        $codeIblockArr[]['PROPERTY_USER_ID'] = $ar_res['PROPERTY_USER_ID_VALUE'];
    }
    $el = new CIBlockElement;
    foreach($codeIblockArr as $codeElement){
        if($codeElement['PROPERTY_USER_ID'] == $USER->GetID())
            $x = $el->Update($codeElement['ID'], ['ACTIVE'=>'N']);
    }
    header("Location: /basket/spisok-sokhranennykh-korzin.php");
}*/

$arFilter = Array("IBLOCK_ID" => 20, "ACTIVE" => 'Y');//, "PROPERTY_USER_ID" => $USER->GetID());

$res = CIBlockElement::GetList(Array(), $arFilter, false, [], $arSelect);
//$baskets = array();
while($ob = $res->GetNextElement()){
    $arFields = $ob->GetFields();
    $baskets[$arFields["PROPERTY_BASKET_ID_VALUE"]][] = $arFields;
    //debug($arFields);
}
//debug($baskets);
echo "<table>";
foreach($baskets as $key => $basket){
    echo "<tr><td><a href='/basket/spisok-sokhranennykh-korzin.php?basket=".$basket[0]["PROPERTY_BASKET_ID_VALUE"]."' id='l__".$key."'>". $basket[0]["PROPERTY_BASKET_NAME_VALUE"]." (".$basket[0]["CREATED_DATE"] .")</a>
    <span title='скопировать ссылку на корзину' class='copy-to-clipboard' id='w__".$key."'></span>";

    $rsUser = CUser::GetByID($basket[0]["PROPERTY_USER_ID_VALUE"]);
    $arUser = $rsUser->Fetch();
    if($arUser["EMAIL"] != "")
        echo "<td><a href='mailto:".$arUser["EMAIL"]."'>".$arUser["NAME"]." ".$arUser["LAST_NAME"]."</a><td>+7".$arUser["PERSONAL_PHONE"];
    else echo "<td>Неавторизованный пользователь";
    //debug($arUser);
    //if($USER->IsAuthorized())        echo " <a href='?delete=".$basket[0]["PROPERTY_BASKET_ID_VALUE"]."' class='delete-item' title='удалить'></a>";

    echo "<br>";
}
echo "</table>";
if(isset($_GET["basket"]) && $_GET["basket"] != ""){
    //if(isset($_GET["user"]) && $_GET["user"] != ""){
        //$arFilter = [];
        $arFilter = Array("IBLOCK_ID" => 20, "PROPERTY_BASKET_ID" => $_GET["basket"]);
    //}
    //debug($arFilter);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
   
    if($res->SelectedRowsCount() > 0){ ?>
        <h1>Корзина "<span id=basketName></span>"</h1>
        <div style="text-align:right;">
            <a onClick='document.getElementById("pdf").submit()'> <img src="/upload/medialibrary/d48/qurtes18xytzuiyeea5h6ky3bj1ix23m.gif" style="height:30px;"><span>Сохранить в PDF</span></a>
        </div>

        <div id="basket_items_list" class=bx_ordercart ><div class="bx_ordercart_order_table_container">
            <form method=post action=/inc/ajax/uploadInBasket.php?clear_cache=Y id=form>
                <table id="basket_items">
                <thead>
                    <tr><td class="item">№</td><td class="item">Фото</td><td class="item" >Наименование товара<td class="item" >Производитель<td class="price" id="col_PRICE">Цена, руб.<td class="custom">Количество<td class="custom" id="col_QUANTITY">Наличие на складах<td class="custom" id="col_SUM">Сумма, руб.<td class="custom">
                </thead>
                <? $i=0; $totalPrice = 0; $forPDF=[];
                while($ob = $res->GetNextElement()){
                    $arFields = $ob->GetFields();
                    //debug($arFields["PROPERTY_PRODUCT_ID_VALUE"]);
                    $arFilter2 = Array("IBLOCK_ID" => 4, "ACTIVE" => "Y", "ID" => $arFields["PROPERTY_PRODUCT_ID_VALUE"]);
                    $res2 = CIBlockElement::GetList(Array(),$arFilter2, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "PREVIEW_PICTURE", "PROPERTY_proizvoditel", "NAME", 'DETAIL_PICTURE_SRC'));
                    while($ob2 = $res2->GetNextElement())
                        $prod = $ob2->GetFields();
                    $img = CFile::GetFileArray($prod["PREVIEW_PICTURE"]);
                    $price = CPrice::GetBasePrice($arFields["PROPERTY_PRODUCT_ID_VALUE"]);
                    //get_Store($arFields["PROPERTY_PRODUCT_ID_VALUE"]);
                    //debug($price);
                    $totalPrice += $price["PRICE"] * $arFields["PROPERTY_COUNT_VALUE"];
                    $i++;

                    $forPDF[$i]["P_name".$i] = $arFields["PROPERTY_NAME_VALUE"];
                    $forPDF[$i]["P_kod".$i] = $prod["XML_ID"];
                    $forPDF[$i]["P_firm".$i] = $prod["PROPERTY_PROIZVODITEL_VALUE"];
                    $forPDF[$i]["P_price".$i] = $price["PRICE"];
                    $forPDF[$i]["P_quatity".$i] = $arFields["PROPERTY_COUNT_VALUE"];
                    $forPDF[$i]["P_ed".$i] = "шт";
                    if(mb_strlen($arFields["PROPERTY_NAME_VALUE"]) > 65)
                        $textName = mb_substr($arFields["PROPERTY_NAME_VALUE"], 0, 65)."<br>".mb_substr($arFields["PROPERTY_NAME_VALUE"], 65, 65);
                    else $textName = $arFields["PROPERTY_NAME_VALUE"];
                    echo "<tr><td>".$i.
                        "<td><a href=/products/".$prod["XML_ID"]."/><img src='".$img["SRC"]."' class='preview_photo'></a>".
                        "<td><a href=/products/".$prod["XML_ID"]."/>".$textName."</a>";

                    if($arFields["PROPERTY_COUNT_VALUE"] > get_Store($arFields["PROPERTY_PRODUCT_ID_VALUE"]) ){
                        $text = "<sup>*</sup>";
                        $textAlert = "<sup><b>*</b></sup> - обратите внимание, количество помеченных позиций в корзине превышает свободное количество на складах<br>";
                    }
                    else $text = "";

                    echo"<td>".$prod["PROPERTY_PROIZVODITEL_VALUE"].
                        "<td>".$price["PRICE"].
                        "<td>".$arFields["PROPERTY_COUNT_VALUE"].$text.
                        "<td>".get_Store($arFields["PROPERTY_PRODUCT_ID_VALUE"]).

                        "<td>".($price["PRICE"]*$arFields["PROPERTY_COUNT_VALUE"]);
                    echo "<input type=hidden name=itemID".$i." value=".$arFields['PROPERTY_PRODUCT_ID_VALUE']."><input type=hidden name=quantity".$i." value=".$arFields["PROPERTY_COUNT_VALUE"].">";
                    //$baskets[$arFields["PROPERTY_BASKET_NAME_VALUE"]][] = $arFields;
                    //debug($arFields);
                }
                ?><tr><td colspan=6><td><b>Итого:<td><b><?=$totalPrice?></b>
                </table>
                <?=$textAlert?>
                <script>
                    //console.log();
                    document.getElementById("basketName").innerHTML = "<?=$arFields["PROPERTY_BASKET_NAME_VALUE"]?>";

                    $(function() {
                        function copyToClipboard(element) {// copy content to clipboard
                            console.log(element);
                            element = element.replace("w__", "l__");
                            //console.log(element);
                            var $temp = $("<input>");
                            $("body").append($temp);
                            console.log($(document.getElementById(element)).prop('href'));
                            $temp.val($(document.getElementById(element)).prop('href')).select();
                            
                            //$temp.val($(element).text()).select();
                            document.execCommand("copy");
                            $temp.remove();
                        }
                        // copy coupone code to clipboard
                        $(".copy-to-clipboard").on("click", function() {
                            //console.log($(this).prop('id'));
                            copyToClipboard($(this).prop('id'));
                            //$(".coupon-alert").fadeIn("slow");
                        });
                    });
                </script>    
                <a href="javascript:void(0)" onClick="document.getElementById('form').submit();" id="oformit_zakaz" class="upload-in-basket">Оформить заказ</a>
            </form>
            <form id=pdf action=/mpdf/pdf.php method=post>
                <?
                    foreach($forPDF as $items){
                        foreach($items as $key => $item){?>
                            <input type=hidden name="<?=$key?>" value="<?=$item?>">
                        <?}
                    }
                ?>
                <input type='hidden' name=number_position value=<?=$i?>>
            </form>
        </div></div><?
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
function get_Store($productId){
    //echo "получаем цену для ". $productId;
    $rsStoreProduct = \Bitrix\Catalog\StoreProductTable::getList(array(
        'filter' => array('=PRODUCT_ID'=>$productId,'=STORE.ACTIVE'=>'Y'),
        'select' => array('AMOUNT','STORE_ID','STORE_TITLE' => 'STORE.TITLE'),
    ));
    $count = 0;
    while($arStoreProduct=$rsStoreProduct->fetch())
        $count += $arStoreProduct["AMOUNT"];
    //debug ($count);
    return $count;
}
