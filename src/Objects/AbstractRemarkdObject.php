<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\RemarkdContext;

abstract class AbstractRemarkdObject implements RemarkdObject
{
  /** @var RemarkdContext */
  protected $_context;
  protected $_config = [];
  protected $_key;

  public function create(RemarkdContext $context, array $configuration, $key = null)
  {
    $obj = new static();
    $obj->_context = $context;
    $obj->_config = $configuration;
    $obj->_key = $key;
    return $obj;
  }
}
