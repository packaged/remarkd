<?php
namespace Packaged\Remarkd;

class AttributeLine
{
  protected $_line;
  protected $_attribute;

  public function __construct($raw = '')
  {
    if(preg_match('/(.*)(\[(.*)?\])\s*$/mi', $raw, $matches))
    {
      $this->_line = $matches[1];
      $this->_attribute = new Attributes($matches[3]);
    }
    else
    {
      $this->_line = $raw;
      $this->_attribute = new Attributes();
    }
  }

  public function getLine(): string
  {
    return $this->_line;
  }

  public function getAttributes(): Attributes
  {
    return $this->_attribute;
  }
}
