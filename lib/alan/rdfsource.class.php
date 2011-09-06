<?php

require_once "guid.php";
require_once "util.php";
require_once "storehelper.class.php";

class RDFStoreSource {
	var $schemaStoreUri, $schemaCredentials;
	var $schemaStoreHelper;
	var $targetStoreUri, $targetStoreHelper;
	var $useCache = false;
	
	function __construct($targetStoreUri=false) {
		$this->schemaStoreUri = SCHEMA_STORE_URI;
		$this->schemaCredentials = new Credentials( SCHEMA_STORE_CREDENTIALS_NAME, SCHEMA_STORE_CREDENTIALS_PASSWORD );
		$this->schemaStoreHelper = new StoreHelper($this->schemaStoreUri, $this->schemaCredentials);
		if ( $targetStoreUri ) {
			$this->setTarget($targetStoreUri);
		}
	}
	function setTarget($targetStoreUri) {
		$this->targetStoreUri = $targetStoreUri;
		if ( startsWith($this->targetStoreUri, TALIS_STORES_PREFIX ) ) {
			$this->targetStoreHelper = new StoreHelper($this->targetStoreUri);
		} else {
			$this->targetStoreHelper = new SparqlHelper($this->targetStoreUri);
		}
	}
	function makeEntityUri() {
		return SCHEMA_ENTITY_PREFIX . generatePackedGuid();
	}
	function saveArgsUri($args,$timestampname,$timestamp=0) {
		if ( is_array($args) ) {
			$argsUri = $this->makeEntityUri();
		} else {
			$argsUri = $args;
		}
		$graph = new SimpleGraph();
		if ( is_array($args) ) {
			foreach ( $args as $name => $value ) {
				$graph->add_literal_triple($argsUri, SCHEMA_PREFIX . $name, $value);
			}
		}
		$graph->add_literal_triple($argsUri, SCHEMA_PREFIX . $timestampname, $timestamp);
		echo "add_literal_triple($argsUri, ".SCHEMA_PREFIX . $timestampname.", $timestamp) <br>\n";

		$response = $this->schemaStoreHelper->addGraph($graph);
		if ( ! $response->is_success() ) {
			echo "failed to make args uri :-(\n    " . $response->body ." <br>\n";
			return false;
		} else {
			return $argsUri;
		}
	}
	function getCacheTimestamp( $args, $timestampname ) {
		$timestampuri = SCHEMA_PREFIX . $timestampname;
		if ( is_array($args) ) {
			$uri = "?uri";
			$uri_clause = "";
			foreach ( $args as $name => $value ) {
				$nameuri = SCHEMA_PREFIX . $name;

				$uri_clause .= "?uri <{$nameuri}> \"{$value}\" .";
			}
			$uri_var = "?uri";
		} else {
			$uri = "<".$args.">";
			$uri_clause = "";
			$uri_var = "";
		}
		$sparql = "select ?time {$uri_var} where { {$uri_clause} {$uri} <{$timestampuri}> ?time . }";
		//echo "time select sparql: ". $sparql . "<br>\n";
		$timeres = $this->schemaStoreHelper->getOneSparqlResult($sparql);
		if ( $timeres ) {
			if ( ! is_array($args) ) {
				$timeres['uri'] = $args;
			}
		}
		return $timeres;
	}
	function getCachedValues($args,$predicatename) {
		$timestampname = $predicatename . "-timestamp";
		$predicateuri = SCHEMA_PREFIX . $predicatename;
		
		$timeres = $this->getCacheTimestamp( $args, $timestampname );
		if ( $timeres ) {
			$uri = $timeres['uri'];
			$sparql = "select ?cached where { {$uri_clause} <{$uri}> <{$predicateuri}> ?cached . }";
			$cached = $this->schemaStoreHelper->getSparqlResults($sparql,array("cached"));
			//echo "getCachedArray($predicatename): got ".count($cached)." cached: $sparql <br>\n";
			if ( $cached === false ) {
				return false;
			} else {
				$res = table_select_column($cached,'cached');
				return $res;
			}
		} else {
			return false;
		}
	}
	function saveCachedValues($res,$args,$predicatename) {
		$timestampname = $predicatename . "-timestamp";
		$predicateuri = SCHEMA_PREFIX . $predicatename;
		$argsUri = $this->saveArgsUri($args,$timestampname);
		if ( !$argsUri ) {
			// do nout
		} else {
			$graph = new SimpleGraph();
			foreach ( $res as $value ) {
				$graph->add_literal_triple($argsUri, SCHEMA_PREFIX . $predicatename, $value );
			}
			$response = $this->schemaStoreHelper->addGraph($graph);
			if(!$response->is_success())
			{
				echo "failed to cache {$predicatename}\n    " . $response->body ." <br>\n";
			}
		}
	}
	function getCachedArray($rawGetter,$args,$predicatename) {
		//echo "getCachedArray($rawGetter,".print_r($args,1).",$predicatename)  <br>\n";
		$time = 0;
		
		if ( $this->useCache ) {
			$res = $this->getCachedValues($args,$predicatename);
			if ( $res ) {
				return $res;
			} else {
				$res = call_user_func($rawGetter,$args);
				//echo "getRaw($predicatename): got ".count($res)." raw <br>\n";
				if ( $res === false ) {
					return $false;
				} else {
					// cache $res
					$this->saveCachedValues($res,$args,$predicatename);
					return $res;
				}
			}
		} else {
			$res = call_user_func($rawGetter,$args);
			return $res;
		}
		
	}
	
