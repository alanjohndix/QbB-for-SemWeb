<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'init.inc.php';

class TreeService extends JSONService {
	function process_op($op,$args) {
		switch ( $op ) {
			case 'apply_to_rows':
					$tree = $this->get_array_param('tree');
					$rows = $this->get_array_param('rows');
					
					$num_rows = count($rows);
					debug("num_rows = $num_rows");
					$selected = array();
					for ( $r=0; $r<$num_rows; $r++ ) {
						$row = $rows[$r];
						$selected[$r] = DecisionTree::apply_tree($tree,$row);
					}
					$output = array( 'status' => true, 'result' => $selected );
					break;
			case 'tree_to_sparql':
					$type = $this->get_param('type');
					$fields = $this->get_array_param('fields');
					$tree = $this->get_array_param('tree');
					$formater = new SparqlTree($type,$fields);
		
					$formatted = $formater->format_tree($tree);
					if ( $formater->is_yes_tree($tree) ) {
						$andor="TRUE";
					} elseif ( $formater->is_no_tree($tree) ) {
						$andor="FALSE";
					} else {
						$andor="MIXED";
					}
					$result = array( 'mix'=>$andor, 'sparql'=>$formatted );
					$output = array( 'status' => true, 'result' => $result );
					break;
			default:
					$output = parent::process_op($op,$args);
					break;
		}
		return $output;
	}
}

$treeservice = new TreeService("0.1");

$treeservice->request();

exit;

/*
$op       = $_REQUEST['op'];

switch ( $op ) {
	case 'apply_to_rows':
			$log = array();
			$tree = $_REQUEST['tree'];
			$rows = $_REQUEST['rows'];
			$num_rows = count($rows);
			$log[] = "num_rows = $num_rows";
			$selected = array();
			for ( $r=0; $r<$num_rows; $r++ ) {
				$row = $rows[$r];
				$selected[$r] = DecisionTree::apply_tree($tree,$row);
			}
			$output = array( 'status' => true, 'result' => $selected, 'log'=>$log );
			break;
	default:
			$output = array( 'status' => false, 'message' => 'unrecognised operation type ' . $op );
			break;
}

$format = $_REQUEST['format'];
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
*/

?>