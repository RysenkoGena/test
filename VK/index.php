<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//if(isset($_REQUEST["auth-token"]) && $_REQUEST["auth-token"] == "2D000001F47F58B3"){

    $post = file_get_contents("php://input");
    $post=json_decode($post, true);
    $warehouseId = $post["warehouseId"];
    $skus = array();  
    foreach($post["skus"] as $sku){
       $sku_array = array(); $items = array();
       $ar = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 4,"ACTIVE" => "Y","XML_ID" => $sku), false, false, Array("ID", "TIMESTAMP_X", "PROPERTY_CENAZ", "IBLOCK_SECTION_ID"))->GetNext();
       $amount = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10), false, false, array("AMOUNT"))->Fetch();
        
        $kit = getKit($ar["PROPERTY_CENAZ_VALUE"], $ar["IBLOCK_SECTION_ID"]);
        if($kit){
            if($amount["AMOUNT"] < $kit) $amount = 0;
            $amount = floor($amount["AMOUNT"] / $kit);
            if($kit != 1) $amount = $kit;
       }
       else $amount = -1;
        $sku_array["sku"] = $sku;
        $sku_array["warehouseId"] = $warehouseId;
        $sku_array["sku"] = $sku;
        $items["type"] = "FIT";
        $items["count"] = $amount;
        $items["updatedAt"] = date("c", strtotime($ar["TIMESTAMP_X"]));
        $sku_array["items"][] = $items;
        $skus[] = $sku_array;
    }
    $return = array();
    $return["skus"] = $skus;
    //debug($skus);
    header('Content-Type: application/json');
    echo json_encode($return);
//}

/*else{
    //header('HTTP/1.0 403 Forbidden');
    http_response_code(403);
    //header('HTTP/1.0 403 Forbidden', true, 403);
    //echo "403 Forbidden";
}*/

function getKit($cenaz, $IBLOCK_SECTION_ID){
        //return $cenaz;
        //return $XML_ID;
        $rootCatalog = getParent($IBLOCK_SECTION_ID);
        //return $rootCatalog;
        if($rootCatalog == 1231) return 50; //кабель - бухты по 50 метров
        elseif($rootCatalog == 1342 || $rootCatalog == 1849){ //лампы и розетки
            if($cenaz >= 350) return 1;
            elseif($cenaz >= 70 && $cenaz < 350) return 5;
            elseif($cenaz >= 35 && $cenaz < 70) return 10;
            elseif($cenaz < 35) return false;
        }
        elseif($rootCatalog == 1435 || $rootCatalog == 1579 || $rootCatalog == 1716 || $rootCatalog == 2183 || $rootCatalog == 2192){ //
            if($cenaz >= 350) return 1;
            else return false;
        }
        elseif($rootCatalog == 2102){ //модульное оборудование
            if($cenaz >= 350) return 1;
            elseif($cenaz >= 115 && $cenaz < 350) return 3;
            elseif($cenaz >= 70 && $cenaz < 115) return 5;
            elseif($cenaz < 70) return false;
        }        
        return $rootCatalog;
}

function getParent($section_id){
    $scRes = CIBlockSection::GetNavChain(4, $section_id, array("ID", "DEPTH_LEVEL"));
    while($arGrp = $scRes->Fetch()){
        if ($arGrp['DEPTH_LEVEL'] == 1)	$ROOT_SECTION_ID = $arGrp['ID'];
    }
    return $ROOT_SECTION_ID;
}
?>