<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Query by Browsing for SemWeb - documentation</title>
<link href="assets/css/qbb.css" rel="stylesheet" type="text/css" />
<link rel='stylesheet' id='syntaxhighlighter-core-css'  href='http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/styles/shCore.css?ver=2.0.320' type='text/css' media='all' />

<link rel='stylesheet' id='syntaxhighlighter-theme-default-css'  href='http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/styles/shThemeDefault.css?ver=2.0.320' type='text/css' media='all' />
<style type="text/css">.syntaxhighlighter { font-size: 12px !important; }</style>
</head>

<body>
<h1>Query by Browsing for SemWeb &ndash; documentation</h1>
<p><a href="http://www.alandix.com/">Alan Dix</a>, <a href="http://talis.com/">Talis</a>, 4th May 2011</p>
<h2>What is QbB?</h2>
<p>The basic concept of Query-by-Browsing (QbB) is to use machine learning in order to generate queries directly from examples of required data. Whilst most query mechanisms (even the slightly misnamed &quot;Query-by-Example&quot;) focus initially on the schema of the query (intention), QbB focuses initially on the data itself (extension). </p>
<p>The example below shows four rows of data (listing entities of a specific RDF type), which have been classified by the user. Query-by-Browsing has generated a SPARQL query that correctly classifies the selected data (although may not be the query the user intends). The query is shown to the user and also further (previously unclassified) records will be highlighted in order to allow users to verify whether it is indeed the desired selection, or if not allowing them to hand pick further rows of data and so refine the query.</p>
<p>The fundamental idea is that while the user may not be able (or may not wish) to  generate SPARQL dircetly, they will be able to use the combination of reading the query (easier than writing it) together with the examples of data selected by the query, in order to ascertain when it is correct.</p>
<blockquote><table><tr><td valign="middle"><img src="/talis/qbb/images/data-1.png" width="240" height="137" /></td><td valign="middle">&nbsp;&nbsp;=&gt;&nbsp;&nbsp;</td><td valign="middle"><img src="/talis/qbb/images/query-1b.png" width="370" height="130" /></td></tr></table></blockquote>

<h2>Example Front End</h2>
<p>A (very crude!) implementation of QbB for RDF data is available (<a href="/talis/qbb/qbb.php">see here</a>).</p>
<p>This is still under development, being initially a port of an earlier RDMS-based version. In particular, more of the interactive aspects are being moved from server-based code to be browser based using AJAX.</p>
<p>Some of the core functionality has already been moved into a web service backend API  for use either server-sdie or client-side (API documentation below)</p>
<h2>History of  QbB </h2>
<p>Quert-by-Browsing was originally posited in 1991 as part of an invited talk at a workshop on neural-nets and pattern matching in HCI. It was an envisaged application used to demonstrate potential problems, and some solutions in producing 'intelligent' interfaces in general. It was subsequently described in a book chapter in 1992 [<a href="#ref_1">ref 1</a>] and first implemented in 1994 [<a href="#ref_2">ref 2</a>] .</p>
<p>The first implementation was as a stand-alone application for relational/tabular data, but a web-based version based on a MySQL backened is also available for demonstration (<a href="http://www.meandeviation.com/qbb/qbb.php">see here</a>).</p>
<p>While QbB focuses on the filtering/selection side of query formation, a related technique, Query-through-Drilldown (QtD) was developed in order to address the formation of joins. While this was first formulated in the late 1990s, the first implemention was not produced until 2008 [<a href="#ref_4">ref 4</a>] .</p>
<p>Like the initial QbB, QtD was focused on relational data and SQL, and the current project is aimed at transfering this technology to RDF triple stores and SPARQL.</p>
<p>&nbsp;</p>


<h2>Backend API</h2>

<p>The example front-end uses three web service endpoints. </p>
<dl>
  <dt>source.php</dt>
  <dd>just a wrapper around the data source</dd>
  <dt>learn.php</dt>
  <dd>the main machine-learning module</dd>
  <dt>tree.php</dt>
  <dd>utility for managing decision tree, queries and data</dd>
</dl>
<h4>Common Request Parameters</h4>
<p>The following parameters are expected by all API services:</p>
<dl>
  <dt><strong>version=0.1</strong></dt>
  <dd>the API version number</dd>
  <dt><strong></strong></dt>
  <dt><strong>op</strong></dt>
  <dd>sub operation code for the request url<br />
    some request URIs have only one opertaion, but some (store.php) support several
  </dd>
  <dt><strong>format</strong> (optional)</dt>
  <dd>resut format: json (defailt) or jsonp</dd>
  <dt><strong>callback </strong>(optional)</dt>
  <dd>Javascript callback function name, must be provided if format=jsonp</dd>
