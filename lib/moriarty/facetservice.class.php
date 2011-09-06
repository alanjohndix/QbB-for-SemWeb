<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';

/**
 * Represents a store's facet service.
 * @see http://n2.talis.com/wiki/Facet_Service
 */
class FacetService {
  /**
   * @access private
   */
  var $uri;
  /**
   * @access private
   */
  var $request_factory;
  /**
   * @access private
   */
  var $credentials;

  /**
   * Create a new instance of this class
   * @param string uri URI of the facet service
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory= null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Perform a facet query
   * @param string query the query to execute
   * @param array fields the list of fields to facet on
   * @param in top the number of facet results to return
   * @return HttpResponse
   */
  function facets($query, $fields, $top = 10) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri . '?query=' . urlencode($query) . '&fields=' . urlencode(join(',', $fields)) . '&top=' . urlencode($top) . '&output=xml';
    $request = $this->request_factory->make( 'GET', $uri , $this->credentials );
    $request->set_accept(MIME_XML);
    return $request->execute();
  }

  /**
   * Perform a facet query and return the results as an array. An empty array is returned if there are any HTTP errors.
   * @param string query the query to execute
   * @param array fields the list of fields to facet on
   * @param in top the number of facet results to return
   * @return array see parse_facet_xml for the structure of this array
   */
  function facets_to_array($query, $fields, $top = 10) {
    $facets = array();
    $response = $this->facets($query, $fields, $top);
    if ($response->is_success()) {
      $facets = $this->parse_facet_xml($response->body);
    }
    return $facets;
  }

  /**
   * Parse the response from a facet query into an array.
   * This method returns an associative array where the keys correspond to field name and the values are
   * associative arrays with two keys:
   * <ul>
   * <li><em>value</em> => the value of the field</li>
   * <li><em>number</em> => the associated number returned by the facet service</li>
   * </ul>
   * @param string xml the facet response as an XML document
   * @return array
   */
  function parse_facet_xml($xml) {
    $facets = array();

    $reader = new XMLReader();
    $reader->XML($xml);

    $field_terms = array();
    $field_name = '';
    while ($reader->read()) {
      if ( $reader->name == 'field') {

        if ( $reader->nodeType == XMLReader::ELEMENT) {
          $field_terms = array();
          $field_name = $reader->getAttribute("name");
        }
        elseif ( $reader->nodeType == XMLReader::END_ELEMENT) {
          $facets[$field_name] = $field_terms;
          $field_terms = array();
        }
      }
      elseif ( $reader->name == 'term') {
        if ( $reader->nodeType == XMLReader::ELEMENT) {
          $term = array();
          $term['value'] = $reader->getAttribute("value");
          $term['number'] = $reader->getAttribute("number");
          $field_terms[] = $term;
        }
      }
    }
    $reader->close();

    return $facets;
  }

}
?>
