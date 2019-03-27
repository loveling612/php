<?
function getWYmusicAdd(){
	$id = $_GET["id"];
	$url = "http://music.163.com/api/song/detail/?id=" . $id . "&ids=%5B" . $id . "%5D";
	$refer = "http://music.163.com/";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER, $refer);
	$output = curl_exec($ch);
	curl_close($ch);
	$output_arr = json_decode($output, true);
	$mp3_url = $output_arr["songs"][0]["mp3Url"];
	header('Content-Type:audio/mp3');
	header("Location:".$mp3_url);
}
function getBrowserVersion()
{
    return getBrowser() . getBrowserVer();
}

function getBrowser()
{
    $agent = $_SERVER["HTTP_USER_AGENT"];
    if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) //ie11判断
        return "ie";
    else if (strpos($agent, 'Firefox') !== false)
        return "firefox";
    else if (strpos($agent, 'Chrome') !== false)
        return "chrome";
    else if (strpos($agent, 'Opera') !== false)
        return 'opera';
    else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false)
        return 'safari';
    else
        return 'unknown';
}

function getBrowserVer()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {    //当浏览器没有发送访问者的信息的时候
        return 'unknow';
    }
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs))
        return $regs[1];
    elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs))
        return $regs[1];
    elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs))
        return $regs[1];
    elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs))
        return $regs[1];
    elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs))
        return $regs[1];
    else
        return 'unknow';
}
function getIp() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return isIp($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $ip;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return isIp($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
    } else {
        return isIp($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }
}

function isIp($str) {
    $ip = explode('.', $str);
    for ($i = 0; $i < count($ip); $i++) {
        if ($ip[$i] > 255) {
            return false;
        }
    }

    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
}
/**
 * excel导出（支持多个sheet）和图片
 * @author Red
 * @date 2016年11月15日10:37:57
 * @param $list
 * @param $excelFieldsZHCN
 * @param $excelFileName
 * @param $sheetTitle
 */
function exportExcels($list, $excelFieldsZHCN, $excelFileName, $sheetTitle)
{
    $excelFileName = iconv('UTF-8', 'GBK', $excelFileName);

    $excelFileName = $excelFileName . date('YmdHi', time());
    include APP_PATH . '/Vendor/PHPExcel.php';
    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()->setCreator("Red")->setLastModifiedBy("")->setTitle('I\'m superredman')->setDescription("create by red");
    //构造excel 列名
    $index = 0;
    $ret   = array();
    foreach ($excelFieldsZHCN as $key => $value) {
        $objPHPExcel->createSheet();
        $i = 0;
        foreach ($value as $fieldName => $ZHCN) {
            $pCoordinate = \PHPExcel_Cell::stringFromColumnIndex($i);
            $objPHPExcel->setActiveSheetIndex($index)->setCellValue($pCoordinate . '1', $value[$fieldName]);
            $ret[$i] = $fieldName;
            $i++;
        }
        $row = 2;//EXCEL 行索引 从第二行自增
        if ($list[$key]) {
            foreach ($list[$key] as $item) {
                $i = 0;
                foreach ($ret as $field) {

                    $pCoordinate = \PHPExcel_Cell::stringFromColumnIndex($i);
                    if (is_array($item[$field]) && $item[$field]['img']) {
                        /*实例化插入图片类*/
                        $objDrawing = new PHPExcel_Worksheet_Drawing();
                        /*设置图片路径 切记：只能是本地图片*/
                        $objDrawing->setPath($item[$field]['path']);
                        /*设置图片高度*/
                        $objDrawing->setHeight($item[$field]['height']);
                        $objDrawing->setWidth($item[$field]['width']);
                        //图片位置
                        $objDrawing->setOffsetX(5);
                        $objDrawing->setOffsetY(5);
                        /*设置图片要插入的单元格*/
                        $objDrawing->setCoordinates($pCoordinate . $row);
                        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                        //设置行高和行宽
                        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($item[$field]['width']);
                    } else {
                        $objPHPExcel->setActiveSheetIndex($index)->setCellValue($pCoordinate . $row, ' ' . strip_tags($item[$field]));//过滤html标签
                    }

                    $i++;
                }
                $row++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[$key]);
        $objPHPExcel->setActiveSheetIndex($index);
        $index++;
    }


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $excelFileName . '.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}

function curl_get_contents($url, $timeout = 10, $data = array())
{
    if (!function_exists('curl_init')) {
        throw new Zend_Exception('CURL not support');
    }

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $data && curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    if (defined('WECENTER_CURL_USERAGENT')) {
        curl_setopt($curl, CURLOPT_USERAGENT, WECENTER_CURL_USERAGENT);
    } else {
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12');
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    }

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}
/**
 * 下载文件
 * @author Red
 * @date 2016年12月12日17:08:56
 * @param $file
 */
function download_file($file)
{
    $file = str_replace('\\', '/', realpath(dirname(dirname(dirname((dirname(__FILE__)))) . '/'))) . $file;
    if (is_file($file)) {
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=" . basename($file));
        readfile($file);
        exit;
    } else {
        echo "文件不存在！";
        echo '<span><a href="javascript:history.go(-1);">◂返回上一步</a></span>';
        exit;
    }
}
/**
 * 16进制转字符串
 * @author Red
 * @date 2016年12月23日11:24:41
 * @param $hex
 * @return string
 */
function hex2str($hex)
{
    $str = '';
    $arr = str_split($hex, 2);
    foreach ($arr as $bit) {
        $str .= chr(hexdec($bit));
    }

    return $str;
}

/**
 * 字符串转16进制
 * @author Red
 * @date 2016年12月23日11:24:41
 * @param $str
 * @return string
 */
function str2hex($str)
{
    $hex = '';
    for ($i = 0, $length = mb_strlen($str); $i < $length; $i++) {
        $hex .= dechex(ord($str{$i}));
    }

    return $hex;
}

/**
 * 时间距离
 * @author Red
 * @date 2016年11月14日16:49:32
 * @param $endTime
 * @param int $starTime
 * @return string
 */
function get_deadline($endTime, $starTime = 0,$line=false)
{
    //计算天数
    $timeDiff = $endTime - ($starTime ? $starTime : time());
    $days     = intval($timeDiff / 86400);
    //计算小时数
    $remain = $timeDiff % 86400;
    $hours  = intval($remain / 3600);
    //计算分钟数
    $remain = $remain % 3600;
    $mins   = intval($remain / 60);
    if($line){
        return $days . "-" . $hours . "-" . $mins . "-";
    }
    return $days . "天" . $hours . "小时" . $mins . "分钟";
}
/**
 * 根据身份证号码算出年龄
 * @author Red
 * @date 2016年12月26日11:37:39
 * @param $idCard
 * @return float
 */
function get_age_by_id_card($idCard)
{
    $date  = strtotime(substr($idCard, 6, 8));//获得出生年月日的时间戳
    $today = strtotime('today');//获得今日的时间戳
    $diff  = floor(($today - $date) / 86400 / 365);//得到两个日期相差的大体年数
    //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
    return strtotime(substr($idCard, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
}

/**
 * 递归读取文件夹的文件列表
 *
 * 读取的目录路径可以是相对路径, 也可以是绝对路径, $file_type 为指定读取的文件后缀, 不设置则读取文件夹内所有的文件
 *
 * @param  string
 * @param  string
 * @return array
 */
function fetch_file_lists($dir, $file_type = null)
{
	if ($file_type)
	{
		if (substr($file_type, 0, 1) == '.')
		{
			$file_type = substr($file_type, 1);
		}
	}

	$base_dir = realpath($dir);
	$dir_handle = opendir($base_dir);

	$files_list = array();

	while (($file = readdir($dir_handle)) !== false)
	{
		if (substr($file, 0, 1) != '.' AND !is_dir($base_dir . '/' . $file))
		{
			if (($file_type AND H::get_file_ext($file, false) == $file_type) OR !$file_type)
			{
				$files_list[] = $base_dir . '/' . $file;
			}
		}
		else if (substr($file, 0, 1) != '.' AND is_dir($base_dir . '/' . $file))
		{
			if ($sub_dir_lists = fetch_file_lists($base_dir . '/' . $file, $file_type))
			{
				$files_list = array_merge($files_list, $sub_dir_lists);
			}
		}
	}

	return $files_list;
}

/**
 * 判断是否是合格的手机客户端
 *
 * @return boolean
 */
function is_mobile($ignore_cookie = false)
{
	if (HTTP::get_cookie('_ignore_ua_check') == 'TRUE' AND !$ignore_cookie)
	{
		return false;
	}

	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	if (preg_match('/playstation/i', $user_agent) OR preg_match('/ipad/i', $user_agent) OR preg_match('/ucweb/i', $user_agent))
	{
		return false;
	}

	if (preg_match('/iemobile/i', $user_agent) OR preg_match('/mobile\ssafari/i', $user_agent) OR preg_match('/iphone\sos/i', $user_agent) OR preg_match('/android/i', $user_agent) OR preg_match('/symbian/i', $user_agent) OR preg_match('/series40/i', $user_agent))
	{
		return true;
	}

	return false;
}


/**
 * 时间友好型提示风格化（即微博中的XXX小时前、昨天等等）
 *
 * 即微博中的 XXX 小时前、昨天等等, 时间超过 $time_limit 后返回按 out_format 的设定风格化时间戳
 *
 * @param  int
 * @param  int
 * @param  string
 * @param  array
 * @param  int
 * @return string
 */
function date_friendly($timestamp, $time_limit = 604800, $out_format = 'Y-m-d H:i', $formats = null, $time_now = null)
{
	if (get_setting('time_style') == 'N')
	{
		return date($out_format, $timestamp);
	}

	if (!$timestamp)
	{
		return false;
	}

	if ($formats == null)
	{
		$formats = array('YEAR' => AWS_APP::lang()->_t('%s 年前'), 'MONTH' => AWS_APP::lang()->_t('%s 月前'), 'DAY' => AWS_APP::lang()->_t('%s 天前'), 'HOUR' => AWS_APP::lang()->_t('%s 小时前'), 'MINUTE' => AWS_APP::lang()->_t('%s 分钟前'), 'SECOND' => AWS_APP::lang()->_t('%s 秒前'));
	}

	$time_now = $time_now == null ? time() : $time_now;
	$seconds = $time_now - $timestamp;

	if ($seconds == 0)
	{
		$seconds = 1;
	}

	if (!$time_limit OR $seconds > $time_limit)
	{
		return date($out_format, $timestamp);
	}

	$minutes = floor($seconds / 60);
	$hours = floor($minutes / 60);
	$days = floor($hours / 24);
	$months = floor($days / 30);
	$years = floor($months / 12);

	if ($years > 0)
	{
		$diffFormat = 'YEAR';
	}
	else
	{
		if ($months > 0)
		{
			$diffFormat = 'MONTH';
		}
		else
		{
			if ($days > 0)
			{
				$diffFormat = 'DAY';
			}
			else
			{
				if ($hours > 0)
				{
					$diffFormat = 'HOUR';
				}
				else
				{
					$diffFormat = ($minutes > 0) ? 'MINUTE' : 'SECOND';
				}
			}
		}
	}

	$dateDiff = null;

	switch ($diffFormat)
	{
		case 'YEAR' :
			$dateDiff = sprintf($formats[$diffFormat], $years);
			break;
		case 'MONTH' :
			$dateDiff = sprintf($formats[$diffFormat], $months);
			break;
		case 'DAY' :
			$dateDiff = sprintf($formats[$diffFormat], $days);
			break;
		case 'HOUR' :
			$dateDiff = sprintf($formats[$diffFormat], $hours);
			break;
		case 'MINUTE' :
			$dateDiff = sprintf($formats[$diffFormat], $minutes);
			break;
		case 'SECOND' :
			$dateDiff = sprintf($formats[$diffFormat], $seconds);
			break;
	}

	return $dateDiff;
}
/**
 * 递归创建目录
 *
 * 与 mkdir 不同之处在于支持一次性多级创建, 比如 /dir/sub/dir/
 *
 * @param  string
 * @param  int
 * @return boolean
 */
function make_dir($dir, $permission = 0777)
{
	$dir = rtrim($dir, '/') . '/';

	if (is_dir($dir))
	{
		return TRUE;
	}

	if (! make_dir(dirname($dir), $permission))
	{
		return FALSE;
	}

	return @mkdir($dir, $permission);
}
$array1 = array(
            0=>array('id'=>8,'name'=>'Apple','age'=> 18),
            1=>array('id'=>8,'name'=>'Bed','age'=>17),
            2=>array('id'=>5,'name'=>'Cos','age'=>16),
            3=>array('id'=>5,'name'=>'Cos','age'=>14)
        );
function sortArrByManyField(){
    $args = func_get_args(); // 获取函数的参数的数组
    if(empty($args)){
	return null;
    }
    $arr = array_shift($args);
    if(!is_array($arr)){
	throw new Exception("第一个参数不为数组");
    }
    foreach($args as $key => $field){
	if(is_string($field)){
	    $temp = array();
	    foreach($arr as $index=> $val){
		$temp[$index] = $val[$field];
	    }
	    $args[$key] = $temp;
	}
    }
    $args[] = &$arr;//引用值
    call_user_func_array('array_multisort',$args);
    return array_pop($args);
}
$arr = sortArrByManyField($array1,'id',SORT_ASC,'name',SORT_ASC,'age',SORT_DESC);
