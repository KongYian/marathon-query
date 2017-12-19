
<?php
/**
 * Created by PhpStorm.
 * User: blue
 * Date: 2017/12/15
 * Time: 9:42
 */

require_once 'simple_html_dom.php';

$idnum = $_POST['idnum'];
$name = $_POST['name'];
$code = $_POST['code'];
$cookie = $_POST['cookie'];

$query_url = "http://www.runchina.org.cn/portal.php?mod=score&ac=personal";

$params = [
    'idnum'=>$idnum,
    'name'=>$name,
    'captcha_code'=>$code
];

$hostName = 'localhost';
$dbName = 'demo';
$userName = 'root';
$password = '';
$charset = 'utf8';

$mysqli = new mysqli($hostName,$userName,$password,$dbName);
$mysqli->set_charset($charset);
$mysqli->select_db($dbName);


$https = query($query_url,$params,$cookie);
$htmlDom = str_get_html($https);

$str = "SELECT `result` FROM `marathon` WHERE `name` = ? and `idnum` = ?";
$stmt = $mysqli->prepare($str);
$stmt->bind_param('ss',$name,$idnum);
$stmt->execute();
$stmt->bind_result($result);
$stmt->fetch();
$stmt->close();

if($result){
    echo $result;
    exit;
}

$out = [];
foreach($htmlDom->find('.myScore tbody tr') as $kk => $e) {
    if($kk != 0){
        foreach ($e->children as $k => $child) {
            switch ($k){
                case 0: $out[$kk]['date'] = $child->plaintext ;break;
                case 1: $out[$kk]['name'] = trimall($child->plaintext) ;break;
                case 2: $out[$kk]['type'] = trimall($child->plaintext) ;break;
                case 3:
                    $out[$kk]['raceNetTime'] = $child->plaintext ;
                    if(strpos($out[$kk]['raceNetTime'],'PB') !== false){
                        $out[$kk]['pbColor'] = 'pink';
                    }else{
                        $out[$kk]['pbColor'] = '';
                    }
                    break;
                case 4:$out[$kk]['raceTrueTime'] = $child->plaintext ;break;
//                case 5: $out[$kk]['raceDetailTime'] = trimall($child->innertext) ;break;
            }
        }
    }
}
$htmlDom->clear();
unset($htmlDom);
if($out){
    $out = [
        'status'=>1,
        'data'=>$out
    ];
    $str = "INSERT INTO `marathon`(`name`,`idnum`,`result`) VALUES (?,?,?)";
    $stmt = $mysqli->prepare($str);
    $stmt->bind_param('sss',$name,$idnum,json_encode($out));
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo json_encode($out);
}else{
    $mysqli->close();
    echo json_encode([
        'status'=>0,
        'data'=>[]
    ]);
}



function query($query_url,$params,$cookie){
    $headers = [
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Accept-Encoding:gzip, deflate",
        "Accept-Language:zh-CN,zh;q=0.9",
        "Connection:keep-alive",
        "Cookie:".$cookie,
        "Host:www.runchina.org.cn",
        "Upgrade-Insecure-Requests:1",
        "User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $query_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp;
}

function trimall($str)//删除空格
{
    $oldchar=array(" ","　","\t","\n","\r");
    $newchar=array("","","","","");
    return
        str_replace($oldchar,$newchar,$str);
}