</dl>
<p>Other parameters are defined below for each operation.</p>
<p>Array input parameters can be supplied using either JSON or  PHP-style array encodings, but  return results in JSON/JSONP.</p>
<h4>Common Response</h4>
<p>All API srevices provide JSON encoded result, an associative array including the following elements</p>
<dl>
  <dt>&nbsp; </dt>
  <dt><strong>status</strong>:</dt>
  <dd> true/false<br />
    true means OK, false measn some error occured</dd>
  <dt><strong>result</strong> (optional):</dt>
  <dd>only present if status=true (success)<br />
  actual ouptut of the operation, the details of which differ and are described with each individual service below.</dd>
  <dt></dt>
  <dt><strong>message </strong>(optional):</dt>
  <dd>explanatory error message present if status is false (failure/error)<br />
    currently you need to match this string to see what sort of failure occurred, maybe in the future include numeric code also
  </dd>
  <dt><strong></strong></dt>
  <dt><strong>startTime</strong>, <strong>endTime</strong>, <strong>elapsedTime</strong>:</dt>
  <dd>server-side recorded timings</dd>
  <dt><strong>debug</strong>:</dt>
  <dd>debug information! array of individual debug message lines</dd>
  <dt>&nbsp;</dt>
</dl>
<h3>learn - Use Machine Learning to Generate SPARQL Query</h3>

<h4> Request URL
</h4>
<p>http://tiree.snipit.org/talis/qbb/api/learn.php</p>
<h4>Method </h4>
<p>POST or GET, POST recommended as URL may get too long for server with GET</p>
<h4>Request Parameters</h4>
<dl>
  <dt><strong>version=0.1</strong></dt>
  <dt><strong></strong></dt>
  <dt><strong>version=learn</strong></dt>
  <dd>&nbsp;</dd>
  <dt><strong></strong></dt>
  <dt>&nbsp;</dt>
  <dt><strong></strong></dt>
  <dt><strong>fields</strong></dt>
  <dd>a 2 dimensional array representing the columns to be used in the learning<br />
    fields[col_index]['id'] = url of property defining column, or could be any unique id such as column number<br />
    fields[col_index]['name'] = short name as used in column headings, used for query variables</dd>
  <dt><strong>yes_rows</strong></dt>
  <dd>a 2 dimensional array representing the positive examples of desired entities<br />
    yes_rows[row_index][property_id] = value
    <br />
    when the property_id is one of the urls (or non-url ids) in the fields array, this property will be used for learning, otherwise ignored.<br />
    Note, that there is a special property_id 'id' the value of which is the url of the entity represented by the row</dd>
  <dt><strong>no_rows</strong></dt><dd>a 2 dimensional array representing the negative examples in exactly the same format as the yes_rows</dd>
</dl>
<h4>Response</h4>
<p>JSON encoded result, an associative array with the following elements</p>
<dl><dt>&nbsp;
  </dt>
  <dt><strong>status, message, startTime</strong>, <strong>endTime</strong>, <strong>elapsedTime, debug</strong>:</dt>
  <dd>common response variables</dd>
  <dt>&nbsp;</dt>
  <dt><strong>result</strong>:</dt>
  <dd>internal representation of the decision tree (use tree services to convert to other forms)</dd>
  <dt>&nbsp;</dt>
  <dt>&nbsp;</dt>
  <dt>&nbsp;</dt>
</dl>
<h4>Example in PHP Code</h4>
<pre  class="brush: php;">function do_learn($fields,$yes_rows,$no_rows) {
	$learn_service = new JSONRemoteService( API_URL . &quot;learn.php&quot;, '0.1');
	$learn_params = array( 'fields'=&gt;json_encode($fields), 'yes_rows'=&gt;$yes_rows, 'no_rows'=&gt;$no_rows );
	$result = $learn_service-&gt;do_op( 'learn', $learn_params );<br />	return $result;
 }</pre>
