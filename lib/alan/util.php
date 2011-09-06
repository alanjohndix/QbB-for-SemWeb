<?php

// source: http://www.webcheatsheet.com/PHP/get_current_page_url.php
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 //echo "<!-- curPageURL: $pageURL -->\n";
 return $pageURL;
}

function get_named_table_col($table,$colname,$includeblank=false,$default=false) {
	$result = array();
	foreach ( $table as $row ) {
		if ( array_key_exists( $colname, $row ) ) {
			$result[] = $row[$colname];
		} elseif ( $includeblank ) {
			$result[] = $default;
		}
	}
	return $result;
}

function startsWith($str,$prefix) {
	return substr($str,0,strlen($prefix)) == $prefix;
}

// based on: http://www.jonasjohn.de/snippets/php/post-request.htm
function PostRequest($url, $referer, $data) {
 
    // parse the given URL
    $url = parse_url($url);
    if ($url['scheme'] != 'http') { 
        die('Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
 
    // open a socket connection on port 80
    $fp = fsockopen($host, 80);
 
    // send the request headers:
    fputs($fp, "POST $path HTTP/1.1\r\n");
    fputs($fp, "Host: $host\r\n");
    fputs($fp, "Referer: $referer\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: ". strlen($data) ."\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $data);
	
	echo "<!-- telnet $host 80 \n";
	echo "POST $path HTTP/1.1\r\n";
    echo "Host: $host\r\n";
    echo "Referer: $referer\r\n";
    echo "Content-type: application/x-www-form-urlencoded\r\n";
    echo "Content-length: ". strlen($data) ."\r\n";
    echo "Connection: close\r\n\r\n";
    echo $data;
	echo "\n===========================\n-->\n";

 
    $result = ''; 
    while(!feof($fp)) {
        // receive the results of the request
        $result .= fgets($fp, 128);
    }
 
    // close the socket connection:
    fclose($fp);
 
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
	
	if ( stripos( $header, 'Transfer-Encoding: chunked' ) !== false ) {
		$content = transfer_encoding_unchunk($content);
	}
 
    // return as array:
    return array($header, $content);
}

function transfer_encoding_unchunk($chunked) {
	$unchunked = "";
	while ( $chunked ) {
    	$parts = explode("\r\n", $chunked, 2);
		$len = hexdec($parts[0]);
		if ( $len == 0 ) break;
		$unchunked .= substr($parts[1],0,$len);
		$chunked = substr($parts[1],$len);
	}
	return $unchunked;
}


function table_select_column($table,$colname,$include_empty = false) {
	$result = array();
	foreach ( $table as $row ) {
		if ( array_key_exists($colname,$row) ) {
			$result[] = $row[$colname];
		} elseif ( $include_empty ) {
			$result[] = false;
		}
	}
	return $result;
}

function array_value_counts($arr) {
	$counts = array();
	foreach( $arr as $value ) {
		$counts[$value]++;
	}
	return $counts;
}

function array_extract_prefixed($arr,$prefix) {
	$res = array();
	foreach ( $arr as $key=>$value ) {
		if ( starts_with( $key, $prefix) ) {
			$pkey = strip_prefix( $key, $prefix);
			$res[$pkey] = $value;
		}
	}
	return $res;
}

function array_clear_prefixed($arr,$prefix) {
	$res = $arr;
	foreach ( $arr as $key=>$value ) {
		if ( starts_with( $key, $prefix) ) {
			unset($res[$key]);
		}
	}
	return $res;
}

function array_add_prefixed($arr,$prefix,$newarr) {
	$res = $arr;
	foreach ( $newarr as $key=>$value ) {
		$res[$prefix.$key] = $value;
	}
	return $res;
}

function starts_with($str,$prefix)
{
	//echo "<!-- starts_with($str,$prefix) -->\n";
	$len = strlen($str);
	$prelen = strlen($prefix);
	if ( $prelen > $len ) return false;
	$res = substr($str,0,$prelen) == $prefix;
	//echo "<!--     = $res -->\n";
	return $res;
}
function ends_with($str,$suffix)
{
	$len = strlen($str);
	$suflen = strlen($suffix);
	if ( $suflen > $len ) return false;
	return substr($str,$len-$suflen) == $suffix;
}
function strip_prefix($str,$prefix)
{
	$len = strlen($str);
	$prelen = strlen($prefix);
	if ( $prelen > $len ) return false;
	return substr($str,$prelen);
}
function strip_suffix($str,$suffix)
{
	$len = strlen($str);
	$suflen = strlen($suffix);
	if ( $suflen > $len ) return false;
	return substr($str,0,$len-$suflen);
}

function nice_value_to_split_range($lower,$upper) {
	if ( $lower * $upper <= 0 ) return 0;  // either is zero, or one pos and one neg
	if ( $lower == $upper ) return $lower;
	if ( $lower > 0 ) {
		$sign = 1;
	} else {
		$sign = -1;
		$lower = -$lower;
		$upper = -$upper;
	}
	if ( $lower > $upper ) {
		$tmp = $lower;
		$lower = $upper;
		$upper = $lower;
	}
	$s_lower = "".$lower;
	$s_upper = "".$upper;
	$p_lower = strpos($s_lower,'.');
	if ( $p_lower === false ) {
		$s_lower .= ".0";
		$p_lower = strpos($s_lower,'.');
	}
	$p_upper = strpos($s_upper,'.');
	if ( $p_upper === false ) {
		$s_upper .= ".0";
		$p_upper = strpos($s_upper,'.');
	}
	
	//echo "comparing [$s_lower]  [$s_upper] <br>\n";
	
	if ( $p_lower < $p_upper ) {
		//echo "lower is shorter \n";
		$val = str_pad("1",$p_lower+1,"0",STR_PAD_RIGHT);
		return $sign * $val;
	}
	
	// make them ther same length
	$l_lower = strlen($s_lower);
	$l_upper = strlen($s_upper);
	if ( $l_lower < $l_upper ) {
		$s_lower = str_pad($s_lower,$l_upper,"0",STR_PAD_RIGHT);
		$len = $l_upper;
	} elseif ( $l_lower > $l_upper ) {
		$s_upper = str_pad($s_upper,$l_lower,"0",STR_PAD_RIGHT);
		$len = $l_lower;
	} else {
		$len = $l_upper;
	}
	
	
	
	$val = "";
	for( $i=0; $i<$len && $s_lower{$i}==$s_upper{$i} ; $i++ ) {
		$val .= $s_lower{$i};
	}
	
	if ( $i == $len ) { // identical
		//echo "identical \n";
		return $sign*$val;
	}
	
	// now hit a different digit
	$lower_rest_zero = ( substr($s_lower,$i+1) == 0 ) ;
	$lower_digit = $s_lower{$i};
	$upper_digit = $s_upper{$i};
	
	//echo "comparing: lower_digit=$lower_digit  upper_digit=$upper_digit  lower_rest_zero=$lower_rest_zero   \n";
	$digit = -1;
	if ( $lower_rest_zero ) {
		if ( $lower_digit==0 ) {
			$digit = '0';
		} elseif ( $lower_digit==1 ) {
			$digit = '1';
		} elseif ( $lower_digit==2 ) {
			$digit = '2';
		} elseif ( $lower_digit==5 ) {
			$digit = '5';
		}
	} else {
		if ( $lower_digit==0 ) {
			$digit = '1';
		} elseif ( $lower_digit==1 ) {
			$digit = '2';
		} elseif ( $lower_digit<5 && $upper_digit>=5 ) {
			$digit = '5';
		}
	}
	if ( $digit<0 ) {
		$digit = ceil( ($lower_digit+$upper_digit) / 2 );
	}
	$val .= $digit;
	if ( $i < $p_lower ) { // before decimal point need to pad with zeros
		$val = str_pad($val,$p_lower,"0",STR_PAD_RIGHT);
	}// after point, no need to pad
	return $sign * $val;
}


function get_fields_and_types($rows) {
	$fields = array();
	foreach( $rows as $row ) {
		foreach( $row as $name => $value ) {
			if ( trim($value) =='' ) {
				$fields[$name]['BLANK'] ++;
			} elseif ( is_numeric($value) ) {
				$fields[$name]['NUMBER'] ++;
			} elseif ( ($timestamp = strtotime($value)) !== false && $timestamp>=0 ) { // return value changed at PHP 5.1
				$fields[$name]['DATE'] ++;
			}else {
				$fields[$name]['STRING'] ++;
			}
		}
	}
	foreach( $fields as $name => &$types ) {
			$types['IS_NUMBER'] = false;
			$types['IS_DATE'] = false;
			$types['IS_STRING'] = false;
			$types['HAS_BLANKS'] = false;
			$types['ALL_BLANKS'] = false;
		if ( $types['NUMBER']>0 && $types['DATE']==0 && $types['STRING']==0 ) {
			$types['IS_NUMBER'] = true;
		} elseif ( $types['NUMBER']==0 && $types['DATE']>0 && $types['STRING']==0 ) {
			$types['IS_DATE'] = true;
		} elseif ( $types['NUMBER']==0 && $types['DATE']==0 && $types['STRING']>0 ) {
			$types['IS_STRING'] = true;
		}
		if ( $types['BLANK'] > 0 ) {
			$types['HAS_BLANKS'] = true;
			if (  $types['NUMBER']==0 && $types['DATE']==0 && $types['STRING']==0 ) {
				$types['ALL_BLANK'] = true;
			}
		}
	}
	return $fields;
}


function do_json_remote_op($url,$op,$params=array(),$version="0.1") {
	$args = $params;
	$args['version'] = $version;
	$args['op'] = $op;
	$pos = strpos($url,'?');
	$query = http_build_query($args);
	list($baseurl,$urlquery) = explode('?',$url,2);
	if ( $urlquery ) $query = $urlquery . '&' . $query;

//echo "args as PHP: ".format_as_php($args)." <br>\n----\n";
	//echo "query: ".print_r($query,1)." <br>\n----<br>\n";
	//echo "uri = {$url}?{$query} <br>\n";
	//$json_output = file_get_contents( $url . "?"  . $query );

	echo "<!-- do_json_remote_op:  $baseurl -->\n";
	echo "<!--                     $query -->\n";
	list($header, $json_output) = PostRequest($baseurl, "QbB server", $query);
	echo "<!-- json: $json_output -->\n";
	//echo "<!-- json: ".print_r($json_output,1)." -->\n";
	$output = json_decode($json_output,true);
	//echo "json: ".print_r($json_output,1)." <br>\n----<br>\n";
	$status = $output['status'];
	$result = $output['result'];
	$elapsedTime = $output['elapsedTime'];
	$debug = $output['debug'];
		echo "<!--  time: $elapsedTime  -->\n";
	if ( $debug ) {
		echo "<!--  log: " . implode("\n", array_map('htmlentities',$debug)) . "  -->\n";
	}
	if ( ! $status ) {
		echo "bad status $op<br>\n";
		return false;
	} else {
		//echo "<!-- data: ".print_r($result,1)."\n -->\n";
		return $result;
	}

}

class JSONRemoteService {
	var $url;
	var $version;
	
	var $status;
	var $message;
	var $result;
	var $debug;
	var $elapsedTime;
	
	function __construct($url,$version="0.1") {
		$this->url = $url;
		$this->version = $version;
	}
	
	function do_op($op,$params=array()) {
		$args = $params;
		$args['version'] = $this->version;
		$args['op'] = $op;
		$pos = strpos($this->url,'?');
		$query = http_build_query($args);
		list($baseurl,$urlquery) = explode('?',$this->url,2);
		if ( $urlquery ) $query = $urlquery . '&' . $query;
	
	//echo "args as PHP: ".format_as_php($args)." <br>\n----\n";
		//echo "query: ".print_r($query,1)." <br>\n----<br>\n";
		//echo "uri = {$url}?{$query} <br>\n";
		//$json_output = file_get_contents( $url . "?"  . $query );
	
		echo "<!-- do_json_remote_op:  $baseurl -->\n";
		echo "<!--                     $query -->\n";
		list($header, $json_output) = PostRequest($baseurl, "QbB server", $query);
		echo "<!-- json: $json_output -->\n";
		//echo "<!-- json: ".print_r($json_output,1)." -->\n";
		$output = json_decode($json_output,true);
		//echo "json: ".print_r($json_output,1)." <br>\n----<br>\n";
		$this->message = $output['message'];
		$this->status = $output['status'];
		$this->result = $output['result'];
		$this->elapsedTime = $output['elapsedTime'];
		$this->debug = $output['debug'];
			echo "<!--  time: $this->elapsedTime  -->\n";
		if ( $this->debug ) {
			echo "<!--  log: " . implode("\n", array_map('htmlentities',$this->debug)) . "  -->\n";
		}
		if ( ! $this->status ) {
			//echo "bad status<br>\n";
			return false;
		} else {
			//echo "<!-- data: ".print_r($result,1)."\n -->\n";
			return $this->result;
		}
	
	}


}


class FormatAsPHP {
	var $indent;
	var $tab;
	var $value;
	function FormatAsPHP($value,$indent="",$tab="    ") {
		$this->indent = $indent;
		$this->tab = $tab;
		$this->value = $value;
	}
	static function format($value,$indent="",$tab="    ") {
		return $indent . self::format_without_first_indent($value,$indent,$tab);
	}
	static function format_without_first_indent($value,$indent="",$tab="    ") {
		$formatter = new FormatAsPHP($value,$indent,$tab);
		return $indent . $formatter->_format();
	}
	function _format_element($value) {
		return self::format_without_first_indent($value,$this->indent.$this->tab,$this->tab); 
	}
	function _format_element_2_deep($value) {
		return self::format_without_first_indent($value,$this->indent.$this->tab.$this->tab,$this->tab); 
	}
	function format_string($str) {
		return '"'.self::escape_string($str).'"';
	}
	function escape_string($str) {
		return addcslashes( $str, "\\\"\'\$\0..\37\177..\377" );
	}
	function _format() {
		//echo "format [[".print_r($this->value,1)."]] <br>\n";
		if ( is_bool($this->value) ) {
			if ( $this->value ) return 'true';
			else return 'false';
		} elseif ( is_string($this->value) ) {
			return $this->format_string($this->value);
		} elseif( is_array($this->value) || is_object($this->value) ) {
			//echo "format as array <br>\n";
			$parts = array();
			foreach ( $this->value as $key => $val ) {
				if ( ! is_integer($key) ) {
					$key = $this->format_string($key);
				}
				$parts[] = $key . '=>' . $this->_format_element_2_deep($val);
			}
			return $this->_build('array( ',' )',', ',$parts);
		} else {
			return strval($this->value);
		}
	}
	function _build($start,$end,$delim,$parts,$maxsize=80) {
		$body_one_line = trim(implode( $delim, $parts ));
		$body_many_line = trim(implode( trim($delim)."\n".$this->indent.$tab, $parts ));
		$php = $start . $body_one_line . $end;
		if ( strpos($body_one_line,"\n") !== false || strlen($php) > $maxsize ) {
			$php = trim($start) . "\n". $this->indent . $tab . $body_many_line . "\n" . $this->indent . trim($end);
		}
		return $php;
	}
}

function format_as_php($value,$indent="",$tab="  ") {
	return FormatAsPHP::format($value,$indent,$tab);
}


function get_label_sparql($uri) {
	  

    $query = 'prefix dct: <http://purl.org/dc/terms/>';
    $query .=' prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#>';
    $query .=' prefix skos: <http://www.w3.org/2004/02/skos/core#>';
    $query .=' prefix dc: <http://purl.org/dc/elements/1.1/>';
    $query .=' prefix foaf: <http://xmlns.com/foaf/0.1/>';
    $query .=' prefix gn: <http://www.geonames.org/ontology#>';
    $query .= ' construct {';
    $query .= ' <' . $uri . '> dct:title ?title .';
    $query .= ' <' . $uri . '> rdfs:label ?label .';
    $query .= ' <' . $uri . '> skos:prefLabel ?prefLabel .';
    $query .= ' <' . $uri . '> dc:title ?title2 .';
    $query .= ' <' . $uri . '> foaf:name ?name .';
    $query .= ' <' . $uri . '> gn:name ?gnname .';
    $query .= '} {';
    $query .= ' optional { <' . $uri . '> dct:title ?title . }';
    $query .= ' optional { <' . $uri . '> rdfs:label ?label . }';
    $query .= ' optional { <' . $uri . '> skos:prefLabel ?prefLabel . }';
    $query .= ' optional { <' . $uri . '> dc:title ?title2 . }';
    $query .= ' optional { <' . $uri . '> foaf:name ?name . }';
    $query .= ' optional { <' . $uri . '> gn:name ?gnname . }';
    $query .= '}';


    return $query;
}
  
function get_common_rdf_label_info() {
	
	return array (
							 array( 'dct',  'http://purl.org/dc/terms/', 'title' ),
							 array( 'rdfs',  'http://www.w3.org/2000/01/rdf-schema#', 'label' ),
							 array( 'skos',  'http://www.w3.org/2004/02/skos/core#', 'prefLabel' ),
							 array( 'dc',  'http://purl.org/dc/elements/1.1/', 'title' ),
							 array( 'foaf',  'http://xmlns.com/foaf/0.1/', 'title' ),
							 array( 'gn',  'http://www.geonames.org/ontology#', 'name' ),
							 );
}
  
function get_common_rdf_labels() {
	$label_info = get_common_rdf_label_info();
	$labels = array();
	foreach( $label_info as $info ) {
		$labels[] = $info[1].$info[2];
	}
	return $labels;
	
}

function get_common_rdf_label_names() {
	return array ( 'prefLabel', 'label', 'title' );
}

function is_uri($uri) {
	return starts_with($uri,"http:") || starts_with($uri,"https:");
	
}

?>