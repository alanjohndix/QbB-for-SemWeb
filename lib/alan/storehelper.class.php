<?php

class BaseHelper {
	function getSparqlResultsWhere($where,$vars) {
		
		$sparql = "select";
		foreach ( $vars as $var ) {
			$sparql .= "?" . $var;
		}
		
		$sparql .= " where { " . $where . " }";
		
		return $this->getSparqlResults($sparql,$vars);
	}

	function getOneSparqlResultWhere($where,$vars) {
		$results = $this->getSparqlResultsWhere($where,$vars);
		if ( $results === false ) {
			return false;
		} elseif ( count($results) == 0 ) {
			return array();
		} else {
			return $results[0];
		}
	}

	function getOneSparqlResult($sparql,$vars=false) {
		$results = $this->getSparqlResults($sparql,$vars);
		if ( $results === false ) {
			return false;
		} elseif ( count($results) == 0 ) {
			return array();
		} else {
			return $results[0];
		}
	}

	function getSparqlResults($sparql,$vars=false) {
		
		$storeSparql = $this->get_sparql_service();

	//	print_r($this->storeSparql);
//echo "\n==============\n\n";

		debug( "getSparqlResults($sparql)" );

		$response = $storeSparql->select($sparql);
	
		debug( "got response" );

	    //echo "getStoredPredicateStatistics:oops failed to run query:\n    " . $sparql ."\n=====\n    " . print_r($response,true) . "\n\n";

		if($response->is_success())
		{
		debug( "SUCCESS" );
			$values = array();
	        $results = $storeSparql->parse_select_results($response->body);
			//echo "****** results of $sparql : <br>\n" .print_r($results,1) . " <br>\n**********************<br>\n";
			foreach( $results as $oneresult ) {
				if ( $vars )  {
					$myvars = $vars;
				} else {
					$myvars = array_keys($oneresult);
				}
				$value = array();
				foreach( $myvars as $var ) {
					if ( array_key_exists( $var, $oneresult  ) ) {
						$value[$var] = $oneresult[$var]['value'];
					}
				}
				$values[] = $value;
			}
			return $values;
		}
		else
		{
		debug( "FAILED:" . $response->body );
			//echo "getSparqlResults:oops failed to run query:" . $response->body ."\n    " . $sparql . "\n\n";
			return false;
		}
 	
	}

}

class SparqlHelper extends BaseHelper {
	var $sparqlUri;
	function __construct($sparqlUri) {
		$this->sparqlUri = $sparqlUri;
	}
	function get_sparql_service() {
		return new SparqlService($this->sparqlUri);
	}
}

class StoreHelper extends BaseHelper {
	var $storeUri, $credentials, $store;
	function __construct($storeUri,$credentials=null) {
		$this->storeUri = $storeUri;
		$this->store = new Store($this->storeUri,$credentials);
		if ( ! $this->store ) {
			echo "failed to create store for {$this->storeUri} <br>\n";
		}
	}
	function addGraph($graph) {
		return $this->store->get_metabox()->submit_rdfxml($graph->to_rdfxml());
	}
	function get_sparql_service() {
		return $this->store->get_sparql_service();
	}
}



?>