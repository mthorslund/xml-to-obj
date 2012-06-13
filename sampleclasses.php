<?php
/**
 *  User (sample) classes
 *
 *  Classes that represent elements in the XML file.
 *  These classes are examples of PHP classes that could be 
 *  instantiated by the ElementWrapper class.
 *
 *  The only requirement is that your class implements the
 *  "create" method as in these examples. See further comments
 *  in-line.
 *
 *  PHP 5.3
 *
 *  @package    SampleCode
 */


/**
 *  Menu element class
 *
 *  This class maps to the root XML element, also named Menu.
 *
 *  @package    SampleCode
 */
class Menu
{
var $menuItems = array();


/**
 *  "Factory" method.
 *
 *  All classes to be instantiated by the ElementWrapper's
 *  createObject method need to implement this static
 *  method.
 */
static function &create(&$element, &$container = null)
{
    $instance = new Menu($element, $container);
    return $instance;
}

/**
 *  Constructor
 *
 *  Here, the constructor takes the element as an argument. This
 *  is convenient but not necessary: The 'create' method could 
 *  call the constructor with any parameters.
 */
function __construct(&$element, &$container = null)
{
    foreach($element->children() as $child){
        $this->menuItems[] = $child->createObject(null, $this);
    }
}
} //end class Menu


/**
 *  Category sample class
 *
 *  It is NOT necessary to derive the mapped classes from each other.
 *  They do need to implement the "create" method, and that's all.
 *
 *  @package    SampleCode
 */
class Category extends Menu
{
var $parentObject;
var $phrase;

static function &create(&$element, &$container = null)
{
    $instance = new Category($element, $container);
    return $instance;
}

function __construct(&$element, &$container = null)
{
    if($container){
        $this->parentObject = $container;
    }
    $this->phrase = $element->getAttribute('phrase');
    foreach($element->children() as $child){
        $this->menuItems[] = $child->createObject(null, $this);
    }
}
} //end class Category



/**
 *  Link sample class
 *
 *  This class is used for both the InternalLink and ExternalLink XML elements.
 *
 *  @package    SampleCode
 */
class Link extends Category
{
var $target = '';

static function &create(&$element, &$container = null)
{
    $instance = new Link($element, $container);
    return $instance;
}

function __construct(&$element, &$container = null)
{
    $this->target = $element->getAttribute('target');
    parent::__construct($element, $container);
}
}  //end class Link