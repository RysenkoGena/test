<?php
/*$data = file_get_contents('php://input');
$data = json_decode($data, true);
file_put_contents(__DIR__ . '/message.txt', print_r($data, true), FILE_APPEND);*/

$dataInput = file_get_contents('php://input');
$data = json_decode($dataInput, false);
if($data) {
    file_put_contents(__DIR__ . '/fullencode.txt', print_r($data, true), FILE_APPEND);
    file_put_contents(__DIR__ . '/telegram/messages/' . $data->update_id . $data->message->from->first_name . '.txt', print_r($dataInput, true));
}

if (isset($data->message)) {    // получим id чата
    $chat_id = $data->message->from->id;     // проверим что это текстовое сообщение
    if (isset($data->message->text)) {        // проверим что это старт бота
        if ($data->message->text == "/start") {            // направим запрос в метод старта бота
            //startBot($chat_id);
        }
        file_put_contents(__DIR__ . '/message.txt', print_r($data, true), FILE_APPEND);
        //file_put_contents(__DIR__ . '/telegram/messages/'.date("d.m H:i:s ").$data->message->from->last_name.'.txt', print_r($data, true), FILE_APPEND);
    } elseif (isset($data->message->photo)) {
        // направим на сохранение photo
        //savePhoto($chat_id, $data->message->photo);
    }
} // если это нажатие по кнопке
elseif (isset($data->callback_query)) {    // получим id чата
    $chat_id = $data->callback_query->from->id;    // получим callBackQuery_id
    $cbq_id = $data->callback_query->id;    // получим переданное значение в кнопке
    $c_data = $data->callback_query->data;
    file_put_contents(__DIR__ . '/callback.txt', print_r($data, true), FILE_APPEND);
    // спарсим значения
    //$params = explode("|", $c_data);
    //if($params[1] === "getPrice") {
    //    price($chat_id, $cbq_id, $params);
    //} elseif($params[1] === "getDemo") {
    //    demo($chat_id, $cbq_id, $params);
    //}
}
