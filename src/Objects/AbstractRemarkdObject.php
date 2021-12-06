<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\RemarkdContext;

abstract class AbstractRemarkdObject implements RemarkdObject
{
  protected $_config = [];
  protected $_key;

  public function create(RemarkdContext $context, array $configuration, $key = null)
  {
    $obj = new static();
    $obj->_config = $configuration;
    $obj->_key = $key;
    return $obj;
  }
}
