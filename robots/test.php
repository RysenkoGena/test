<?php
$array = [
    "https://yugkabel.ru/products/111102/",
    "https://yugkabel.ru/products/111132/",
    "https://yugkabel.ru/products/111218/",
    "https://yugkabel.ru/products/111219/",
    "https://yugkabel.ru/products/115960/",
    "https://yugkabel.ru/products/141341/",
    "https://yugkabel.ru/products/25341/",
    "https://yugkabel.ru/products/31636/",
    "https://yugkabel.ru/products/32001/",
    "https://yugkabel.ru/products/43555/",
    "https://yugkabel.ru/products/43959/",
    "https://yugkabel.ru/products/44896/",
    "https://yugkabel.ru/products/44897/",
    "https://yugkabel.ru/products/45924/",
    "https://yugkabel.ru/products/45926/",
    "https://yugkabel.ru/products/45927/",
    "https://yugkabel.ru/products/46343/",
    "https://yugkabel.ru/products/46379/",
    "https://yugkabel.ru/products/48147/",
    "https://yugkabel.ru/products/49366/",
    "https://yugkabel.ru/products/49377/",
    "https://yugkabel.ru/products/49391/",
    "https://yugkabel.ru/products/49396/",
    "https://yugkabel.ru/products/49397/",
    "https://yugkabel.ru/products/49398/",
    "https://yugkabel.ru/products/49405/",
    "https://yugkabel.ru/products/49504/",
    "https://yugkabel.ru/products/49505/",
    "https://yugkabel.ru/products/49506/",
    "https://yugkabel.ru/products/49571/",
    "https://yugkabel.ru/products/49573/",
    "https://yugkabel.ru/products/50353/",
    "https://yugkabel.ru/products/50893/",
    "https://yugkabel.ru/products/52579/",
    "https://yugkabel.ru/products/52583/",
    "https://yugkabel.ru/products/52584/",
    "https://yugkabel.ru/products/52585/",
    "https://yugkabel.ru/products/52587/",
    "https://yugkabel.ru/products/52588/",
    "https://yugkabel.ru/products/52593/",
    "https://yugkabel.ru/products/52597/",
    "https://yugkabel.ru/products/52598/",
    "https://yugkabel.ru/products/53070/",
    "https://yugkabel.ru/products/53469/",
    "https://yugkabel.ru/products/53553/",
    "https://yugkabel.ru/products/54162/",
    "https://yugkabel.ru/products/54493/",
    "https://yugkabel.ru/products/55138/",
    "https://yugkabel.ru/products/56386/",
    "https://yugkabel.ru/products/56838/",
    "https://yugkabel.ru/products/56840/",
    "https://yugkabel.ru/products/56898/",
    "https://yugkabel.ru/products/57239/",
    "https://yugkabel.ru/products/57862/",
    "https://yugkabel.ru/products/58040/",
    "https://yugkabel.ru/products/58144/",
    "https://yugkabel.ru/products/60115/",
    "https://yugkabel.ru/products/61319/",
    "https://yugkabel.ru/products/61830/",
    "https://yugkabel.ru/products/63377/",
    "https://yugkabel.ru/products/63455/",
    "https://yugkabel.ru/products/63827/",
    "https://yugkabel.ru/products/65037/",
    "https://yugkabel.ru/products/68886/",
    "https://yugkabel.ru/products/69826/",
    "https://yugkabel.ru/products/72269/",
    "https://yugkabel.ru/products/72992/",
    "https://yugkabel.ru/products/73274/",
    "https://yugkabel.ru/products/74387/",
    "https://yugkabel.ru/products/74682/",
    "https://yugkabel.ru/products/78058/",
    "https://yugkabel.ru/products/78059/",
    "https://yugkabel.ru/products/84463/",
    "https://yugkabel.ru/products/86238/",
    "https://yugkabel.ru/products/88320/",
    "https://yugkabel.ru/products/88985/",
    "https://yugkabel.ru/products/90032/",
    "https://yugkabel.ru/products/92399/",
    "https://yugkabel.ru/products/94343/",
    "https://yugkabel.ru/products/94658/",
    "https://yugkabel.ru/products/94904/",
    "https://yugkabel.ru/products/95576/",
    "https://yugkabel.ru/products/96031/",
    "https://yugkabel.ru/products/96044/",
    "https://yugkabel.ru/products/96935/",
];
echo "<pre> Ищем \"<h1\""."<br>";
$i=0;
foreach ($array as $item){

    check($item, "<h1");
    //if($i > 4) break;
    $i++;
}
echo "</pre>";

function check($url, $needle){
    $needle_text = htmlspecialchars($needle);
    $text = file_get_contents($url);
    $count_text = strlen($text);
    $count = substr_count(strtolower($text), $needle);
    if($count > 1) {
        echo $url . " длина текста: " . $count . " -> ";
        echo "количество вхождений <b>" . $needle_text . "</b>: " . $count . "<br>";
    }
}