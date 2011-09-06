<?php

// **************************************
//
//  values to be set for your system 

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.inc.php';

//echo "<h1>===================  1 =======================</h1>\n";

DebugLog::theDebugLog('comment')->flush();
debug('start');

//echo "<h1>===================  2  =======================</h1>\n";

// **************************************

// andor=FALSE&last_table=qbb_ex1&doit=Hide+this+Help+Message&table=qbb_ex1&num_rows=7&select0=&select1=&select2=&select3=&select4=&select5=&select6=

$doit = $_REQUEST['doit'];
$hidehelp = $_REQUEST['hidehelp'];

$andor = $_REQUEST['andor'];
$last_table = $_REQUEST['last_table'];
$table = $_REQUEST['table'];
$num_rows = $_REQUEST['num_rows'];
$andor = $_REQUEST['andor'];
$last_storeuri = $_REQUEST['last_storeuri'];
$storeuri = $_REQUEST['storeuri'];

$opts = array_extract_prefixed($_REQUEST,"select");
echo "<!-- opts: ".print_r($opts,1)." -->\n";

$reset = false;

if ( !isset($storeuri) || "$storeuri" == '' )
{
	if ( $ex_storeuri ) {
		$storeuri = $ex_storeuri;
	}
}
if ( !isset($last_storeuri) || $last_storeuri != $storeuri )
{
	$reset = true;
	$table = "";
}

//echo "<h1>===================  3  =======================</h1>\n";

if ( $storeuri ) {
	//echo "<h1>===================  3.1  =======================</h1>\n";
	$source->setTarget($storeuri);
	//echo "<h1>===================  3.2  =======================</h1>\n";
	$tables = $source->getKinds();
	//echo "<h1>===================  3.3  =======================</h1>\n";
	if ( $tables == false ) {
		$tables = array();
	}
} else {
	$tables = array();
}

//echo "<h1>===================  4  =======================</h1>\n";

if ( !isset($table) || '$table' == '' || ! in_array($table,$tables) )
{
	echo "<!-- **** use default table  -->\n";
	if ( $ex_table && in_array($ex_table,$tables) ) {
		$table = $ex_table;
	} else {
		$table = $tables[0];
	}
}

echo "<!-- **** storeuri = $storeuri   table = $table  -->\n";

if ( !isset($last_table) || $last_table != $table )
{
	$reset = true;
}

$makequery = 1;

/*
if ( $doit == "Hide this Help Message" )
{
	$makequery = 0;
	$hidehelp = 1;
}
else if ( $doit == "Show Help Message" )
{
	$makequery = 0;
	$hidehelp = 0;
}
*/
if ( $reset ) {
	$makequery = 0;
	$opts = array();
}

$instances_and_fields = $source->getInstancesAndFields($table,0,20);
if ( $instances_and_fields ) {
	$rows = $instances_and_fields['rows'];
	$fields = $instances_and_fields['fields'];
} else {
	$rows = array();
	$fields = array();
}

$merged = array();
foreach( $rows as $row ) {
	$merged = array_merge( $merged, array_keys($row), array_values($row) );
}
//echo "merged: ".print_r(array_values($merged),1)." <br>\n<br>\n<br>\n<br>\n";
$merged = array_filter( $merged, 'is_uri' );
//echo "merged: ".print_r(array_values($merged),1)." <br>\n<br>\n<br>\n<br>\n";
$merged = array_merge( $tables , $merged );

$all_uris = array_unique( $merged );


//echo "merged: ".print_r(array_values($merged),1)." <br>\n<br>\n<br>\n<br>\n";
//echo "all: ".print_r($all_uris,1)." <br>\n<br>\n";;

$labels = $source->getLabels($all_uris);
//echo "labels: ".print_r($labels,1)." <br>\n<br>\n";

$num_rows =  count($rows);
$num_fields =  count($fields);

function do_learn($fields,$yes_rows,$no_rows) {
	$learn_service = new JSONRemoteService( API_URL . "learn.php", '0.1');
	$learn_params = array( 'fields'=>json_encode($fields), /*'field_types'=>$field_types,*/ 'yes_rows'=>$yes_rows, 'no_rows'=>$no_rows );
	$learn_result = $learn_service->do_op( 'learn', $learn_params );
	return $learn_result;
}



