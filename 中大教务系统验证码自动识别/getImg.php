<?php
function getImg($url = "http://uems.sysu.edu.cn/jwxt/jcaptcha", $filename ='upload/image.jpg')
{
 //去除URL连接上面可能的引号
  //$url = preg_replace( '/(?:^['"]+|['"/]+$)/', '', $url );
  $hander = curl_init();
  $fp = fopen($filename,'wb');
  curl_setopt($hander,CURLOPT_URL,$url);
  curl_setopt($hander,CURLOPT_FILE,$fp);
  curl_setopt($hander,CURLOPT_HEADER,0);
  curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
  //curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
  curl_setopt($hander,CURLOPT_TIMEOUT,60);
  curl_exec($hander);
  curl_close($hander);
  fclose($fp);
  Return true;
}
/*for($i=0;$i<100;$i++){
	getImg("http://uems.sysu.edu.cn/jwxt/jcaptcha","upload/{$i}.jpg");
}*/