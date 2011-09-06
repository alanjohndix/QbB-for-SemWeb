<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.inc.php';

class SourceService extends JSONService {
	function process_op($op,$args) {
		$source = new RDFStoreSource();
		
		$storeuri = $args['store'];
		
		if ( ! $storeuri ) {
			$output = array( 'status' => false, 'message' => 'no store given' );
		} else {
			$source->setTarget($storeuri);
			
			switch ( $op ) {
				case 'label':
						$uri = $this->get_param('uri');
						$labels = $source->getLabels($uri);
						$result = $labels;
						$output = array( 'status' => true, 'result' => $result );
						break;
				case 'kinds':
						$kinds = $source->getKinds();
						$result = $kinds;
						$output = array( 'status' => true, 'result' => $result );
						break;
				case 'instances':
						$table = $this->get_param('kind');
						$offset = $this->get_param('offset');
						$limit = $this->get_param('limit');
						if ( !$offset ) $offset = 0;
						if ( !$limit ) $limit = 20;
						$result = $source->getInstancesAndFields($table,$offset,$limit);
						$output = array( 'status' => true, 'result' => $result );
						break;
				default:
						$output = parent::process_op($op,$args);
					//$output = array( 'status' => false, 'message' => 'unrecognised operation type ' . $op );
						break;
			}
		}

		return $output;
	}
}

$service = new SourceService("0.1");

$service->request();

exit;

/*
$startTime = time();
debug("starting");

//$source = new RDFStoreSource($targetStoreUri);
$source = new RDFStoreSource();

$op       = $_REQUEST['op'];
$storeuri = $_REQUEST['store'];

if ( ! $op ) { // local testing
	//$op = 'kinds';
	$op = 'instances';
	$storeuri = QBB_RDF_DEFAULT_SOURCE;
	$_REQUEST['kind']='http://www.alandix.com/example#qbb_ex1';
	$_REQUEST['offset']=0;
	$_REQUEST['limit']=20;
}

if ( ! $storeuri ) {
	$output = array( 'status' => false, 'message' => 'no store given' );
} else {
	$source->setTarget($storeuri);
	
	switch ( $op ) {
		case 'label':
				$uri =  $_REQUEST['uri'];
				$labels = $source->getLabels($uri);
				$result = $labels;
				$output = array( 'status' => true, 'result' => $result );
				break;
		case 'kinds':
				$kinds = $source->getKinds();
				$result = $kinds;
				$output = array( 'status' => true, 'result' => $result );
				break;
		case 'instances':
				$table =  $_REQUEST['kind'];
				$offset =  $_REQUEST['offset'];
				$limit =  $_REQUEST['limit'];
				if ( !$offset ) $offset = 0;
				if ( !$limit ) $limit = 20;
				
				//$rows = $source->getInstances($table,$offset,$limit);
				//$num_rows =  count($rows);
				////echo "rows($table): ".print_r($rows,1) . " <br>\n";
				////echo "num_rows($table): $num_rows <br>\n";
				//$fields_present = array_unique( call_user_func_array( 'array_merge',  array_map( 'array_keys', $rows ) ) );
				//$fields_present = array_diff( $fields_present, array('id','http://www.w3.org/1999/02/22-rdf-syntax-ns#type') );
				//$fields = $source->addPropertyNames($fields_present);
				//$num_fields =  count($fields);
				//$result = array( 'num_rows' => $num_rows, 'rows' => $rows, 'num_fields' => $num_fields, 'fields' => $fields );
				
				$result = $source->getInstancesAndFields($table,$offset,$limit);
				$output = array( 'status' => true, 'result' => $result );
				break;
		default:
				$output = array( 'status' => false, 'message' => 'unrecognised operation type ' . $op );
				break;
	}

}

debug("finished");

$debuglog = DebugLog::theDebugLog();
$log = $debuglog->getLogEntriesFormatted();

$endTime = time();

$output['startTime'] = $startTime;
$output['endTime'] = $endTime;
$output['elapsedTime'] = $endTime-$startTime;
$output['debug'] = $log;

//echo "==do json_encode==\n";

$json = json_encode($output);

//echo "==done==\n";
//header('content-type: application/json; charset=utf-8');

echo $json;

//echo "==end abc==\n";
*/
?>