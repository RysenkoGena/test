<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>TEST_BOT</title>

</head>
<body>
<?php
echo "Бот запущен!";

require_once 'vendor/autoload.php';

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

$bot = new Client('6807258724:AAFSTX-76pL_Pt7Qo-YsDWKLaxk7_HZ_Dp0');

// Обработка команды /start
$bot->command('start', function (Update $update) use ($bot) {
    $chatId = $update->getMessage()->getChat()->getId();
    $bot->sendMessage($chatId, 'Привет! Я бот, который может показать вам список пользователей с номерами телефонов в беседе. Напишите /listusers, чтобы получить этот список.');
});

// Обработка команды /listusers
$bot->command('listusers', function (Update $update) use ($bot) {
    $chatId = $update->getMessage()->getChat()->getId();
    $userId = $update->getMessage()->getFrom()->getId();
    $members = $bot->getChatMembers($chatId);

    $userList = "Пользователи с номером телефона в беседе:\n";
    foreach ($members as $member) {
        $user = $member->getUser();
        if ($user->getPhoneNumber() !== null) {
            $userList .= $user->getFirstName() . ' ' . $user->getLastName() . ' - ' . $user->getPhoneNumber() . "\n";
        }
    }

    $bot->sendMessage($userId, $userList);
});

$bot->run();
?>

</body>
</html>
