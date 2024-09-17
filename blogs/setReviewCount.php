<?
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$arFilter = Array("IBLOCK_ID"=>4, "!PROPERTY_BLOG_POST_ID" => false);
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_BLOG_POST_ID");
$res = CIBlockElement::GetList(["ID" => "DESC"], $arFilter, false, [], $arSelect);
//echo "Всего товаров: ".$res->SelectedRowsCount()."<br>";

$products = [];
while($ob = $res->GetNextElement()) {
    $item = $ob->GetFields();
    //deb($item);
    //break;
    $products[$item["~NAME"]] = $item["ID"];
    $productPostId[$item["PROPERTY_BLOG_POST_ID_VALUE"]] = $item["ID"];
}
echo "Всего товаров: ".count($products)."<br>";


CModule::IncludeModule("blog");
$arFilter = [];
$dbPosts = CBlogPost::GetList(["ID" => "ASC"],    $arFilter);
echo "Количество постов: ".$dbPosts->SelectedRowsCount()."<br>";

$posts =[];
while ($arPost = $dbPosts->Fetch())
    $posts[$arPost["TITLE"]] = $arPost["ID"];

echo "Количество валидных постов: ".count($posts)."<br>";

/*foreach ($posts as $post => $value){
    $arFilter = Array("POST_ID"=> $value);
    $dbComment = CBlogComment::GetList([], $arFilter, false, false, []);

    $reviews[$post] = $dbComment->SelectedRowsCount();
}*/

foreach ($productPostId as $postId => $productId){
    $arFilter = Array("POST_ID"=> $postId);
    $dbComment = CBlogComment::GetList([], $arFilter, false, false, []);
    if($numCommnets = $dbComment->SelectedRowsCount())
        $reviews[$productId] = $numCommnets;
}

echo "Количество товаров с отзывами: ".count($reviews)."<br>";
//deb($reviews);




echo "Начинаем обновлять <br>"; $i=0;

foreach ($reviews as $productId => $numReviews){

        echo ++$i . " " . $productId . ": " . $numReviews;

        $el = new CIBlockElement;
        $res = $el->SetPropertyValueCode($productId, 'EXTENDED_REVIEWS_COUNT', $numReviews);
        if($res) {
            echo " успех<br>";
            //break;
        }
        else echo "ошибка<br>";
}
