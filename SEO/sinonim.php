<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$sinonimFile = "sinonims.php";
include $sinonimFile; # тут просто массив с сопоставлениями
if(in_array(18, $USER->GetUserGroupArray())){
    Bitrix\Main\Loader::includeModule("highloadblock");
    function GetEntityDataClass($HlBlockId) {
        if (empty($HlBlockId) || $HlBlockId < 1)    return false;
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($HlBlockId)->fetch();
        return Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
    }
    $entity_data_class = GetEntityDataClass(6);
?>
    <style>
        td {
            padding:5px 5px 5px 5px;
        }
        .edit{
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="kupi-text-color-main-tw"><path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2h-5.5"></path><polyline fill="red" points="14 2 14 8 20 8"></polyline><path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l.99-3.95 5.43-5.44Z"></path></svg>');
            background-repeat: no-repeat;
            background-position: center;
            width: 24px;
        }
        .edit a{
            display: block;
            width: 24px;
        }
        .del{
            background-image: url('data:image/svg+xml,<svg style="color:red;" width="24px" height="24x" viewBox="0 -0.5 8 8" fill="red" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>close_mini [%231522]</title><desc>Created with Sketch.</desc><defs></defs><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Dribbble-Light-Preview" transform="translate(-385.000000, -206.000000)" fill="%23000000"><g id="icons" transform="translate(56.000000, 160.000000)"><polygon fill="red" id="close_mini-[%231522]" points="334.6 49.5 337 51.6 335.4 53 333 50.9 330.6 53 329 51.6 331.4 49.5 329 47.4 330.6 46 333 48.1 335.4 46 337 47.4"></polygon></g></g></g></svg>');
            background-repeat: no-repeat;
            background-position: center;
            width: 24px;
        }
        .del a{
            display: block;
            width: 24px;
        }
    </style>

    <h1>Подмены поисковых запросов (заполнено <?=count($sinonims)?>)</h1> <?php

    if(isset($_GET["sort"])) {
        if($_GET["sort"] == "q") {
            if($_GET["order"] == "desc")
                ksort($sinonims);
            else
                krsort($sinonims);
        }
        if($_GET["sort"] == "d") {
            if($_GET["order"] == "desc")
                array_multisort(array_column($sinonims, 'd'), SORT_DESC, SORT_NUMERIC, $sinonims);
            else
                array_multisort(array_column($sinonims, 'd'), SORT_ASC, SORT_NUMERIC, $sinonims);
        }
        if($_GET["sort"] == "a") {
            if($_GET["order"] == "desc")
                array_multisort(array_column($sinonims, 'a'), SORT_DESC, SORT_STRING, $sinonims);
            else
                array_multisort(array_column($sinonims, 'a'), SORT_ASC, SORT_STRING, $sinonims);
        }
        if($_GET["sort"] == "r") {
            if($_GET["order"] == "desc")
                array_multisort(array_column($sinonims, 'r'), SORT_DESC, SORT_STRING, $sinonims);
            else
                array_multisort(array_column($sinonims, 'r'), SORT_ASC, SORT_STRING, $sinonims);
        }
    } else array_multisort(array_column($sinonims, 'd'), SORT_DESC, SORT_NUMERIC, $sinonims);


$text = '<?' . PHP_EOL;
if(isset($_POST["action"]) && $_POST["action"] == "del"){
    file_put_contents("log.txt", print_r($_POST, true), FILE_APPEND);
    //$sinonims[$_POST["item"]] = ["r" => trim($_POST["replace"]), "d" => time(), "a" => trim($_POST["author"])];
    if(key_exists($_POST["item"], $sinonims))
        unset($sinonims[$_POST["item"]]);

    file_put_contents("log.txt", print_r($sinonims, true), FILE_APPEND);
    $text .= '$sinonims = [' . PHP_EOL;
    foreach ($sinonims as $key => $sinonim) {
        $text .= '"' . $key . '" => ["r" => "' . $sinonim["r"] . '", "d" => "' . $sinonim["d"] . '", "a" => "' . $sinonim["a"] . '"],' . PHP_EOL;
    }
    $text .= '];' . PHP_EOL;
    if(rename($sinonimFile, $sinonimFile.".".time())) {
        lg("Файл переименован");
        file_put_contents($sinonimFile, $text);
    }
    else
        lg("Ошибка переименования файла");
}

if(isset($_POST["from"])){
    if($_POST["from"] != "" && $_POST["to"] != "") {
        if (!str_contains($_POST["from"], "'") && !str_contains($_POST["to"], "'") && !str_contains($_POST["from"], '"') && !str_contains($_POST["to"], '"') && !str_contains($_POST["to"], '\\')) {
            if (!array_key_exists($_POST["from"], $sinonims)) {

                if(str_contains($_POST["to"], ";")){
                    $_POST["to"] = explode(";", $_POST["to"]);
                }

                $sinonims[trim($_POST["from"])]["r"] = mb_strtolower($_POST["to"]);
                $sinonims[trim($_POST["from"])]["d"] = time();
                $sinonims[trim($_POST["from"])]["a"] = $USER->GetFullName();


                //ksort($sinonims);
                array_multisort(array_column($sinonims, 'd'), SORT_DESC, SORT_NUMERIC, $sinonims);


                $text .= '$sinonims = [' . PHP_EOL;
                foreach ($sinonims as $key => $sinonim) {
                    if(is_array($sinonim["r"])){
                        $textR = "[";
                        foreach ($sinonim["r"] as $sino){
                            $textR .= '"'.trim($sino).'",';
                        }
                        $textR = substr($textR,0,-1)."]";
                    }else $textR ='"'.$sinonim["r"].'"';

                    $text .= '"' . $key . '" => ["r" => ' . $textR . ', "d" => "' . $sinonim["d"] . '", "a" => "' . $sinonim["a"] . '"],' . PHP_EOL;
                }
                $text .= '];' . PHP_EOL;
                $result = rename($sinonimFile, $sinonimFile . "." . time());
                file_put_contents($sinonimFile, $text);
            } else echo "<span style='color: red;'>Ошибка. Поисковой запрос <b>'" . $_POST["from"] . "'</b> уже есть!</span></br>";
        } else echo "<span style='color: red;'>Ошибка. В запросах <b>'" . $_POST["from"] . " и " . $_POST["to"] . "'</b> не должно быть кавычек или знакак \</span></br>";
    } else echo "<span style='color: red;'>Ошибка! Пустой запрос.</span></br>";
}?>
<br>Добавить новую подмену:
    <table width="800"><tr><td>
                <form method="post" id="form">
                    <input name="from"> -> <input name="to" id="to">

                </form>
                <button onclick="ajax2()">Записать</button>
            <td><button  onclick="window.location.replace('/test/SEO/sinonim.php')">Обновить таблицу</button> </table>
    <script>
        function ajax2(){
            let text = document.getElementById("to").value;
            console.log(text);
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "/products/?qa=" + text, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("запрос успешно выполнен");
                    //console.log(xhr.responseText);
                }
            };
            xhr.send();
            document.getElementById("form").submit();
        }
    </script>
