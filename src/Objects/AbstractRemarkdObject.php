<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;

abstract class AbstractRemarkdObject implements RemarkdObject
{
  /** @var RemarkdContext */
  protected $_context;

  /** @var Attributes */
  protected $_config;
  protected $_key;

  public function create(RemarkdContext $context, ?Attributes $attributes, $key = null)
  {
    $obj = new static();
    $obj->_context = $context;
    $obj->_config = $attributes;
    $obj->_key = $key;
    return $obj;
  }
}
