<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';

require_once MORIARTY_DIR. 'metabox.class.php';
require_once MORIARTY_DIR. 'sparqlservice.class.php';
require_once MORIARTY_DIR. 'multisparqlservice.class.php';
require_once MORIARTY_DIR. 'contentbox.class.php';
require_once MORIARTY_DIR. 'jobqueue.class.php';
require_once MORIARTY_DIR. 'config.class.php';
require_once MORIARTY_DIR. 'facetservice.class.php';
require_once MORIARTY_DIR. 'snapshots.class.php';
require_once MORIARTY_DIR. 'augmentservice.class.php';
require_once MORIARTY_DIR. 'oaiservice.class.php';

/**
* Represents a platform store.
*/
class Store {
  /** 
   * @access private 
   */
  var $uri;

  /** 
   * @access private 
   */
  var $credentials;

  /** 
   * @access private 
   */
  var $request_factory;


  /**
   * @param string $uri URI of the store
   * @param Credentials $credentials
   */ 
  function Store($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Obtain a reference to this store's metabox
   * @see http://n2.talis.com/wiki/Metabox
   * @return Metabox
   */
  function get_metabox() {
    return new Metabox($this->uri . '/meta', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's sparql service
   * @see http://n2.talis.com/wiki/Store_Sparql_Service
   * @return SparqlService
   */
  function get_sparql_service() {
    return new SparqlService($this->uri . '/services/sparql', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's multisparql service
   * @see http://n2.talis.com/wiki/Store_Multisparql_Service
   * @return MultiSparqlService
   */
  function get_multisparql_service() {
    return new MultiSparqlService($this->uri . '/services/multisparql', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's contentbox
   * @see http://n2.talis.com/wiki/Contentbox
   * @return Contentbox
   */
  function get_contentbox() {
    return new Contentbox($this->uri . '/items', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's job queue
   * @see http://n2.talis.com/wiki/Scheduled_Job_Collection
   * @return JobQueue
   */
  function get_job_queue() {
    return new JobQueue($this->uri . '/jobs', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's configuration
   * @see http://n2.talis.com/wiki/Store_Configuration
   * @return Config
   */
  function get_config() {
    return new Config($this->uri . '/config', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's facet service
   * @see http://n2.talis.com/wiki/Facet_Service
   * @return FacetService
   */
  function get_facet_service() {
    return new FacetService($this->uri . '/services/facet', $this->credentials, $this->request_factory);
  }


  /**
   * Obtain a reference to this store's OAI service
   * @see http://n2.talis.com/wiki/Store_OAI_Service
   * @return OAIService
   */
  function get_oai_service() {
    return new OAIService($this->uri . '/services/oai-pmh', $this->credentials, $this->request_factory);
  }
  /**
   * Obtain a reference to this store's snapshot collection
   * @see http://n2.talis.com/wiki/Snapshot_Collection
   * @return Snapshots
   */
  function get_snapshots() {
    return new Snapshots($this->uri . '/snapshots', $this->credentials, $this->request_factory);
  }

  /**
   * Obtain a reference to this store's augment service
   * @see http://n2.talis.com/wiki/Augment_Service
   * @return AugmentService
   */
  function get_augment_service() {
    return new AugmentService($this->uri . '/services/augment', $this->credentials, $this->request_factory);
  }


  function describe($uri, $type='cbd', $output='rdf') {
    if ( is_array( $uri ) || $type != 'cbd' ) {
      $ss = $this->get_sparql_service();
      return $ss->describe($uri, $type, $output);
    }
    else {
      $mb = $this->get_metabox();
      return $mb->describe($uri, $output);    
    }
  }


}
?>
