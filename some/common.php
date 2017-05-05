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
