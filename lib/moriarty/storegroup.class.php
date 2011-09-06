<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'networkresource.class.php';
require_once MORIARTY_DIR. 'sparqlservice.class.php';
require_once MORIARTY_DIR. 'contentbox.class.php';
require_once MORIARTY_DIR. 'storegroupconfig.class.php';

/**
 * Represents a group of stores.
 */
class StoreGroup extends NetworkResource {

  /**
   * Create a new instance of this class
   * @param string uri URI of the store group
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null) {
    parent::__construct($uri, $credentials);
    $this->add_resource_triple($this->uri, RDF_TYPE, BF_STOREGROUP);
  }
  
  /**
   * Obtain a reference to this store group's sparql service
   * @see http://n2.talis.com/wiki/Store_Sparql_Service
   * @return SparqlService
   */
  function get_sparql_service() {
    return new SparqlService($this->uri . '/services/sparql', $this->credentials);
  }

  /**
   * Obtain a reference to this store group's configuration
   * @see http://n2.talis.com/wiki/Store_Configuration
   * @return StoreGroupConfig
   */
  function get_config() {
    return new StoreGroupConfig($this->uri . '/config', $this->credentials);
  }
  
  /**
   * Obtain a reference to this store group's contentbox
   * @see http://n2.talis.com/wiki/Contentbox
   * @return Contentbox
   */
  function get_contentbox() {
    return new Contentbox($this->uri . '/items', $this->credentials);
  }
  
  /**
   * Add a store to this group. Save the changes by calling put_to_network.
   * @param string store_uri the URI of the store to add to this group.
   */
  function add_store_by_uri($store_uri) {
    $this->add_resource_triple($this->uri, BF_STORE, $store_uri);
  }
  
  /**
   * Remove all stores from this group. Save the changes by calling put_to_network.
   */
  function remove_all_stores() {
    $this->remove_property_values($this->uri, BF_STORE);
  }
}
?>
