<?php
namespace Packaged\Remarkd;

class Attributes
{
  protected $_raw = '';

  public function __construct($raw = '')
  {
    $this->_raw = $raw;
  }
}
