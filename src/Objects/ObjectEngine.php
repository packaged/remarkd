<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;

class ObjectEngine
{
  /**
   * @var \Packaged\Remarkd\RemarkdContext
   */
  protected RemarkdContext $_context;

  const objectSelectors = '/{{(%TYPES%)(:([^ }]+))?([^}]*|.*?\"(.|\n)*\".*?)}}/mi';

  protected $_selector = '';

  public function __construct(RemarkdContext $ctx)
  {
    $this->_selector = self::objectSelectors;
    $this->_context = $ctx;
  }

  /**
   * @var \Packaged\Remarkd\Objects\RemarkdObject[]
   */
  protected $_objects = [];

  public function registerObject(RemarkdObject $obj)
  {
    $this->_objects[$obj->getIdentifier()] = $obj;
    krsort($this->_objects);
    $this->_selector = str_replace('%TYPES%', implode('|', array_keys($this->_objects)), self::objectSelectors);
    return $this;
  }

  public function parse(string $text): string
  {
    $matches = [];
    if(preg_match_all($this->_selector, $text, $matches, PREG_SET_ORDER) > 0)
    {
      foreach($matches as $match)
      {
        if(isset($this->_objects[$match[1]]))
        {
          $o = $this->_objects[$match[1]]->create($this->_context, new Attributes(trim($match[4])), $match[3]);
          $text = str_replace($match[0], $o->render(), $text);
        }
      }
    }
    return $text;
  }
}
