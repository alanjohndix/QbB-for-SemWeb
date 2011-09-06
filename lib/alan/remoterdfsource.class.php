<?php

require_once "util.php";

define( 'SCHEMA_ENTITY_PREFIX', 'http://alandix.com/schema/entity/');

class RemoteRDFStoreSource {
	var $targetStoreUri;
	var $sourceUrl;
	function __construct($targetStoreUri=false) {
		if ( $targetStoreUri ) {
			$this->setTarget($targetStoreUri);
		}
	}
	function setTarget($targetStoreUri) {
		$this->targetStoreUri = $targetStoreUri;
		$args['store'] = $this->targetStoreUri;
		$query = http_build_query($args);
		$sourceUrl = API_URL . "source.php?" . $query;
		$this->setSourceUrl($sourceUrl);
	}
	function setSourceUrl($sourceUrl) {
		$this->sourceUrl = $sourceUrl;
	}
	function makeEntityUri() {
		return SCHEMA_ENTITY_PREFIX . generatePackedGuid();
	}
	function getLabels($uri) {
		return $this->_do_op('label',array('uri'=>$uri));
	}
	function getKinds() {
		return $this->_do_op('kinds');
	}
	function getInstancesAndFields($class,$offset=0,$limit=10) {
		return $this->_do_op('instances',array('kind'=>$class,'offset'=>$offset,'limit'=>$limit));
	}
	function _do_op($op,$params=array()) {
		$result = do_json_remote_op($this->sourceUrl,$op,$params);
		return $result;
	}
	function _do_op_old($op,$params=array()) {
		$args = $params;
		$args['op'] = $op;
		$args['store'] = $this->targetStoreUri;
		$query = http_build_query($args);
		//echo "args as PHP: ".format_as_php($args)." <br>\n----\n";
		//echo "query: ".print_r($query,1)." <br>\n----<br>\n";
		$url = API_URL . "source.php";
		//echo "uri = {$url}?{$query} <br>\n";
		//$json_output = file_get_contents( $url . "?"  . $query );

		echo "<!-- $url -->\n";
		list($header, $json_output) = PostRequest($url, "QbB server", $query);
		$output = json_decode($json_output,true);
		//echo "json: ".print_r($json_output,1)." <br>\n----<br>\n";
		//echo "json: ".print_r($json_output,1)." <br>\n----<br>\n";
		$status = $output['status'];
		$result = $output['result'];
		if ( ! $status ) {
			//echo "bad status<br>\n";
			return false;
		} else {
			//echo "data: ".print_r($result,1)." <br>\n----<br>\n";
			return $result;
		}

	}
}
?>