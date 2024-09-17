<?php

function checkText($text, $lang = 'ru,en', $options = 0){
    $url = 'https://speller.yandex.net/services/spellservice.json/checkText';
    $params = array(
        'text' => $text,
        'lang' => $lang,
        'options' => $options
    );
    $query = http_build_query($params);
    $url = $url . '?' . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    $correctedText = $text;
    foreach ($result as $wordData) {
        $word = $wordData['word'];
        $correctedWord = $wordData['s'][0]; // Берём первый вариант исправления
        $correctedText = str_replace($word, $correctedWord, $correctedText);
    }
    return $correctedText;
}


$text = 'лублю акамулятор в клярэ и я';
echo $text;



$result = checkText($text);


//$correctedText = $text;
echo "<pre>".$result;
//print_r($result);
echo "</pre>";

