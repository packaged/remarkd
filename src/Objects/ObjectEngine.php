<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\RemarkdContext;

class ObjectEngine
{
  /**
   * @var \Packaged\Remarkd\RemarkdContext
   */
  protected RemarkdContext $_context;

  const configParse = '/(^|[\s,]+)([^, =}]+)(=((\"([^\"]*)\")|([^\s,}]*)))?/';
  const objectSelectors = '/{(%TYPES%)(:([^ }]+))?([^}]*|.*?\"(.|\n)*\".*?)}/mi';

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
          $config = $configMatches = [];
          if(preg_match_all(self::configParse, $match[4], $configMatches, PREG_SET_ORDER))
          {
            foreach($configMatches as $cMatch)
            {
              if(isset($cMatch[7]))
              {
                $config[$cMatch[2]] = $cMatch[7];
              }
              else if(isset($cMatch[6]))
              {
                $config[$cMatch[2]] = $cMatch[6];
              }
              else if(isset($cMatch[2]))
              {
                $config[$cMatch[2]] = true;
              }
            }
          }
          $o = $this->_objects[$match[1]]->create($this->_context, $config, $match[3]);
          $text = str_replace($match[0], $o->render(), $text);
        }
      }
    }
    return $text;
  }
}
