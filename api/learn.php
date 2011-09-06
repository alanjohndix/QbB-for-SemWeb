<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.inc.php';

class LearnService extends JSONService {
	function process_op($op,$args) {
		
		debug("starting");
		
		$learn = new ID3();

		/*
		$type        = $args['type'];
		$fields      = $args['fields'];
		$yes_rows    = $args['yes_rows'];
		$no_rows     = $args['no_rows'];
		*/
		//$type        = $this->get_param('type');
		$fields      = $this->get_array_param('fields');
		$yes_rows    = $this->get_array_param('yes_rows');
		$no_rows     = $this->get_array_param('no_rows');
		

		$rows = array_merge($yes_rows,$no_rows);
		$field_types = get_fields_and_types($rows,$fields);
		
		
		$learn->set_fields($fields,$field_types);
		debug("learn_tree");
		//echo "learn_tree <br>\n"; flush();
		$tree = $learn->learn_tree($yes_rows,$no_rows);
		//echo "learn_tree returns <br>\n"; flush();
		debug("learn_tree returns");
		$formater = new SparqlTree($type,$fields);
		
		$formatted = $formater->format_tree($tree);
		if ( $formater->is_yes_tree($tree) ) {
			$andor="TRUE";
		} elseif ( $formater->is_no_tree($tree) ) {
			$andor="FALSE";
		} else {
			$andor="MIXED";
		}
		
		//$result = array( 'mix'=>$andor, 'tree'=>$tree, 'sparql'=>$formatted );
		$output = array( 'status' => true, 'result' => $tree );
		
		debug("finished");

		return $output;
	}
}

$service = new LearnService("0.1");

$service->request();

exit;

/*
$startTime = time();
debug("starting");

$type        = $_REQUEST['type'];
$fields      = $_REQUEST['fields'];
//$field_types = $_REQUEST['field_types'];
$yes_rows    = $_REQUEST['yes_rows'];
$no_rows     = $_REQUEST['no_rows'];

if ( !$fields ) {
	require TESTDATA_DIR . "tst1.inc.php";
	$fields      = $data['fields'];
	//$field_types = $data['field_types'];
	$yes_rows    = $data['yes_rows'];
	$no_rows     = $data['no_rows'];
}


//function label_columns(&$rows,$fields) {
//	foreach ( $rows as $row_index => $row ) {
//		foreach ( $row as $id => $value ) {
//			if ( is_numeric($id) && array_key_exists($id,$fields) ) {
//				$id = $fields[$id];
//				$rows[$row_index][$id] = $value;
//			}
//		}
//	}
//}
//
//label_columns($yes_rows,$fields);
//label_columns($no_rows,$fields);


$rows = array_merge($yes_rows,$no_rows);
$field_types = get_fields_and_types($rows,$fields);


$learn->set_fields($fields,$field_types);
debug("learn_tree");
//echo "learn_tree <br>\n"; flush();
$tree = $learn->learn_tree($yes_rows,$no_rows);
//echo "learn_tree returns <br>\n"; flush();
debug("learn_tree returns");
$formater = new SparqlTree($type,$fields);

$formatted = $formater->format_tree($tree);
if ( $formater->is_yes_tree($tree) ) {
	$andor="TRUE";
} elseif ( $formater->is_no_tree($tree) ) {
	$andor="FALSE";
} else {
	$andor="MIXED";
}

$result = array( 'mix'=>$andor, 'tree'=>$tree, 'sparql'=>$formatted );

debug("finished");

$debuglog = DebugLog::theDebugLog();
$log = $debuglog->getLogEntriesFormatted();

$endTime = time();

$result['startTime'] = $startTime;
$result['endTime'] = $endTime;
$result['elapsedTime'] = $endTime-$startTime;
$result['debug'] = $log;

$json_result = json_encode($result);

//header('content-type: application/json; charset=utf-8');

echo $json_result;
*/
?>