<?
if(isset($_GET["action"]) && $_GET["action"] == "remove"){
    rename($_GET["file"], __DIR__."/../old/".$_GET["file"]);
}

$files = scandir(__DIR__, SCANDIR_SORT_DESCENDING);
foreach ($files as $file) {
    if ($file != "index.php" and $file != "." and $file != "..") {
        echo $file . "<br>";
    }
}

