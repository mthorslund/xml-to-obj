<?php
/**
 *  Usage example for the ElementWrapper class.
 *
 *  Run this script from your console:
 *  $ php test.php
 */

include_once 'elementwrapper.class.php';
include_once 'sampleclasses.php';


/**
 *  Loading the XML file
 *
 *  Note that we map the two elements ExternalLink and InternalLink
 *  to a common Link class.
 */
$element = ElementWrapper::createFromFile(
    'sample.xml',
    array(
        'ExternalLink' => 'Link',
        'InternalLink' => 'Link'
    )
);


/**
 *  Here's the magic: The sample classes instantiated from the root.
 */
$rootObject = $element->createObject();
print_r($rootObject);


/**
 *  With XPATH, we can grab a subset of the XML...
 */
$subElements = $element->xpath('/Menu/Category/Category');
print_r($subElements);


/**
 *  ...and instantiate the matching elements.
 */
$myObjects = array();
foreach($subElements as $subElement){
    $myObjects[] = $subElement->createObject();
}
print_r($myObjects);


/**
 *  Destroying the ElementWrapper. Any instantiated sub-element
 *  ElementWrappers will need to be destroyed separately.
 */
$element = null;