	function getLabels($uris) {
		if ( ! is_array($uris) ) $uris = array($uris);
		$triples = $this->targetStoreHelper->get_sparql_service()->describe_to_triple_list( $uris );
		$raw_labels = $this->collect_triples_by_subject($triples);
		$common_labels = get_common_rdf_labels();
		$common_label_names = get_common_rdf_label_names();
			//echo "common_labels: ".print_r($common_labels,1)." <br>\n";
		$res = array();
		foreach ( $uris as $uri ) {
			if ( array_key_exists($uri,$raw_labels) ) {
				$raw = $raw_labels[$uri];
			} else {
				$raw = array();
			}
			$label = "";
			//echo "data for $uri: ".print_r($raw,1)." <br>\n";
			foreach( $common_labels as $label_uri ) {
				if ( array_key_exists( $label_uri, $raw ) ) {
					$label = $raw[$label_uri];
					break;
				}
			}
			if ( ! $label ) {
				foreach( $common_label_names as $label_name ) {
					foreach( $raw as $p => $o ) {
						if ( ends_with($p,'/'.$label_name) || ends_with($p,'#'.$label_name) )  {
							$label = $o;
							break;
						}
					}
				}
			}
			if ( ! $label ) {
				$label = $this->makeUriLabel($uri);
			}
			//echo "chose label $label <br>\n";
			$res[$uri] = $label;
		}
		return $res;
	}

	function getLabels_old($uris) {
		if ( ! is_array($uris) ) $uris = array($uris);
		$labels = array();
		foreach ( $uris as $uri ) {
			$label_for_one = $this->getLabelsForUri($uri);
			if ( count($label_for_one) > 0 ) {
				$labels[$uri] = $label_for_one[0][1];
			} else {
				$labels[$uri] = "";
			}
		}
		return $labels;
	}
	function getLabelsForUri($uri) {
		$sparql = get_label_sparql($uri);
		//echo "LABEL SPARQL: $sparql <br>\n\n";
    	$response = $this->targetStoreHelper->get_sparql_service()->graph($sparql);
		$label_body = $response->body;
		//echo "BODY:" . print_r( $label_body,1) . " <br>\n\n";
		$label_graph = new SimpleGraph();
		$label_graph->from_rdfxml($label_body);
		//echo "GRAPH:" . print_r( $label_graph,1) . " <br>\n\n";
		$label_triples = $label_graph->get_triples();
		//echo "TRIPLES:" . print_r( $label_triples,1) . " <br>\n\n";
		$result = array();
		foreach ( $label_triples as $triple ) {
			$result[] = array($triple['p'],$triple['o']);
		}
		return $result;
  	}

	
	
	function getKinds() {
		return $this->getCachedArray(array($this,'getKindsRaw'),$this->targetStoreUri,'has_class');
	}
	function getKindsRaw($args=false) { // ignore args
		$sparql = "select distinct ?kind where { ?s a ?kind }";
		$kinds = $this->targetStoreHelper->getSparqlResults($sparql,array("kind"));
		if ( $kinds === false ) {
			return $false;
		} else {
			$res = table_select_column($kinds,'kind');
			return $res;
		}
	}
	
