<?php
include 'files.php';

class valite
{	
	public function setImage($Image)
	{
		//载入图片路径
		$this->ImagePath = $Image;
	}
	
	public function setZimu($zimu)
	{
		//设置字库
		$this->zimu = $zimu;
	}

	//图片二值化
	public function getHec()
	{
		//得到像素大小，RGB值
		$res = imagecreatefromjpeg($this->ImagePath);
		$size = getimagesize($this->ImagePath);
		$data = array();
		for($i = 0; $i < $size[1]; ++$i)
		{
			for($j = 0; $j < $size[0]; ++$j)
			{
				$rgb = imagecolorat($res,$j,$i);
				$rgbarray = imagecolorsforindex($res, $rgb);
				//计算灰度，并作出计算
				$gray = $rgbarray['red']*0.3 + $rgbarray['green'] * 0.59 + $rgbarray['blue'] * 0.11;
				if($gray < 129)
				{
					$data[$i][$j]=1;
				}else{
					$data[$i][$j]=0;
				}
			}
		}
		$this->DataArray = $data;
		$this->ImageSize = $size;
		
		//去除噪点	
		$this->filterInfo();
		
		//返回二值化后数据
		return $this->DataArray;
	}

	
	//去噪
	public function filterInfo()
	{
		for($i=0; $i < $this->ImageSize[1]; $i++)
		{
			for($j=0; $j < $this->ImageSize[0]; $j++)
			{
				$num = 0;
				if($this->DataArray[$i][$j] == 1)
				{
					// 左
					if(isset($this->DataArray[$i-1][$j])){
						$num = $num + $this->DataArray[$i-1][$j];
					}
					// 右
					if(isset($this->DataArray[$i+1][$j])){
						$num = $num + $this->DataArray[$i+1][$j];
					}
					// 上
					if(isset($this->DataArray[$i][$j-1])){
						$num = $num + $this->DataArray[$i][$j-1];
					}
					// 下
					if(isset($this->DataArray[$i][$j+1])){
						$num = $num + $this->DataArray[$i][$j+1];
					}
					// 左上
					if(isset($this->DataArray[$i-1][$j-1])){
						$num = $num + $this->DataArray[$i-1][$j-1];
					}
					// 左下
					if(isset($this->DataArray[$i-1][$j+1])){
						$num = $num + $this->DataArray[$i-1][$j+1];
					}
					// 右上
					if(isset($this->DataArray[$i+1][$j-1])){
						$num = $num + $this->DataArray[$i+1][$j-1];
					}
					// 右上
					if(isset($this->DataArray[$i+1][$j+1])){
						$num = $num + $this->DataArray[$i+1][$j+1];
					}
				}
				//孤立噪点，去除
				if($num < 2){
					$this->DataArray[$i][$j] = 0;
				}
			}
		}
	}
	
	
	//分割字符
	public function segData()
	{
		
		$data=$this->getHec();
		
		//第一个非零行
		$zeroRow=-1;
		for($i=0;$i<$this->ImageSize[1];$i++)
		{
			for ($j=0;$j<$this->ImageSize[0];$j++)
			{
				if($data[$i][$j]==1)
				{
					$zeroRow = $i;
					break;
				}
			}
			if($zeroRow==$i)break;
		}


		//记录非0列
		$zeroLine=array();
		$oneLine=array();

		for ($j=0;$j<$this->ImageSize[0];$j++)
		{
			for($i=0;$i<$this->ImageSize[1];$i++)
			{
				if($data[$i][$j]==1)
				{
					array_push($oneLine,$j);
					break;
				}
			}
		}
		
		
		//分割列
		for ($i=0;$i<count($oneLine);$i++)
		{
			if($i==0 or $i==count($oneLine)-1)
			{
				array_push($zeroLine,$oneLine[$i]);
			}
			elseif ($oneLine[$i]-$oneLine[$i-1]!=1 or $oneLine[$i+1]-$oneLine[$i]!=1)
			{
				array_push($zeroLine,$oneLine[$i]);
			}
		}

		//字符粘连处理
		
		//字符个数
		$num=count($zeroLine)/2;
		for ($i=0;$i<$num;$i++)
		{
			if($zeroLine[2*$i+1]-$zeroLine[2*$i]>13)
			{
				//分割字符，默认10列
				$seg = array ($zeroLine[2*$i]+9,$zeroLine[2*$i]+10); 
				//插入
				array_splice($zeroLine,2*$i+1,0,$seg);
			}
			
		}
	
		//分割字符
		$segData=array();
		for($i=0;$i<count($zeroLine)/2;$i++)
		{
			$segData[$i]='';
			for($j=$zeroRow;$j<$zeroRow+14;$j++)
			{
				for($k=$zeroLine[2*$i];$k<$zeroLine[2*$i+1]+1;$k++)
				{
					$segData[$i] .=strval($data[$j][$k]);
				}
			}
		}
		//返回分割字符
		return $segData;
	}	
	
	//输出结果
	public function getResult()
	{
		$result = '';
		$seg = $this->segData();
		
		for ($i=0;$i<count($seg);$i++)
		{
            //判断最符合的一个一个字模
			$maxPercent = 0;
			$maxValue='';
			foreach ($this->zimu as $key => $value)
			{
				similar_text($seg[$i],$key,$precent);
				if ($precent>$maxPercent)
				{
					$maxPercent = $precent;
					$maxValue = $value;
				}
			}
			$result .= $maxValue;
		}
		
		return $result;
		
	}
	protected $ImagePath;
	protected $DataArray;
	protected $ImageSize;
	protected $data;
	protected $Keys;
	protected $NumStringArray;
}

function increaseZimu($path,$zimu)
	{
		$valid = new Valite();
		$valid->setImage($path);
		$valid->setZimu($zimu);	
		$seg=$valid->segData();
		$value=$valid->getResult();
		
		$new=array();
		
		for($i = 0;$i<count($seg);$i++)
		{
			$new[$seg[$i]] = substr($value, $i,1);
		}
		$zimu=array_merge($zimu,$new);
//		var_dump($new);
		
		return $new;		
	}
//	$zimu=increasezimu("img/1230.jpg",$zimu);
/*	$fp = fopen('a.php', 'a+b');
	fwrite($fp, var_export($zimu, true));
	fclose($fp);*/
	

/*for($i=0;$i<38;$i++)
{
	$zimu=increasezimu("ture/{$i}.jpg",$zimu);
}


	$fp = fopen('a.php', 'a+b');
	fwrite($fp, var_export($zimu, true));
	fclose($fp);*/
	
/*$valid = new Valite();
for($i=0;$i<100;$i++)
{

$valid->setImage("upload/{$i}.jpg");
$valid->setzimu($zimu);	
$result=$valid->getResult();
rename("img/{$i}.jpg", "img/{$result}.jpg");
}*/