$has_yes = 0;
$has_no = 0;
$has_tree = 0;

if ( $reset )    $andor="FALSE";


$selected = array();
//if ( ! $opts ) $opts = array();

//echo "<h1>===================  8  =======================</h1>\n";

if ( $makequery )
{
	$has_yes = 1;
	$has_no = 1;
	
	$yes_ct = 0;
	$no_ct = 0;
	
	$yes_rows = array();
	$no_rows = array();
	
	for ( $r=0; $r<$num_rows; $r++ )
	{
		$opt = $opts[$r];
		$row = $rows[$r];
		//$opt_selected  =  $opt == 'Y' || $opt == 'N';
	  //  if ( ! $opt_selected ) continue;
		if ( $opt == 'Y' ) 
		{
			$yes_ct++;
			$yes_rows[] = $row;
		}
		else if ( $opt == 'N' ) 
		{
			$no_ct++;
			$no_rows[] = $row;
		}
	}
	
	if ( $yes_ct==0 ) {
		$andor="FALSE";
	} else if ( $no_ct==0 ) {
		$andor="TRUE";
	} else
	{
debug('start do_learn');
		$tree = do_learn($fields,$yes_rows,$no_rows);
debug('learning returns');
		//echo "QBB tree = ".print_r($tree,1)."<br>\n";
		
		$selected = array();
		if ( $tree ) {
			$has_tree = 1;
			
			//$tree_url = API_URL . "tree.php";
		$tree_url = API_URL . "tree.php";
		$tree_service = new JSONRemoteService( $tree_url, '0.1');
			
		debug('start tree_to_sparql');
			$tree_to_sparql_params = array( 'type'=>$table, 'fields'=>json_encode($fields), 'tree'=>json_encode($tree) );
			$tree_to_sparql = $tree_service->do_op( 'tree_to_sparql', $tree_to_sparql_params );
			//$tree_to_sparql = do_json_remote_op( $tree_url, 'tree_to_sparql', $tree_to_sparql_params );
			//echo "tree_to_sparql = ".print_r($tree_to_sparql,1)."<br>\n<br>\n";
		debug('tree_to_sparql returns');

		$formatted = $tree_to_sparql['sparql'];
		$andor = $tree_to_sparql['mix'];
		
		debug('start apply_to_rows');
			$selected = $tree_service->do_op( 'apply_to_rows', array('tree'=>$tree,'rows'=>$rows) );
		debug('apply_to_rows returns');
			
		} else {
			$has_tree = 0;
			$formatted = "--- no query ---";
			$andor="FALSE";
		}
	}
	
}  // end of if/else reset


if ( $andor=="FALSE" )
{
	$andor_val = 0;
	$display_query = "-- none selected --";
}
else if ( $andor=="TRUE" )
{
	$andor_val = 1;
	$display_query = "-- all selected --";
}
else
{
	$andor_val = $andor;
	$display_query = $formatted;
}


//echo "<h1>===================  9  =======================</h1>\n";

function insert_head()
{
	global $andor;
	global $table;
  	global $tables;
	global $tree;
  	global $display_query;
	global $opts;
	global $source;
	global $rows;
	global $num_rows;
	global $fields;
	global $num_fields;
	global $selected;
	global $labels;

	echo "<script>\n";
	echo "  table = " . json_encode($table) . ";\n\n";
	echo "  tree = " . json_encode($tree) . ";\n\n";
	echo "  tables = " . json_encode($tables) . ";\n\n";
	echo "  display_query = " . json_encode($display_query) . ";\n\n";
	echo "  rows = " . json_encode($rows) . ";\n\n";
	echo "  fields = " . json_encode($fields) . ";\n\n";
	echo "  selected = " . json_encode($selected) . ";\n\n";
	echo "  opts = " . json_encode($opts) . ";\n\n";
	//$labels = array();
	echo "  labels = " . json_encode($labels) . ";\n\n";
	
	      

        

	echo "</script>\n";
}


function insert_hidden()
{
	global $andor;
	global $table;
	global $storeuri;

	echo  "<input type='hidden' name='andor' value='$andor'>";
	echo  "<input type='hidden' name='last_table' value='$table'>";
	echo  "<input type='hidden' name='last_storeuri' value='$storeuri'>";
}

//echo "<h1>===================  10  =======================</h1>\n";


 include("qbb-template.html");

?>
