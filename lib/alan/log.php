<?php

class DebugLog {
	var $mode = 'echo';  // 'none', 'echo', 'echoh', 'comment', 'save', 'file'
	var $fp = false;     // used in file mode
	var $messages = array();
	static $_theDebugLog = false;
	static function theDebugLog($mode=false,$fp=false) {
		if ( ! self::$_theDebugLog ) self::$_theDebugLog = new DebugLog();
		if ( $mode ) self::$_theDebugLog->setMode($mode);
		if ( $fp ) self::$_theDebugLog->setLogfile($fp);
		return self::$_theDebugLog;
	}
	function __construct($mode='echo',$fp=false) {
		$this->setMode($mode,$fp);
	}
	function setMode($mode,$fp=false) {
		$this->mode = $mode;
		if ( $mode == 'file' ) {
			$this->setLogfile($fp);
		}
	}
	function setLogfile($fp) {
		if ( is_string($fp) && ! is_numeric($fp) ) {
			$fpath = $fp;
			$fp = fopen($fpath,'a');
		}
		$this->fp = $fp;
	}
	function log($message,$type='LOG') {
		$now = time();
		$entry = array( 'time'=>$now, 'type'=>$type, 'message'=> $message );
		switch( $this->mode ) {
			case 'save':
					$this->messages[] = $entry;
					break;
			default:
					$this->flushEntry($entry);
					break;
		}
	}
	function flushEntry($entry,$mode=false) {
		if ( ! $mode ) $mode = $this->mode;
		$formatted = $this->formatEntry($entry);
		switch( $this->mode ) {
			case 'file':
					if ( $this->fp ) {
						fwrite( fp,  $formatted . "\n" );
					}
					break;
			case 'comment':
					echo "<!-- " . htmlentities( $formatted ) . " -->\n";
					break;
			case 'echoh':
					echo htmlentities( $formatted ) . "<br>\n";
					break;
			case 'echo':
			default:
					echo $formatted . "\n";
					break;
		}
	}
	function formatEntry($entry) {
		return $entry['time'] . ' :' . $entry['type'] . ' :' . $entry['message'];
	}
	function flush() {
		foreach ( $this->messages as $entry ) {
			$this->flushEntry($entry);
		}
		$this->clear();
	}
	function getLogRaw() {
		return $this->messages;
	}
	function getLogEntriesFormatted() {
		return array_map( array($this,'formatEntry'), $this->messages );
	}
	function getLog() {
		return implode("\n", $this->getLogEntriesFormatted() );
	}
	function clear() {
		$this->messages = array();
	}
}


?>