<?php
    if(isset($_GET["order"]) && $_GET["order"]=="asc")
        $orderText = "&order=desc";
    else $orderText = "&order=asc";

echo "<table border='1' class='sinonim'><tr><th>№<th><a href='?sort=q".$orderText."'>Поисковой запрос</a><th><a href='?sort=r".$orderText."'>Преобразование</a><th><a href='?sort=d".$orderText."'>Изменено</a><th><a href='?sort=a".$orderText."'>Автор</a>";
$i=0;
//ksort($sinonims);
foreach ($sinonims as $key => $sinonim){
    if(is_array($sinonim["r"])){
        $text = "";
        foreach ($sinonim["r"] as $sino){
            $text .= $sino.";";
        }
        $sinonim["r"] = substr($text,0,-1);
    }

    $rsData = $entity_data_class::getList(['order' => [], 'select' => array("*"), 'filter' => array('UF_WORDSTAT_QUERY'=>$sinonim["r"])]);
    $num = "(<a href='/products/?q=".$sinonim["r"]."' target='_blank'>?</a>)";
    while($el = $rsData->fetch()){
        $num = " (<a href='/products/?q=".$sinonim["r"]."' target='_blank'>".$el["UF_WORDSTAT_RESULTS"]."</a>)";
        break;
    }
    //echo $num;
    $i++;
    echo "<tr id='tr_". $i ."'><td>".$i."<td>".$key.
        "<td ondblclick='edit(".$i.", \"".$key."\")'><textarea style='display:none' id='textarea_".$i."'>".$sinonim["r"]."</textarea><span id='span_".$i."'>".$sinonim["r"].$num."</span>".
        "<td>";
    if($sinonim["d"]) $date = date("d.m.y H:i", $sinonim["d"]);
    else $date = "";
    echo $date."<td>".$sinonim["a"].
        "<td class='edit'><a href='##' onClick='edit(".$i.", \"".$key."\")' style='cursor: pointer; height: 20px;'>.</a>";
    if ($USER->IsAdmin())        echo "<td class = del><a href=# onClick='del( \"".$key."\", \"".urlencode($key)."\")' style='cursor: pointer; height: 20px;'> &nbsp;</a>";

    echo "</tr>";
}
echo "</table>";
?>
    <script>
        function del(key, key_esc){
            if (confirm("Удалить правило для "+ key +"?")) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "/test/SEO/sinonim.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log("запрос на удаление успешно выполнен " + key);
                        location.href = '/test/SEO/sinonim.php';
                    }
                };
                //var replace = document.getElementById('textarea_' + id).value;
                xhr.send("action=del&item=" + key_esc);
            }
        }
    function edit(id, key){
        document.getElementById("span_" + id).style.display="none";
        let oldtext = document.getElementById("span_" + id).innerText;
        document.getElementById("textarea_" + id).style.display="block";
        document.getElementById("textarea_" + id).focus();
        //console.log(key);
        document.getElementById("textarea_" + id).onkeydown = function(event){
            if (event.key === "Enter") {
                var replace = document.getElementById('textarea_' + id).value;

                if(replace != oldtext){
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", "/test/SEO/ajax.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            console.log("запрос успешно выполнен " + replace);
                            console.log(xhr.responseText);
                            document.getElementById("tr_" + id).innerHTML = xhr.responseText;
                        }
                    };
                    //var replace = document.getElementById('textarea_' + id).value;
                    let author = "<?=$USER->GetFullName()?>";
                    xhr.send("action=edit&item=" + key +"&replace=" + replace + "&author=" + author + "&id=" + id);
                }

                //document.getElementById("_" + id).style.display="block";
                document.getElementById("textarea_" + id).style.display="none";
            }
            if (event.key === "Escape") {
                document.getElementById("span_" + id).style.display="block";
                document.getElementById("textarea_" + id).style.display="none";
            }
        }
        document.getElementById("textarea_" + id).onblur = function(){
            document.getElementById("span_" + id).style.display="block";
            document.getElementById("textarea_" + id).style.display="none";
        }
    }
</script>
<?php }
else echo "Раздел только для служебного пользования";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");