<p>&nbsp;</p>
<h4>Example Data</h4>
<p>Here is an example corresponding to the selected rows in the following:</p>
<blockquote><table><tr><td valign="middle"><img src="/talis/qbb/images/data-1.png" width="240" height="137" /></td><td valign="middle">&nbsp;&nbsp;=&gt;&nbsp;&nbsp;</td><td valign="middle"><img src="/talis/qbb/images/query-1b.png" width="370" height="130" /></td></tr></table></blockquote>
<p>Try it: <a href="http://tiree.snipit.org/talis/qbb/api/learn.php?version=0.1&amp;op=learn&amp;type=http://www.alandix.com/example#&amp;fields=[{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/name&quot;,&quot;name&quot;:&quot;name&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/contact&quot;,&quot;name&quot;:&quot;contact&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/balance&quot;,&quot;name&quot;:&quot;balance&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/actlimit&quot;,&quot;name&quot;:&quot;actlimit&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Wage&quot;,&quot;name&quot;:&quot;Wage&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Title&quot;,&quot;name&quot;:&quot;Title&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Overdraft&quot;,&quot;name&quot;:&quot;Overdraft&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Name&quot;,&quot;name&quot;:&quot;Name&quot;}]&amp;yes_rows[0][id]=http://alandix.com/schema/entity/Fe6ppKrx58NUrDoxesqWtg&amp;yes_rows[0][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#&amp;yes_rows[0][http://www.alandix.com/example#/name]=Evans&amp;yes_rows[0][http://www.alandix.com/example#/contact]=Jones&amp;yes_rows[0][http://www.alandix.com/example#/balance]=50&amp;yes_rows[0][http://www.alandix.com/example#/actlimit]=100&amp;yes_rows[1][id]=http://alandix.com/schema/entity/B-RJ0pOw2IcKfOGIskfnxA&amp;yes_rows[1][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#&amp;yes_rows[1][http://www.alandix.com/example#/name]=Smith&amp;yes_rows[1][http://www.alandix.com/example#/contact]=Smith&amp;yes_rows[1][http://www.alandix.com/example#/balance]=-100&amp;yes_rows[1][http://www.alandix.com/example#/actlimit]=-50&amp;no_rows[0][id]=http://alandix.com/schema/entity/-tXYVHXNKYOyk_5UP0EsuQ&amp;no_rows[0][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#&amp;no_rows[0][http://www.alandix.com/example#/name]=Rogers&amp;no_rows[0][http://www.alandix.com/example#/contact]=Jones&amp;no_rows[0][http://www.alandix.com/example#/balance]=-100&amp;no_rows[0][http://www.alandix.com/example#/actlimit]=0&amp;no_rows[1][id]=http://alandix.com/schema/entity/-WutjbRCM-lJiIXiYAv1jQ&amp;no_rows[1][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#&amp;no_rows[1][http://www.alandix.com/example#/name]=Smith&amp;no_rows[1][http://www.alandix.com/example#/contact]=Smith&amp;no_rows[1][http://www.alandix.com/example#/balance]=-50&amp;no_rows[1][http://www.alandix.com/example#/actlimit]=100">http://tiree.snipit.org/talis/qbb/api/learn.php?type=http://www.alandix.com/example#</span>&amp;fields...</a></p>
<p><strong>Request</strong> (mix of JSON and PHP-style arrays, line breaks and indentation added for readability) </p>

