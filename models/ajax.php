<?php

 /**
 * @package plugin mail sync. Ya
 * @author Artem Zhukov (artem@joomline.ru)
 * @version 1.2
 * @copyright (C) 2008-2012 by JoomLine (http://www.joomline.net)
 * @license JoomLine: http://joomline.net/licenzija-joomline.html
 *
*/
global $rez;

$variant = $_POST['variant'];
	$curl = $_POST['curl'];
    $request = file_get_contents($curl);
	/*$ch = curl_init($curl);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru-RU; rv:1.9.2) Gecko/20100115 AdCentriaIM/1.7 Firefox/3.6 GTB6 (.NET CLR 3.5.30729)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$request = curl_exec($ch);
	curl_close($ch);*/
		
		
		
        $tmp = array();
		switch ($variant) {
			case 'od': $pattern = "/^ODKL.updateCountOC\('[\d\w]+','(\d+)','(\d+)','(\d+)'\);$/i";break;
			case 'gp': $pattern = "/\<div id\=\"aggregateCount\" class\=\"V1\"\>(\d+)\<\/div\>/i";break;			
		}		
		preg_match($pattern,$request,$tmp);
		if (isset($tmp[1])) {echo $tmp[1];}

	
?>

