
<?php
if(!str_contains($_POST["item"], "'") && !str_contains($_POST["item"], "'") && !str_contains($_POST["replace"], '"') && !str_contains($_POST["replace"], '"')) {
$sinonimFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/.default/components/bitrix/catalog/catalog/sinonim.php";

include $sinonimFile; # тут просто массив с сопоставлениями

$text = '<?' . PHP_EOL;

    if (isset($_POST["action"]) && $_POST["action"] == "edit") {

        $sinonims[$_POST["item"]] = ["r" => trim($_POST["replace"]), "d" => time(), "a" => trim($_POST["author"])];
        $text .= '$sinonims = [' . PHP_EOL;
        foreach ($sinonims as $key => $sinonim) {
            if(str_contains($sinonim["r"], ";")){
                $sinonim["r"] = explode(";", $sinonim["r"]);
                $textR = "[";
                foreach ($sinonim["r"] as $sino){
                    $textR .= '"'.$sino.'",';
                }
                $textR = substr($textR,0,-1)."]";
            }
            else $textR ='"'.$sinonim["r"].'"';
            $text .= '"' . $key . '" => ["r" => ' . $textR . ', "d" => "' . $sinonim["d"] . '", "a" => "' . $sinonim["a"] . '"],' . PHP_EOL;
        }
        $text .= '];' . PHP_EOL;
        $result = rename($sinonimFile, $sinonimFile . "." . time());
        file_put_contents($sinonimFile, $text);
    }


    echo "<td>" . $_POST["id"] .
        "<td><a href='/products/?qa=" . $_POST["item"] . "' target='_blank'>" . $_POST["item"] . "</a>" .
        "<td ondblclick='edit(" . $_POST["id"] . ", \"" . $_POST["item"] . "\")'><textarea style='display:none' id='textarea_" . $_POST["id"] . "'>" . trim($_POST["replace"]) . "</textarea><span id='span_" . $_POST["id"] . "'>" . $_POST["replace"] . "</span>" .
        "<td>" . date("d.m.y H:i") .
        "<td>" . $_POST["author"] .
        "<td><img src='/upload/resize_cache/medialibrary/370/140_105_1/lmovg73ulef29ptl8sm0fogs76qwpw0t.png' onClick='edit(" . $_POST["id"] . ", \"" . $_POST["item"] . "\")' style='cursor: pointer; height: 20px;'>";
}