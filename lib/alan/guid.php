<?php

/*
from string to binary:
$uuid = str_replace("-", "", $uuid);
var_dump(pack('H*', $uuid));
then base64 it, and drop any trailing "="
use the base64 for uris http://en.wikipedia.org/wiki/Base64#URL_applications
where you replace + with - and / with _
*/
function generateGuid()
{
	$hex = strtoupper(md5(uniqid(getmypid(), true)));
 	return substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-'.substr($hex, 12, 4).'-'.substr($hex, 16, 4).'-'.substr($hex, 20, 12);
}
function generatePackedGuid()
{
	$uuid = generateGuid();

	// remove "-" from the UUID
	$uuid = str_replace("-", "", $uuid);
	
	//get binary value
	$packed = pack('H*', $uuid);
	
	// base64 encode it
	$base64Encoded = base64_encode($packed);
	
	//drop any trailing =
	$base64Encoded = str_replace("=", "", $base64Encoded);
	
	// replace + with -, and / with _
	$base64Encoded = str_replace("+", "-", $base64Encoded);
	$base64Encoded = str_replace("/", "_", $base64Encoded);
	
	//echo "\n{$uuid}  ::   :: {$base64Encoded} \n";
	return $base64Encoded;
}
?>