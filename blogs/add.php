<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>

    <h2>Добавление сообщения в блоге (отзыва)</h2>

<?
CModule::IncludeModule("blog");

$arSelect = ["ID", "NAME", "PROPERTY_RAITING", "DETAIL_TEXT", "DATE_CREATE", "PROPERTY_GOODS"];
$arFilter = Array("IBLOCK_ID"=>2, "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, [], $arSelect);
echo "Найдено старых комментариев: ".$res->SelectedRowsCount()."<br>";
$i=0; $j=0; $noRating = 0;

while($ob = $res->GetNextElement()){
    $j++;
    $arFields = $ob->GetFields();
    if(!$arFields["PROPERTY_GOODS_VALUE"]) continue;

    if(addPost($arFields)) {
        $el = new CIBlockElement;
        $arLoadProductArray = ["ACTIVE" => "N"];
        $PRODUCT_ID = 2;  // изменяем элемент с кодом (ID) 2
        $res2 = $el->Update($arFields["ID"], ["ACTIVE" => "N"]);
        if($res2) echo "Старый отзыв ".$arFields["ID"]. " деактивирован<br>";
        else "Ошибка деактивации отзыва";
    }
    if(++$i > 500)        break;
}
echo "Всего нормальных отзывов отзывов ".$i." из ".$j."<br>Без рейтинга отзывов: ".$noRating;

function addPost($arFields){
    global $noRating;
    // получаем пост по ID товара
    $productID = $arFields["PROPERTY_GOODS_VALUE"];

    $postID = getPostId($productID);
    if(!$postID)
        echo "Не найден пост товара ".$productID."<br><br>";
    else {
        if ($arFields["PROPERTY_RAITING_VALUE"]) {
            //$UserIP = CBlogUser::GetUserIP();
            $arFieldsPost = array(
                //"TITLE" => 'Мое первое сообщение в блоге',
                "POST_TEXT" => "<virtues></virtues> <limitations></limitations> <comment>" . $arFields["~DETAIL_TEXT"] . "</comment>",
                "BLOG_ID" => 1,
                "PATH" => "https://yugkabel.ru" . $postID["product"]["DETAIL_PAGE_URL"] . "?commentId=" . "#reviews",
                "POST_ID" => $postID["ID"], #$postID
                //"PARENT_ID" => 0, //комментарий привязан к сообщению
                //"AUTHOR_ID" => 10000, //добавляем неанонимный комментарий,
                "AUTHOR_NAME" => $arFields["NAME"],
                //в противном случае необходимо задать AUTHOR_NAME, AUTHOR_EMAIL
                "DATE_CREATE" => $arFields["DATE_CREATE"], //'30.07.2024 11:20:36', //ConvertTimeStamp(false, "FULL"),
                //"AUTHOR_IP" => "8.8.8.8",
                //"AUTHOR_IP1" => "8.8.8.8",
                "UF_ASPRO_COM_RATING" => $arFields["PROPERTY_RAITING_VALUE"],
                "UF_ASPRO_COM_APPROVE" => "Y"    # пометка в отзыве "Товар куплен в магазине"
            );
            //deb($arFieldsPost);
            $newID = CBlogComment::Add($arFieldsPost);
            if (IntVal($newID) > 0) {
                echo "Новый комментарий [" . $newID . "] добавлен.<br>";
                return true;
            } else {
                if ($ex = $APPLICATION->GetException()) {
                    echo $ex->GetString();
                    return false;
                }
            }
        } else {
            echo "не записался отзыв!, у него нет оценки <br>";
            $noRating++;
            return false;
        }
    }
}

function getPostId($productID){
    if($productID == 127816)
        echo "Тот самый товар!!<br>";
    $arFilter = Array("IBLOCK_ID"=>4, "ID" => $productID);
    $res = CIBlockElement::GetList(["ID" => "DESC"], $arFilter, false, Array("nPageSize"=>50), []);
    while($ob = $res->GetNextElement())
    {
        $arFields = $ob->GetFields();
        $product = $arFields;
        echo "Найден товар: <a href='/products/".$product["XML_ID"]."'>". $product["XML_ID"]."</a><br>";
        //deb($arFields["NAME"]);
        break;
        //return $arFields["NAME"];
    }
    if($productID == 127816)
        echo "Title = ".$arFields["~NAME"]."<br>";
    $arFilter = ["TITLE" => $arFields["~NAME"]];
    $dbPosts = CBlogPost::GetList(
        ["ID" => "DESC"],
        $arFilter
    );
    if($productID == 127816) lg($dbPosts);

    echo "количество постов: ".$dbPosts->SelectedRowsCount()."<br>";
    while ($arPost = $dbPosts->Fetch())
    {
        //print_r($arPost["ID"]);

        $arPost["product"] = $product;
        echo "ID поста: ".$arPost["ID"]."<br>";
        //deb($arPost);
        return $arPost;
    }
}

?>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
