<?

namespace Bitrix\Sender;

use Bitrix\Main;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\Type;
use Bitrix\Sender\Dispatch\MethodSchedule;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\Model;

$_SERVER['DOCUMENT_ROOT'] = __DIR__."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

echo "Start";
lg("===arDateFilter");
$chainDb = MailingChainTable::getList(array(
    'select' => array(
        'ID', 'LAST_EXECUTED', 'POSTING_ID',
        'MONTHS_OF_YEAR', 'DAYS_OF_MONTH', 'DAYS_OF_WEEK', 'TIMES_OF_DAY'
    ),
    'filter' => array(
        '=MAILING.ACTIVE' => 'Y',
        //'=STATUS' => array(static::STATUS_NEW, static::STATUS_PAUSE),
    ),
));
d($chainDb);
while($arMailingChain = $chainDb->fetch())
    lg("123123");