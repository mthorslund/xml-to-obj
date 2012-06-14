<?php
/**
 *  XML-to-object example: ElementWrapper class.
 *
 *  The purpose is to easily generate PHP object instances from XML files
 *  for processing. I use this technique in a build process. I don't know
 *  about performance, so I wouldn't recommend that you use this for something
 *  perfomance critical without testing it well first.
 *
 *  The PHP classes to be instantiated could be any classes, only they need to
 *  implement the "create" method (see sampleclasses.php).
 *
 *  For usage, see the sample-usage.php file.
 *
 *  PHP 5.3
 *
 *
 *  LICENSE: Simplified BSD
 *
 *  Copyright (c) 2012, Mattias Thorslund
 *  All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met: 
 *
 *  1. Redistributions of source code must retain the above copyright notice, this
 *     list of conditions and the following disclaimer. 
 *  2. Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution. 
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 *  ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  @author         Mattias Thorslund <mattias@thorslund.us>
 *  @copyright      Copyright (c) 2012, Mattias Thorslund.
 *                  All Rights Reserved.
 *  @license        http://opensource.org/licenses/BSD-2-Clause
 *  @version        0.9
 *  @package        ElementWrapper
 */


/**
 *  Wraps the SimpleXMLElement class.
 *
 *  Although it would be more elegant, simply extending SimpleXMLElement 
 *  would leave out some abilities:
 *   Because SimpleXMLElement::_construct() is final, we can't override it
 *       so that additional properties could be set.
 *   Methods that return elements would always return SimpleXMLElement
 *       instances, not instances of the extended class.
 *
 *  Instead, we wrap a SimpleXMLElement instance within the ElementWrapper 
 *  object and implement the necessary methods. 
 *
 *  Since what we're after here is the ability to easily instantiate PHP classes
 *  from XML, I haven't (yet) implemented all the wonderful features of
 *  SimpleXMLElement.
 *
 *  @package    ElementWrapper
 */
