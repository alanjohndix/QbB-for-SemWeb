<?php

require_once ALAN_DIR . 'log.php';

class JSONService {
	var $version;
	var $request_args;
	var $request_version;
	var $request_op;
	var $request_format;
	var $startTime, $endTime;
	
	function __construct($version="0.1") {
		$this->version = $version;
	}
	function fail( $message ) {
		return array( 'status'=>false, 'message'=>$message );
	}
	function request($args=false) {
		$this->startTime = time();
		if ( ! $args ) {
			$args = $_REQUEST;
		}
		$this->request_args = $this->process_generic_args($args);
		if ( $this->request_version != $this->version ) {
			$output = $this->fail( 'bad version, expect '.$this->version );
			//array( 'status'=>false, 'message'=>'bad version, expect '.$this->version);
		} else {
			$output = $this->process_op($this->request_op,$this->request_args);
		}
		$this->endTime = time();
		$this->create_reponse($output);
	}
	function has_param($name) {
		return array_key_exists($name,$this->request_args);
	}
	function get_param($name,$default_value=false) {
		if ( array_key_exists($name,$this->request_args) ) {
			return $this->request_args[$name];
		} else {
			return $default_value;
		}
	}
	function get_array_param($name,$default_value=false) {
		if ( array_key_exists($name,$this->request_args) ) {
			$arr = $this->request_args[$name];
			if ( is_string($arr) ) {
				$arr = json_decode($arr,true);
			}
			return $arr;
		} else {
			return $default_value;
		}
	}
	function process_generic_args($args) {
		$this->request_version = @$args['version'];
		$this->request_op = @$args['op'];
		$this->request_format = @$args['format'];
		$this->request_callback = @$args['callback'];
		
		unset($args['version']); unset($args['op']); unset($args['op']); unset($args['callback']);
				
		return $args;
	}
	/*
	 * @return array ( status=>true/false,  result=>the result (if status=true), message=>error message (if status=fase)
	 */
	function process_op($op,$args) {
		// subclass to define
		return $this->fail( 'unrecognised operation '+$op );
		//array( 'status'=>false, 'message'=>'unrecognised operation '+$op);
	}
	function create_reponse($output) {
		$debuglog = DebugLog::theDebugLog();
		$log = $debuglog->getLogEntriesFormatted();
		
		if ( ! array_key_exists('status', $output) ) {
			$output['status']=true;
		}
		
		$output['startTime'] = $this->startTime;
		$output['endTime'] = $this->endTime;
		$output['elapsedTime'] = $this->endTime-$this->startTime;
		$output['debug'] = $log;
		
		//echo "==do json_encode==\n";
		
		$json = json_encode($output);
		
		$content_type="";
		switch ( $format ) {
			case 'jsonp':
					$callback = $_REQUEST['callback'];
					if ( ! $callback ) {
						exit;
					}
					$output = $callback . '('.json_encode($output).')';
					$content_type='application/json; charset=utf-8';
					break;
			case 'json':
			default:
					$output = json_encode($output);
					$content_type='application/json; charset=utf-8';
					break;
		}
		
		$json = json_encode($output);
		
		if ( $content_type )  header('content-type: '.$content_type);
		
		echo $output;
	}
}

?>