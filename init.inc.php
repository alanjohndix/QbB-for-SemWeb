<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',true);

$appRoot = dirname(__FILE__);

require_once $appRoot . '/../config.php' ;

set_include_path(get_include_path() . PATH_SEPARATOR . $appRoot);
set_include_path(get_include_path() . PATH_SEPARATOR . $appRoot . '/lib');

define('MORIARTY_ARC_DIR', $appRoot . '/lib/arc'.DIRECTORY_SEPARATOR);
define('MORIARTY_DIR', $appRoot . '/lib/moriarty'.DIRECTORY_SEPARATOR);
define('OPTIMISER_DIR', $appRoot . '/lib/optimiser'.DIRECTORY_SEPARATOR);
define('ALAN_DIR', $appRoot . '/lib/alan'.DIRECTORY_SEPARATOR);
define('TEST_DIR', $appRoot.'/tests/');
define('TESTDATA_DIR', $appRoot.'/tests/data/');

define( 'RDF_TYPE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' );


//require_once OPTIMISER_DIR . "prettyprintquery.class.php";
//require_once OPTIMISER_DIR . "schemastats.class.php";
//require_once OPTIMISER_DIR . "optimiser.class.php";


require_once MORIARTY_ARC_DIR . 'ARC2.php';
require_once MORIARTY_DIR .'moriarty.inc.php';
require_once MORIARTY_DIR . 'store.class.php';
require_once MORIARTY_DIR . 'credentials.class.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';

$ex_storeuri = QBB_RDF_DEFAULT_SOURCE;

require_once ALAN_DIR . 'util.php';
require_once ALAN_DIR . 'log.php';

DebugLog::theDebugLog('save');

function debug($message) {
	$debuglog = DebugLog::theDebugLog();
	$debuglog->log($message);
}

if ( ! defined('IN_API') ) {
	define('IN_API',false);
}
if ( ! defined('BASE_URL') ) {
	$myurl = curPageURL();
	$pos = strrpos($myurl,'/');
	if ( $pos === false ) {
		$base = "$myurl";  // weird something wrong
	} else {
		$base = substr($myurl,0,$pos+1);  // N.B. inlcudes trailing '/'; 
	}
	if ( IN_API ) {
		if ( ends_with($base,'api/') ) {
			$base = strip_suffix($base,'api/');
		}
	}
	
	define('BASE_URL',$base);
}
if ( ! defined('API_URL') ) {
	define('API_URL',BASE_URL . 'api/');
}


require_once ALAN_DIR . 'jsonservice.class.php';
require_once ALAN_DIR . 'rdfsource.class.php';
require_once ALAN_DIR . 'remoterdfsource.class.php';
require_once ALAN_DIR . 'decisiontree.class.php';
require_once ALAN_DIR . 'id3.class.php';
require_once ALAN_DIR . 'sparqltree.class.php';

//$source = new RDFStoreSource(QBB_RDF_DEFAULT_SOURCE);
$source = new RemoteRDFStoreSource(QBB_RDF_DEFAULT_SOURCE);

?>