class ElementWrapper
{


/**
 *  Internal instance of SimpleXMLElement that is being wrapped.
 *
 *  @var        SimpleXMLElement
 */
protected $_sxe; 


/**
 *  What to do if a matching class for an element is not found.
 *
 *  Expected values:
 *      'error':     trigger an error,
 *      'exception': throw an exception,
 *      'null' :     ignore and return a null value,
 *      'generic':   return a generic (StdClass) object.
 *  Defaults to 'error'.
 *
 *  @var    string
 */
protected static $missingClassBehavior = 'error';


/**
 *  A key-value array that maps XML element names to class names
 *
 *  Values are optional: If an entry is not found, the matching class name is
 *  assumed to be the same as the element name.
 *
 *  @var    array
 */
protected static $classMap = array();


/**
 *  Constructor
 *
 *  @param SimpleXMLElement &$sxe
 *      A SimpleXMLElement instance.
 *  @param array $classMap
 *      An array that maps XML Element names to PHP classes where the element 
 *      names are defined as the array index values. Defaults to an empty 
 *      array (class names expected to match XML element names).
 *  @param string $missingClassBehavior
 *      What to do if a matching class for an element is not found. 
 *      Expected values: 'error' (trigger an error), 'exception' (throws an 
 *      ElementWrapperException), 'null' (just return null), 'generic' (returns
 *      a generic StdClass object). Defaults to 'error'.
 */
public function __construct(&$sxe, $classMap = false, $missingClassBehavior = null)
{
    $this->_sxe = $sxe;
    if($missingClassBehavior){
        self::$missingClassBehavior = $missingClassBehavior;
    }
    if($classMap){
        self::$classMap = $classMap;
    }
}


/**
 *  Destroys the wrapped SimpleXMLElement instance.
 */
public function __destruct()
{
    $this->_sxe = null;
}


/**
 *  Factory method that creates an ElementWrapper from a file path
 *
 *  @param string $filePath 
 *      Path to XML file.
 *  @param array $classMap (see ElementWrapper::__construct())
 *  @param string $missingClassBehavior (see ElementWrapper::__construct())
 *  @param int $options LibXML options. 
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}. For expected values, see {@link 
 *      http://us.php.net/manual/en/libxml.constants.php LibXML Constants}.
 *  @param string $ns 
 *      Namespace. Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @param boolean $is_prefix 
 *      True if $ns is a prefix, false if it is a URI. 
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @see ElementWrapper::__construct()
 *  @return ElementWrapper
 */
public static function &createFromFile(
    $filePath,
    $classMap = false,
    $missingClassBehavior = null,
    $options = 0,
    $ns = '',
    $is_prefix = false)
{
    $element = new ElementWrapper(
        new SimpleXMLElement( $filePath, $options, true, $ns, $is_prefix ),
        $classMap,
        $missingClassBehavior
    );
    return $element;
}


/**
 *  Factory method that creates an ElementWrapper from an XML string
 *
 *  @param string $xml 
 *      A valid XML string.
 *  @param array $classMap (see ElementWrapper::__construct())
 *  @param string $missingClassBehavior (see ElementWrapper::__construct())
 *  @param int $options LibXML options. 
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}. For expected values, see {@link 
 *      http://us.php.net/manual/en/libxml.constants.php LibXML Constants}.
 *  @param string $ns 
 *      Namespace. Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @param boolean $is_prefix 
 *      True if $ns is a prefix, false if it is a URI.
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @see ElementWrapper::__construct()
 *  @return ElementWrapper
 */
public static function &createFromXML(
    $xml,
    $classMap = false,
    $missingClassBehavior = null,
    $options = 0,
    $ns = '',
    $is_prefix = false)
{
    $element = new ElementWrapper(
        new SimpleXMLElement( $xml, $options, false, $ns, $is_prefix ),
        $classMap,
        $missingClassBehavior
    );
    return $element;
}


/**
 *  Factory method that creates an ElementWrapper from a SimpleXMLElement instance
 *
 *  @param SimpleXMLElement &$sxe
 *      An existing SimpleXMLElement instance
 *  @param array $classMap (see ElementWrapper::__construct())
 *  @param string $missingClassBehavior (see ElementWrapper::__construct())
 *  @see ElementWrapper::__construct()
 *  @return ElementWrapper
 */
public static function createFromSXE(&$sxe, $classMap = false, $missingClassBehavior = null)
{
    $element = new ElementWrapper($sxe, $classMap, $missingClassBehavior);
    return $element;
}


/**
 *  Factory method that creates an ElementWrapper using the same parameters as SimpleXMLElement
 *
 *  The ElementWrapper parameters $classMap and $missingClassBehavior are added at the end of
 *  the parameter list.
 *
 *  @param string $data 
 *      Path to XML file, or an XML string. Depends on $data_is_url.
 *  @param int $options LibXML options.
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}. For expected values, see {@link 
 *      http://us.php.net/manual/en/libxml.constants.php LibXML Constants}.
 *  @param boolean $data_is_url
 *      Whether $data is a path to an XML file (if false, $data is expected to be a valid
 *      XML string.
 *  @param string $ns 
 *      Namespace. Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @param boolean $is_prefix 
 *      True if $ns is a prefix, false if it is a URI.
 *      Passed to {@link http://us.php.net/manual/en/simplexmlelement.construct.php
 *      SimpleXMLElement constructor}.
 *  @param array $classMap (see ElementWrapper::__construct())
 *  @param string $missingClassBehavior (see ElementWrapper::__construct())
 *  @see ElementWrapper::__construct()
 *  @return ElementWrapper
 */
public static function createLikeSXE($data, $options = 0, $data_is_url = false, $ns = '', $is_prefix = false, $classMap = false, $missingClassBehavior = null)
{
    $element = new ElementWrapper(new SimpleXMLElement($data, $options, $data_is_url, $ns, $is_prefix), $classMap, $missingClassBehavior);
    return $element;
}


/**
 *  Returns an instance of a PHP class, using the XML element.
 *
 *  Unless the XML element name is listed in the ElementWrapper::$classMap 
 *  array, the PHP class name is expected to be the same as the element name.
 *
 *  @param string $elementName
 *      Overrides the element's name for the purpose of instantiating a different
 *      class than the default.
 *  @param object &$callerRef
 *      A refrence to the calling object (or other object reference) to be 
 *      passed to the object being instantiated.
 *  @return object
 */
public function &createObject($elementName = null, &$callerRef = null)
{
    $object = null;

    if(!$elementName){
        $elementName = $this->_sxe->getName();
    }

    $className = $elementName;
    if(isset(self::$classMap[$elementName])){
        $className = self::$classMap[$elementName];
    }

    if(!class_exists($className)){
        //switch($this->missingClassBehavior){
        switch(self::$missingClassBehavior){
        case 'error':
            trigger_error( "Class '$className' is not defined.", E_USER_ERROR );
            break;
        case 'exception':
            throw new ElementWrapperException("Class '$className' is not defined.");
            break;
        case 'object':
            $object = new StdClass();
            break;
        case 'null':
        default:
            //$object is already null
            break;
        }
        return $object; //can't return null as a reference directly
    }

    if(is_null($callerRef)){
        $object = call_user_func_array(array($className, 'create'), array(&$this)); //sic: explicitly passing by reference because they are otherwise not recognized
    } else {
        $object = call_user_func_array(array($className, 'create'), array(&$this, &$callerRef));
    }

    if($object === false){
        trigger_error("ElementWrapper::createObject: 'create' method is not defined for the class $className.", E_USER_ERROR);
    }

    return $object;
}


/**
 *  Returns the SXE instance.
 *
 *  @return SimpleXMLElement
 */
public function &getSXE()
{
    return $this->_sxe;
}


/**
 *  Returns an XML string
 *
 *  This does not implemnt SimpleXMLElement's save-to-file functionality; use saveAsFile() for that.
 *
 *  @return string
 */
public function asXML()
{
    return $this->_sxe->asXML();
}


/**
 *  Saves the XML string as a file
 *
 *  Returns true or false depending on success.
 *
 *  @param  string $filePath Path to save the XML to.
 *  @return boolean
 */
public function saveAsFile($filePath)
{
    return $this->_sxe->asXML($filePath);
}


/**
 *  Converts an array of SimpleXMLElement instances to an array of ElementWrapper
 *
 *  @param  array &$sxes Array of SimpleXMLElement instances.
 *  @return array
 */
private function wrapSXEs(&$sxes)
{
    $wrappedElements = array();
    foreach($sxes as $sxe){
        $wrappedElements[] = ElementWrapper::createFromSXE($sxe);
    }
    return $wrappedElements;
}


/**
 *  Returns matches to an XPATH expression as ElementWrapper instances.
 *
 *  @param  string $path An XPATH expression.
 *  @return array An array of ElementWrapper instances.
 */
public function xpath($path)
{
    return $this->wrapSXEs($this->_sxe->xpath($path));
}


/**
 *  Quicker version of xpath method which does not wrap each result as ElementWrapper
 *
 *  @param  string $path An XPATH expression.
 *  @return array An array of SimpleXMLElement instances.
 */
public function xpathAsSXE($path)
{
    return $this->_sxe->xpath($path);
}


/**
 *  Returns the element name
 *
 *  @return string The element name
 */
public function getName()
{
    return $this->_sxe->getName();
}


/**
 *  Returns the element's child elements wrapped as ElementWrapper instances
 *
 *  @param  string $ns Namespace
 *  @param  boolean $is_prefix True if $ns is a prefix, false if it is a URI.
 *  @return array An array of ElementWrapper instances.
 */
public function children($ns = null,$is_prefix = false)
{
    return $this->wrapSXEs($this->_sxe->children($ns, $is_prefix));
}


/**
 *  Returns the element attributes
 *
 *  @return array An array indexed by attribute names.
 */
public function attributes()
{
    return $this->_sxe->attributes();
}


/**
 *  Returns the value of the named element attribute
 *
 *  @param  string $name Name of the attribute.
 *  @return string Value of the attribute.
 */
public function getAttribute($name)
{
    $attributes = $this->_sxe->attributes();
    if(isset($attributes[$name])){
        return (string)$attributes[$name];
    }
}
} //end class ElementWrapper



/**
 *  Custom exception class for the ElementWrapper functionality
 *
 *  @package    ElementWrapper
 */
class ElementWrapperException extends Exception
{

}