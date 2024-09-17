<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
?>

    ИНН <input name="inn" maxlength="12" onkeyup="dadataSend(this.value)" value="2309070991"><br>
    Наименование <input name="nameOrganisation" id="nameOrganisation"><br>
    Адрес <input name="address" id="address" maxlength="212" style="width:600px;"><br>



<script>
    function dadataSend(inn){
        var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";
        var token = "bec521a96562bb742bdb801eb6dfce20c09cd5ff";
        var query = inn;

        var options = {
            method: "POST",
            mode: "cors",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": "Token " + token
            },
            body: JSON.stringify({query: query})
        }

        fetch(url, options)
            .then(response => response.json())
            .then(result => {
                console.log(result.suggestions[0])
                document.getElementById('nameOrganisation').value = result.suggestions[0].value;
                document.getElementById('address').value = result.suggestions[0].data.address.value;
            })
            .catch(error => {
                console.log("error", error)
                document.getElementById('nameOrganisation').value = "";
            });
    }
</script>

<?php
if(isset($_POST["inn"]) && $_POST["inn"] > 100000000 && $_POST["inn"] < 1000000000000) {
    // URL для запроса
    $url = 'http://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';

    // Данные, которые будут отправлены в запросе
    $data = [
        "query" => $_POST["inn"]
    ];

    // Инициализация cURL
    $ch = curl_init($url);

    // Установка необходимых параметров cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Вернуть ответ как строку
    curl_setopt($ch, CURLOPT_POST, true); // Установить метод POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Token bec521a96562bb742bdb801eb6dfce20c09cd5ff'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Установка данных запроса

    // Выполнение запроса
    $response = curl_exec($ch);

    // Проверка на ошибки
    if ($response === false) {
        // Вывод ошибок cURL
        echo 'Curl error: ' . curl_error($ch);
    } else {
        // Вывод результата
        echo 'Response: OK<br>';
    }

    // Закрытие cURL
    curl_close($ch);

    $response = json_decode($response, true);

    //d($response);
    if(count($response["suggestions"]) == 0){
        echo "Организация не найдена";
    }
    elseif(count($response["suggestions"]) == 1){
        echo "<h2>Наименование организации: ". $response["suggestions"][0]["value"]."</h2>";
        ?>
        <table>
            <tr><td>КПП<td><?=$response["suggestions"][0]["data"]["kpp"]?></td>
            <tr><td>Юр. адрес<td><?=$response["suggestions"][0]["data"]["address"]["value"]?></td>
        </table>
        <? d($response["suggestions"][0]);
    }
    else
        echo "Нужно ввести еще и КПП";
    //d($response);
}