<pre class="brush: plain;">
http://tiree.snipit.org/talis/qbb/api/learn.php?

    version=0.1
    &amp;op=learn

    &amp;fields=[{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/name&quot;,&quot;name&quot;:&quot;name&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/contact&quot;,&quot;name&quot;:&quot;contact&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/balance&quot;,&quot;name&quot;:&quot;balance&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/actlimit&quot;,&quot;name&quot;:&quot;actlimit&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Wage&quot;,&quot;name&quot;:&quot;Wage&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Title&quot;,&quot;name&quot;:&quot;Title&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Overdraft&quot;,&quot;name&quot;:&quot;Overdraft&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example#\/Name&quot;,&quot;name&quot;:&quot;Name&quot;}]

    &amp;yes_rows[0][id]=http://alandix.com/schema/entity/Fe6ppKrx58NUrDoxesqWtg  
    &amp;yes_rows[0][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#  
    &amp;yes_rows[0][http://www.alandix.com/example#/name]=Evans  
    &amp;yes_rows[0][http://www.alandix.com/example#/contact]=Jones  
    &amp;yes_rows[0][http://www.alandix.com/example#/balance]=50  
    &amp;yes_rows[0][http://www.alandix.com/example#/actlimit]=100  
    &amp;yes_rows[1][id]=http://alandix.com/schema/entity/B-RJ0pOw2IcKfOGIskfnxA  
    &amp;yes_rows[1][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#  
    &amp;yes_rows[1][http://www.alandix.com/example#/name]=Smith  
    &amp;yes_rows[1][http://www.alandix.com/example#/contact]=Smith  
    &amp;yes_rows[1][http://www.alandix.com/example#/balance]=-100  
    &amp;yes_rows[1][http://www.alandix.com/example#/actlimit]=-50
  
    &amp;no_rows[0][id]=http://alandix.com/schema/entity/-tXYVHXNKYOyk_5UP0EsuQ
    &amp;no_rows[0][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#  
    &amp;no_rows[0][http://www.alandix.com/example#/name]=Rogers  
    &amp;no_rows[0][http://www.alandix.com/example#/contact]=Jones  
    &amp;no_rows[0][http://www.alandix.com/example#/balance]=-100
    &amp;no_rows[0][http://www.alandix.com/example#/actlimit]=0  
    &amp;no_rows[1][id]=http://alandix.com/schema/entity/-WutjbRCM-lJiIXiYAv1jQ
    &amp;no_rows[1][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#  
    &amp;no_rows[1][http://www.alandix.com/example#/name]=Smith
    &amp;no_rows[1][http://www.alandix.com/example#/contact]=Smith
    &amp;no_rows[1][http://www.alandix.com/example#/balance]=-50  
    &amp;no_rows[1][http://www.alandix.com/example#/actlimit]=100 
</pre>
<!--
    &amp;fields[0][id]=http://www.alandix.com/example#/name
    &amp;fields[0][name]=name
    &amp;fields[1][id]=http://www.alandix.com/example#/contact
    &amp;fields[1][name]=contact
    &amp;fields[2][id]=http://www.alandix.com/example#/balance
    &amp;fields[2][name]=balance
    &amp;fields[3][id]=http://www.alandix.com/example#/actlimit
    &amp;fields[3][name]=actlimit
-->

<p> <strong>Response</strong> (line breaks and indentation added for readability) </p>
<pre class="brush: plain;">{
  &quot;status&quot;:&quot;true&quot;,
  &quot;result&quot;:{&quot;kind&quot;:&quot;CHOOSE&quot;,
          &quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,
                    &quot;field&quot;:&quot;http:\/\/www.alandix.com\/example#\/name&quot;,
                    &quot;operator&quot;:&quot;=&quot;,
                    &quot;arg_type&quot;:&quot;LITERAL&quot;,
                    &quot;arg&quot;:&quot;Evans&quot;},
          &quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},
          &quot;no_branch&quot;:{&quot;kind&quot;:&quot;CHOOSE&quot;,
                       &quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,
                                 &quot;field&quot;:&quot;http:\/\/www.alandix.com\/example#\/actlimit&quot;,
                                 &quot;operator&quot;:&quot;=&quot;,
                                 &quot;arg_type&quot;:&quot;LITERAL&quot;,
                                 &quot;arg&quot;:-50},
                                 &quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},
                                 &quot;no_branch&quot;:{&quot;kind&quot;:&quot;NO&quot;}}},
  &quot;startTime&quot;:1304443269,
  &quot;endTime&quot;:1304443269,
  &quot;elapsedTime&quot;:0,
  &quot;debug&quot;:[&quot;1304443269 :LOG :starting&quot;,&quot;1304443269 :LOG :learn_tree&quot;,&quot;1304443269 :LOG :learn_tree returns&quot;,&quot;1304443269 :LOG :finished&quot;]}</pre>



<h3><strong>tree_to_sparql</strong> - Convert Decision Tree to SPARQL Query</h3>
<p>Classifies entities using a decsion tree create using the learn API call. This should give the same result as applying the SPARQL query.</p>
<h4>Request URL </h4>
<p>http://tiree.snipit.org/talis/qbb/api/tree.php</p>
<h4>Method </h4>
<p>POST or GET, POST recommended as URL may get too long for server with GET</p>
<h4></h4>
<h4>Request Parameters</h4>
<dl>
  <dt><strong>version=0.1</strong></dt>
  <dd>the API version number</dd>
  <dt><strong></strong></dt>
  <dt><strong>op=tree_to_sparql</strong></dt>
  <dd>fixed value giving sub op for request url</dd>
  <dt><strong>type</strong></dt>
  <dd>the RDF type of the entities being filtered</dd>
  <dt><strong></strong></dt>
  <dt><strong>fields</strong></dt>
  <dd>a 2 dimensional array representing the columns to be used in the learning<br />
    fields[col_index]['id'] = url of property defining column<br />
    fields[col_index]['name'] = short name as used in column headings, used for query variables and variable names in the tree</dd>
  <dt><strong>tree</strong></dt>
  <dd>an array using the internal coding as returned by learn</dd>
</dl>
  <h4>Response</h4>
<p>JSON encoded result, an associative array with the following elements</p>
  <dl>
    <dt>&nbsp;</dt>
    <dt><strong>status, message, startTime</strong>, <strong>endTime</strong>, <strong>elapsedTime, debug</strong>:</dt>
    <dd>common response variables</dd>
    <dt><strong>result['mix']</strong>:</dt>
    <dd> TRUE/FALSE/MIXED<br />
      indicator to say whether the result is one which selects everything (TRUE), selects nothing (FALSE), or is a mixed result (MIXED).</dd>
    <dt>&nbsp;</dt>
    <dt><strong>result['sparql']</strong>:</dt>
    <dd>SPARQL query corresponding to the decision tree<br />
      Note: It may be more logical in future versions to move the transformation of the internal tree representation to SPARQL into a separate API call.</dd>
    <dt>&nbsp;</dt>
    <dt>&nbsp;</dt>
  </dl>
  <h4>Example in PHP Code</h4>
  <pre  class="brush: php;">		$tree_service = new JSONRemoteService( API_URL . &quot;tree.php&quot;, '0.1');<br />		$tree_to_sparql_params = array( 'type'=&gt;$table, 'fields'=&gt;json_encode($fields), 'tree'=&gt;json_encode($tree) );<br />		$tree_to_sparql = $tree_service-&gt;do_op( 'tree_to_sparql', $tree_to_sparql_params );</pre>
  <p></p>
  <h4>Example data</h4>
<p>
Try it: <a href="http://tiree.snipit.org/talis/qbb/api/tree.php?version=0.1&amp;op=tree_to_sparql&amp;type=http://www.alandix.com/example%23&amp;fields=[{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/name&quot;,&quot;name&quot;:&quot;name&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/contact&quot;,&quot;name&quot;:&quot;contact&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/balance&quot;,&quot;name&quot;:&quot;balance&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/actlimit&quot;,&quot;name&quot;:&quot;actlimit&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Wage&quot;,&quot;name&quot;:&quot;Wage&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Title&quot;,&quot;name&quot;:&quot;Title&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Overdraft&quot;,&quot;name&quot;:&quot;Overdraft&quot;},{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Name&quot;,&quot;name&quot;:&quot;Name&quot;}]&amp;tree={&quot;kind&quot;:&quot;CHOOSE&quot;,&quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,&quot;field&quot;:&quot;http:\/\/www.alandix.com\/example%23\/name&quot;,&quot;operator&quot;:&quot;=&quot;,&quot;arg_type&quot;:&quot;LITERAL&quot;,&quot;arg&quot;:&quot;Evans&quot;},&quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},&quot;no_branch&quot;:{&quot;kind&quot;:&quot;CHOOSE&quot;,&quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,&quot;field&quot;:&quot;http:\/\/www.alandix.com\/example%23\/actlimit&quot;,&quot;operator&quot;:&quot;=&quot;,&quot;arg_type&quot;:&quot;LITERAL&quot;,&quot;arg&quot;:-50},&quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},&quot;no_branch&quot;:{&quot;kind&quot;:&quot;NO&quot;}}}">http://tiree.snipit.org/talis/qbb/api/tree.php?version=0.1&amp;op=tree_to_sparql&amp;type=...</a></p>
<p><strong>Request</strong></p>

<pre  class="brush: php;">
http://tiree.snipit.org/talis/qbb/api/tree.php?

    version=0.1
    &amp;op=tree_to_sparql

    &amp;type=http://www.alandix.com/example%23

    &amp;fields=[{&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/name&quot;,&quot;name&quot;:&quot;name&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/contact&quot;,&quot;name&quot;:&quot;contact&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/balance&quot;,&quot;name&quot;:&quot;balance&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/actlimit&quot;,&quot;name&quot;:&quot;actlimit&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Wage&quot;,&quot;name&quot;:&quot;Wage&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Title&quot;,&quot;name&quot;:&quot;Title&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Overdraft&quot;,&quot;name&quot;:&quot;Overdraft&quot;},
             {&quot;id&quot;:&quot;http:\/\/www.alandix.com\/example%23\/Name&quot;,&quot;name&quot;:&quot;Name&quot;}]

    &amp;tree={&quot;kind&quot;:&quot;CHOOSE&quot;,
           &quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,
                     &quot;field&quot;:&quot;http:\/\/www.alandix.com\/example%23\/name&quot;,
                     &quot;operator&quot;:&quot;=&quot;,
                     &quot;arg_type&quot;:&quot;LITERAL&quot;,
                     &quot;arg&quot;:&quot;Evans&quot;},
           &quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},
           &quot;no_branch&quot;:{&quot;kind&quot;:&quot;CHOOSE&quot;,
                        &quot;choice&quot;:{&quot;kind&quot;:&quot;COMPARE_EQUAL&quot;,
                                  &quot;field&quot;:&quot;http:\/\/www.alandix.com\/example%23\/actlimit&quot;,
                                  &quot;operator&quot;:&quot;=&quot;,
                                  &quot;arg_type&quot;:&quot;LITERAL&quot;,&quot;arg&quot;:-50},
                        &quot;yes_branch&quot;:{&quot;kind&quot;:&quot;YES&quot;},
                        &quot;no_branch&quot;:{&quot;kind&quot;:&quot;NO&quot;}}}
</pre>
<p><strong>Response</strong></p>
<pre  class="brush: php;">
{
  &quot;status&quot;:true,
  &quot;result&quot;:{&quot;mix&quot;:&quot;MIXED&quot;,
            &quot;sparql&quot;:&quot;SELECT ?actlimit ?name \nWHERE {\n  ?s &lt;http:\/\/www.w3.org\/1999\/02\/22-rdf-syntax-ns#type&gt; &lt;http:\/\/www.alandix.com\/example#&gt; .\n  ?s &lt;http:\/\/www.alandix.com\/example#\/actlimit&gt; ?actlimit .\n  ?s &lt;http:\/\/www.alandix.com\/example#\/name&gt; ?name .\n   FILTER( ( name=Evans ) ||  (actlimit=-50)) .\n}&quot;
           },
  &quot;startTime&quot;:1304524401,
  &quot;endTime&quot;:1304524401,
  &quot;elapsedTime&quot;:0,
  &quot;debug&quot;:[]
}</pre>
<h3><strong>apply_to_tree</strong> - Classify Rows Using Decision Tree</h3>

<p>Classifies entities using a decsion tree create using the learn API call. This should give the same result as applying the SPARQL query.</p>
<h4>Request URL </h4>
<p>http://tiree.snipit.org/talis/qbb/api/tree.php</p>
<h4>Method </h4>
<p>POST or GET, POST recommended as URL may get too long for server with GET</p>
<h4></h4>
<h4>Request Parameters</h4>
<dl>
  <dt><strong>version=0.1</strong></dt>
  <dd>the API version number</dd>
  <dt><strong></strong></dt>
  <dt><strong>op=apply_to_tree</strong></dt>
  <dd>fixed value giving sub op for request url</dd>
  <dt><strong>tree</strong></dt>
  <dd>an array using the internal coding as returned by learn</dd>
  <dt><strong>rows</strong></dt>
  <dd>a 2 dimensional array representing the entities to be classified<br />
    uses the same row encoding as learn</dd>
</dl>
<h4>Response</h4>
<p>JSON encoded result, an associative array with the following elements</p>
<dl>
  <dt><strong>status, message, startTime</strong>, <strong>endTime</strong>, <strong>elapsedTime, debug</strong>:</dt>
  <dd>common response variables</dd>
  <dt>&nbsp;</dt>
  <dt><strong>result</strong>:</dt>
  <dd>array of boolean values representing each entity of the request rows</dd>
  <dt>&nbsp;</dt>
</dl>
<h4>Example in PHP Code</h4>
<pre  class="brush: php;">
$tree_url = API_URL . &quot;tree.php&quot;;
$selected = do_json_remote_op( $tree_url, 'apply_to_rows', array('tree'=&gt;$tree,'rows'=&gt;$rows) );</pre>
<p>&nbsp;</p>
<h4>Example Data</h4>
<p>Try it <a href="http://tiree.snipit.org/talis/qbb/api/tree.php?version=0.1&amp;op=apply_to_rows&amp;tree[kind]=CHOOSE&amp;tree[choice][kind]=COMPARE_EQUAL&amp;tree[choice][field]=http%3A//www.alandix.com/example%23/name&amp;tree[choice][operator]==&amp;tree[choice][arg_type]=LITERAL&amp;tree[choice][arg]=Evans&amp;tree[yes_branch][kind]=YES&amp;tree[no_branch][kind]=CHOOSE&amp;tree[no_branch][choice][kind]=COMPARE_EQUAL&amp;tree[no_branch][choice][field]=http%3A//www.alandix.com/example%23/actlimit&amp;tree[no_branch][choice][operator]==&amp;tree[no_branch][choice][arg_type]=LITERAL&amp;tree[no_branch][choice][arg]=-50&amp;tree[no_branch][yes_branch][kind]=YES&amp;tree[no_branch][no_branch][kind]=NO&amp;rows[0][id]=http%3A//alandix.com/schema/entity/Fe6ppKrx58NUrDoxesqWtg&amp;rows[0][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[0][http%3A//www.alandix.com/example%23/name]=Evans&amp;rows[0][http%3A//www.alandix.com/example%23/contact]=Jones&amp;rows[0][http%3A//www.alandix.com/example%23/balance]=50&amp;rows[0][http%3A//www.alandix.com/example%23/actlimit]=100&amp;rows[1][id]=http%3A//alandix.com/schema/entity/-tXYVHXNKYOyk_5UP0EsuQ&amp;rows[1][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[1][http%3A//www.alandix.com/example%23/name]=Rogers&amp;rows[1][http%3A//www.alandix.com/example%23/contact]=Jones&amp;rows[1][http%3A//www.alandix.com/example%23/balance]=-100&amp;rows[1][http%3A//www.alandix.com/example%23/actlimit]=0&amp;rows[2][id]=http%3A//alandix.com/schema/entity/B-RJ0pOw2IcKfOGIskfnxA&amp;rows[2][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[2][http%3A//www.alandix.com/example%23/name]=Smith&amp;rows[2][http%3A//www.alandix.com/example%23/contact]=Smith&amp;rows[2][http%3A//www.alandix.com/example%23/balance]=-100&amp;rows[2][http%3A//www.alandix.com/example%23/actlimit]=-50&amp;rows[3][id]=http%3A//alandix.com/schema/entity/-WutjbRCM-lJiIXiYAv1jQ&amp;rows[3][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[3][http%3A//www.alandix.com/example%23/name]=Smith&amp;rows[3][http%3A//www.alandix.com/example%23/contact]=Smith&amp;rows[3][http%3A//www.alandix.com/example%23/balance]=-50&amp;rows[3][http%3A//www.alandix.com/example%23/actlimit]=100&amp;rows[4][id]=http%3A//alandix.com/schema/entity/-cFFb_3mlyZgproCtHj4Fg&amp;rows[4][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[4][http%3A//www.alandix.com/example%23/Wage]=15000&amp;rows[4][http%3A//www.alandix.com/example%23/Title]=Mr&amp;rows[4][http%3A//www.alandix.com/example%23/Overdraft]=100&amp;rows[4][http%3A//www.alandix.com/example%23/Name]=Tom&amp;rows[5][id]=http%3A//alandix.com/schema/entity/Ljc36yj-11-OZAjPRlKz_g&amp;rows[5][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[5][http%3A//www.alandix.com/example%23/name]=Rogers+&amp;rows[5][http%3A//www.alandix.com/example%23/contact]=Rogers&amp;rows[5][http%3A//www.alandix.com/example%23/balance]=-100&amp;rows[5][http%3A//www.alandix.com/example%23/actlimit]=-200&amp;rows[6][id]=http%3A//alandix.com/schema/entity/GZApP2btL614Qp5aQ3Ybcg&amp;rows[6][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[6][http%3A//www.alandix.com/example%23/Wage]=15000&amp;rows[6][http%3A//www.alandix.com/example%23/Title]=Mr&amp;rows[6][http%3A//www.alandix.com/example%23/Overdraft]=100&amp;rows[6][http%3A//www.alandix.com/example%23/Name]=Tom&amp;rows[7][id]=http%3A//alandix.com/schema/entity/3kx_A9W0bwaODc66hpNz9Q&amp;rows[7][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[7][http%3A//www.alandix.com/example%23/Wage]=20000&amp;rows[7][http%3A//www.alandix.com/example%23/Title]=Ms&amp;rows[7][http%3A//www.alandix.com/example%23/Overdraft]=-5000&amp;rows[7][http%3A//www.alandix.com/example%23/Name]=Jane&amp;rows[8][id]=http%3A//alandix.com/schema/entity/ETtKdj5dyvK3MA2DlX8hYw&amp;rows[8][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[8][http%3A//www.alandix.com/example%23/Wage]=10000&amp;rows[8][http%3A//www.alandix.com/example%23/Title]=Mr&amp;rows[8][http%3A//www.alandix.com/example%23/Overdraft]=50&amp;rows[8][http%3A//www.alandix.com/example%23/Name]=Dick&amp;rows[9][id]=http%3A//alandix.com/schema/entity/JRiNEOSpCV_Zbb47FJZdKg&amp;rows[9][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[9][http%3A//www.alandix.com/example%23/Wage]=20000&amp;rows[9][http%3A//www.alandix.com/example%23/Title]=Dr&amp;rows[9][http%3A//www.alandix.com/example%23/Overdraft]=10000&amp;rows[9][http%3A//www.alandix.com/example%23/Name]=John&amp;rows[10][id]=http%3A//alandix.com/schema/entity/41oMcy_hXs9DpjmVLwdBPA&amp;rows[10][http%3A//www.w3.org/1999/02/22-rdf-syntax-ns%23type]=http%3A//www.alandix.com/example%23&amp;rows[10][http%3A//www.alandix.com/example%23/name]=Jones&amp;rows[10][http%3A//www.alandix.com/example%23/contact]=Jones&amp;rows[10][http%3A//www.alandix.com/example%23/balance]=-30&amp;rows[10][http%3A//www.alandix.com/example%23/actlimit]=60">http://tiree.snipit.org/talis/qbb/api/tree.php?op=apply_to_rows&amp;tree=...</a></p>
<p>(N.B. 11 rows only as URL too long for GET, example below has 20)</p>
<p><strong>Request</strong></p>
<pre class="brush: plain;">
http://tiree.snipit.org/talis/qbb/api/tree.php?

    version=0.1
    &amp;op=apply_to_rows

    &amp;tree[kind]=CHOOSE
    &amp;tree[choice][kind]=COMPARE_EQUAL
    &amp;tree[choice][field]=http://www.alandix.com/example#/name
    &amp;tree[choice][operator]==
    &amp;tree[choice][arg_type]=LITERAL
    &amp;tree[choice][arg]=Evans
    &amp;tree[yes_branch][kind]=YES
    &amp;tree[no_branch][kind]=CHOOSE
    &amp;tree[no_branch][choice][kind]=COMPARE_EQUAL
    &amp;tree[no_branch][choice][field]=http://www.alandix.com/example#/actlimit
    &amp;tree[no_branch][choice][operator]==
    &amp;tree[no_branch][choice][arg_type]=LITERAL
    &amp;tree[no_branch][choice][arg]=-50
    &amp;tree[no_branch][yes_branch][kind]=YES
    &amp;tree[no_branch][no_branch][kind]=NO

    &amp;rows[0][id]=http://alandix.com/schema/entity/Fe6ppKrx58NUrDoxesqWtg
    &amp;rows[0][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#
    &amp;rows[0][http://www.alandix.com/example#/name]=Evans
    &amp;rows[0][http://www.alandix.com/example#/contact]=Jones
    &amp;rows[0][http://www.alandix.com/example#/balance]=50
    &amp;rows[0][http://www.alandix.com/example#/actlimit]=100

    &amp;rows[1][id]=http://alandix.com/schema/entity/-tXYVHXNKYOyk_5UP0EsuQ
    &amp;rows[1][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#
    &amp;rows[1][http://www.alandix.com/example#/name]=Rogers
    &amp;rows[1][http://www.alandix.com/example#/contact]=Jones
    &amp;rows[1][http://www.alandix.com/example#/balance]=-100
    &amp;rows[1][http://www.alandix.com/example#/actlimit]=0

    &amp;rows[2][id]=http://alandix.com/schema/entity/B-RJ0pOw2IcKfOGIskfnxA
    &amp;rows[2][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#
    &amp;rows[2][http://www.alandix.com/example#/name]=Smith
    &amp;rows[2][http://www.alandix.com/example#/contact]=Smith
    &amp;rows[2][http://www.alandix.com/example#/balance]=-100
    &amp;rows[2][http://www.alandix.com/example#/actlimit]=-50
    
    ... lots more row entries ...

    &amp;rows[19][id]=http://alandix.com/schema/entity/-XkePDA2tMuIGkScb0kFYA
    &amp;rows[19][http://www.w3.org/1999/02/22-rdf-syntax-ns#type]=http://www.alandix.com/example#
    &amp;rows[19][http://www.alandix.com/example#/Wage]=10000
    &amp;rows[19][http://www.alandix.com/example#/Title]=Ms
    &amp;rows[19][http://www.alandix.com/example#/Overdraft]=0
    &amp;rows[19][http://www.alandix.com/example#/Name]=Sue

</pre>
<p class="brush: plain;"><br>
<strong>Response</strong></p>
<pre  class="brush: plain;">{
  &quot;status&quot;:true,
  &quot;result&quot;:[true,false,true,false,false,false,false,false,false,false,false,false,true,false,false,true,false,false,false,false],
  &quot;startTime&quot;:1304514388,
  &quot;endTime&quot;:1304514388,
  &quot;elapsedTime&quot;:0,
  &quot;debug&quot;:[&quot;1304514388 :LOG :num_rows = 11&quot;]
}
</pre>
<h2  >References</h2>
<ol>
  <li><a name="ref_1">A. Dix</a> (1992).  <strong>Human issues in the use of pattern recognition techniques.</strong> In <em>Neural Networks and Pattern Recognition in Human Computer Interaction</em> Eds. R. Beale and J. Finlay. Ellis Horwood. 429-451. <a href="http://hcibook.com/alan/papers/neuro92/neuro92.html">http://hcibook.com/alan/papers/neuro92/neuro92.html</a></li>
  <li><a name="ref_2">A. Dix and A. Patrick</a> (1994). <strong>Query By Browsing.</strong> <em>Proceedings of IDS'94: The 2nd International Workshop on User Interfaces to Databases,</em> Ed. P. Sawyer.  Lancaster, UK, Springer Verlag. 236-248. <a href="http://hcibook.com/alan/papers/QbB-IDS94/">http://hcibook.com/alan/papers/QbB-IDS94/</a></li>
  <li><a name="ref_3">A. Dix</a> (1998). <strong>Interactive Querying - locating and discovering information</strong> <em>Second Workshop on <a href="http://www.dcs.gla.ac.uk/irhci/">Information Retrieval and Human Computer Interaction</a></em>, Glasgow, 11th September 1998. <a href="http://www.hcibook.com/alan/papers/IQ98/">http://www.hcibook.com/alan/papers/IQ98/</a><br />
  </li>
  <li><a name="ref_4">A. Dix and D. Oram</a> (2008). <strong>Query-through-Drilldown: Data-Oriented Extensional Queries.</strong> <em>Proceedings                  of Advanced Visual Interfaces, AVI2008</em>. P.Bottoni &amp; S. Levialdi (eds). ACM, New York. pp. 251-259. <a href="http://www.hcibook.com/alan/papers/avi2008-query-through-drilldown/">http://www.hcibook.com/alan/papers/avi2008-query-through-drilldown/</a></li>
</ol>
<p></p>
<hr />
<?php
//$str="";

//echo htmlentities(urldecode($str));

?>
<hr />
<script type='text/javascript' src='http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/scripts/shCore.js?ver=2.0.320'></script>
<script type='text/javascript' src='http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/scripts/shBrushPhp.js?ver=2.0.320'></script>
<script type='text/javascript' src='http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/scripts/shBrushPlain.js?ver=2.0.320'></script>
<script type='text/javascript'>
	SyntaxHighlighter.config.clipboardSwf = 'http://www.alandix.com/blog/wp-content/plugins/syntaxhighlighter/syntaxhighlighter/scripts/clipboard.swf';
	SyntaxHighlighter.config.strings.expandSource = 'expand source';
	SyntaxHighlighter.config.strings.viewSource = 'view source';
	SyntaxHighlighter.config.strings.copyToClipboard = 'copy to clipboard';
	SyntaxHighlighter.config.strings.copyToClipboardConfirmation = 'The code is in your clipboard now';
	SyntaxHighlighter.config.strings.print = 'print';
	SyntaxHighlighter.config.strings.help = '?';
	SyntaxHighlighter.config.strings.alert = 'SyntaxHighlighter\n\n';
	SyntaxHighlighter.config.strings.noBrush = 'Can\'t find brush for: ';
	SyntaxHighlighter.config.strings.brushNotHtmlScript = 'Brush wasn\'t configured for html-script option: ';
	SyntaxHighlighter.all();
</script>
</body>
</html>
