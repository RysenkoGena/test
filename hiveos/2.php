<?PHP
    require_once(dirname(__FILE__).'/src/jpgraph.php');
    require_once(dirname(__FILE__).'/src/jpgraph_line.php');
    if(isset($_GET["rig"]) && $_GET["rig"] != ""){
        $rig = $_GET["rig"];
        $sqlText = "rig = '".$rig."' AND ";
    }
    else 
    $sqlText = "";

    $conn = new mysqli("localhost", "useryugkabel", "UIV1%3)}eCc}C+v", "hiveos");
    if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
    //else echo "Подключеие к базе OK";
        //for($i=0; $i < count($X); $i++){
        
    $hr = array(); $time = time();
    for($i=1440; $i > 0; $i--)    $hr[date("H:i", $time+=60)] = [];

          $query = "SELECT rig, hashrate, date FROM hashrate WHERE ".$sqlText."date > NOW() - INTERVAL 1 DAY;";
          
          //echo $query."<br>";
          $result = $conn->query($query);
          //$hr = array();
          while($res = $result -> fetch_assoc()){
            $hr[date("H:i", strtotime($res["date"]))][] = $res["hashrate"];
            //print_r(date("g.i", strtotime($res["date"])));
            //$hr[date("H.i", strtotime($res["date"]))][] = $res["hashrate"]." ".$res["date"];
            //$summ += $res["hashrate"];
            //break;
          }
          //debug($hr);
          $day = array();
          foreach($hr as $hr_m => $m){
            foreach($m as $hr_rig){
              $day[$hr_m]  += $hr_rig;
            }
          }
          //debug($day);
         
    
        //}
        //file_put_contents(__DIR__ . '/requests.txt', print_r($query, true)."\n", FILE_APPEND);
      //echo $query;
    $conn->close();


// Создадим немного данных для визуализации:

$i =  0; $y = 0;
foreach($day as $da => $hr){
    $i++;
    if($i < 60){
        continue;
    }
    else $i = 0;
    
    $xdata[] =  $y;
    $y++;
    $ydata[] = $hr;
}
//$ydata = $day;
//Массив значений абсцисс опционален, его можно не задавать
//$xdata = array(0, 1, 2, 3, 4, 5, 6);

/*
        Создаем экземпляр класса графика, задаем параметры
        изображения: ширина, высота, название файла в кеше,
        время хранения изображения в кеше, указываем, выводить
        ли изображение при вызове функции Stroke (true)
        или только создать и хранить в кеше (false):
*/
$graph = new Graph(1600, 300, 'auto', 10, true);
$graph->SetScale('textlin');// Указываем, какие оси использовать:
$lineplot = new LinePlot($ydata, $xdata);
$lineplot->SetColor('forestgreen');// Задаём цвет кривой
$graph->Add($lineplot);// Присоединяем кривую к графику:
$graph->title->Set('Простой график');// Даем графику имя:

//Если планируете использовать кириллицу, то необходимо использовать TTF-шрифты, которые её поддерживают,например arial.
$graph->title->SetFont(FF_ARIAL, FS_NORMAL);
$graph->xaxis->title->SetFont(FF_VERDANA, FS_ITALIC);
$graph->yaxis->title->SetFont(FF_TIMES, FS_BOLD);


$graph->xaxis->title->Set('Время');// Назовем оси:
$graph->yaxis->title->Set('HR');

$graph->xaxis->SetColor('#СС0000');// Выделим оси цветом:
$graph->yaxis->SetColor('#СС0000');
$lineplot->SetWeight(3);// Зададим толщину кривой:
$lineplot->mark->SetType(MARK_FILLEDCIRCLE);// Обозначим точки звездочками, задав тип маркера:
$lineplot->value->Show();// Выведем значения над каждой из точек:
$graph->SetBackgroundGradient('ivory', 'orange');// Фон графика зальем градиентом:
$graph->SetShadow(4);// Придадим графику тень:
$graph->Stroke();