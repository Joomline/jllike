<?php
/**
 * jllikepro
 *
 * @version 2.4.4
 * @author Vadim Kunicin (vadim@joomline.ru)
 * @copyright (C) 2010-2013 by Vadim Kunicin (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

$variant = isset($_REQUEST['variant']) ? $_REQUEST['variant'] : '';
$typeGet = isset($_REQUEST['tpget']) ? $_REQUEST['tpget'] : '';
$curl =    isset($_REQUEST['curl']) ? $_REQUEST['curl'] : '';

switch ($variant)
{
    case 'ya':
        if ($typeGet == 0) {
            $request = @file_get_contents($curl);
        }
        else
        {
            $ch = curl_init($curl);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru-RU; rv:1.9.2) Gecko/20100115 AdCentriaIM/1.7 Firefox/3.6 GTB6 (.NET CLR 3.5.30729)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $request = curl_exec($ch);
            curl_close($ch);
        }

        $tmp = array();
        $pattern = "/(.+?)Ya.Share.showCounter(([^<]+)(\d+)([^<]+))(.+?)/i";
        preg_match($pattern, $request, $tmp);

        if (isset($tmp[4][0]))
        {
            echo $tmp[4][0];
        }
        die;

    case 'gp':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $curl . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $curl_results = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($curl_results, true);
        echo (int)$json[0]['result']['metadata']['globalCounts']['count'];
        die;
}
?>