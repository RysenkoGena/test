<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
$sectFilter = Array("IBLOCK_ID"=>4, "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID"));
While($obEl = $list->GetNext())
    $artikuls[] = $obEl["XML_ID"];

$json = json_encode($artikuls);
echo $json;
