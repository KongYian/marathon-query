<?php
$verify_code_url = "http://www.runchina.org.cn/template/default/public/js/securimage/securimage_show.php";
$query_url = "http://www.runchina.org.cn/portal.php?mod=score&ac=personal";
$cookie_file = "../tmp.cookie";
showAuthcode($verify_code_url,$cookie_file);
$handle = fopen($cookie_file,'r');
$line= '';
while (!feof($handle))
{
    $line .= fgets($handle);
}
preg_match("/PHPSESSID(?<right>.*)/",$line,$sessionArr);
fclose($handle);
$session = trimall($sessionArr['right'],' ');
$sessionString = "PHPSESSID=".$session.';';
$res = curlLogin($query_url,$cookie_file,$sessionString);

preg_match_all('/Set-Cookie:(.*);/iU',$res,$out);
$tmp = implode(';',$out[1]);
$cookieString = $sessionString.$tmp;

echo json_encode(['data'=>$cookieString]);
exit;

function trimall($str)//删除空格
{
    $oldchar=array(" ","　","\t","\n","\r");
    $newchar=array("","","","","");
    return
        str_replace($oldchar,$newchar,$str);
}

function showAuthcode( $authcode_url,$cookieFile)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $authcode_url);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36');
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $img = curl_exec($curl);
    curl_close($curl);
    $fp = fopen("../image/verifyCode.jpg","w");
    fwrite($fp,$img);
    fclose($fp);
}

function curlLogin($url,$cookiefile,$session)
{
    $headers = [
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Accept-Encoding:gzip, deflate",
        "Accept-Language:zh-CN,zh;q=0.9",
        "Connection:keep-alive",
        "Cookie:".$session,
        "Host:www.runchina.org.cn",
        "Upgrade-Insecure-Requests:1",
        "User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "gzip, deflate, sdch");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.221 Safari/537.36 SE 2.X MetaSr 1.0");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
}