	function getFields($class) {
		$props = $this->getProperties($class);
		return $this->addPropertyNames($props);
	}
	
	function addPropertyNames($props) {
		$fields = array();
		foreach ( $props as $prop ) {
			$name = $this->makeUriLabel($prop);
			$fields[] = array( 'id' => $prop, 'name' => $name );
		}
		return $fields;
	}
	
	function makeUriLabel($prop) {
		$len = strlen($prop);
		while ( $len>0 && (  $prop{$len-1} == '/' || $prop{$len-1} == '#' ) ) {
			$prop = substr($prop,0,-1);
			$len--;
		}
		$pos_hash = strrpos($prop,'#');
		$pos_slash = strrpos($prop,'/');
		
		if ( $pos_hash === false ) {
			$pos = $pos_slash;
		} else if ( $pos_slash === false ) {
			$pos = $pos_hash;
		} else {
			if ( $pos_hash > $pos_slash ) {
				$pos = $pos_hash;
			} else {
				$pos = $pos_slash;
			}
		}
		if ( $pos !== false ) {
			$name = substr($prop,$pos+1);
			//echo "** found # name $name for $prop  <br>\n";
			return $name;
		}
		//echo "no / ($pos) in $prop <br>\n";
		return $prop;
	}
	
	function getProperties($class) {
		if ( ! trim($class) ) {
			//echo "<!-- ********* getProperties empty class ********* -->\n";
			return false;
		}
		return $this->getCachedArray(array($this,'getPropertiesRaw'),array('store'=>$this->targetStoreUri,'class'=>$class),'has_class');
	}

	function getPropertiesRaw($args) { // ignore args
		$class = $args['class'];
		//echo "<!-- ********** getPropertiesRaw($class) ********** -->\n";
		$sparql = "select distinct ?p where { ?s a <{$class}>. ?s ?p ?o }";
		$properties = $this->targetStoreHelper->getSparqlResults($sparql,array("p"));
		if ( $properties === false ) {
			return $false;
		} else {
			$res = table_select_column($properties,'p');
			return $res;
		}
	}
	
	
	function getInstancesAndFields($class,$offset=0,$limit=10) {
		$rows = $this->getInstances($class,$offset,$limit);
		$num_rows =  count($rows);
		//echo "rows($table): ".print_r($rows,1) . " <br>\n";
		//echo "num_rows($table): $num_rows <br>\n";
		if ( $num_rows ) {
			$fields_present = array_unique( call_user_func_array( 'array_merge',  array_map( 'array_keys', $rows ) ) );
			$fields_present = array_diff( $fields_present, array('id','http://www.w3.org/1999/02/22-rdf-syntax-ns#type') );
			$fields = $this->addPropertyNames($fields_present);
		} else {
			$fields = array();
		}
		$num_fields =  count($fields);
		$result = array( 'num_rows' => $num_rows, 'rows' => $rows, 'num_fields' => $num_fields, 'fields' => $fields );
		return $result;
	}
	
	function getInstances($class,$offset=0,$limit=10) {
		$sparql = "SELECT ?s WHERE { ?s a <{$class}>. } LIMIT {$limit} OFFSET {$offset} ";
		//echo "getInstances($class,$offset,$limit): $sparql <br>\n";
		$subjects = $this->targetStoreHelper->getSparqlResults($sparql,array("s"));
		if ( $subjects === false ) {
			return $false;
		} 
		if ( count ($subjects) == 0 ) {
			// echo "***** NO instances of $class in " . $this->targetStoreHelper->storeUri . " with query $sparql<br>\n";
		}
		$res = array();
		$subjects_uris = get_named_table_col($subjects,'s');
		$triples = $this->targetStoreHelper->get_sparql_service()->describe_to_triple_list( $subjects_uris );
		$res = $this->collect_triples_by_subject($triples,'id');
		$res = array_values($res);
		//echo "result: " . print_r($res,1) . "  <br>\n";
		return $res;
	}
	
	
	function collect_triples_by_subject($triples,$idname=false) {
		$res = array();
		foreach ( $triples as $triple ) {
			$id = $triple['s'];
			if ( $idname ) $res[$id][$idname] = $id;
			$res[$id][$triple['p']] = $triple['o'];
		}
		return $res;
	}
	
}

?>