<?php
namespace Packaged\RemarkdExample\Layout;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Ui\Element;

class Wrap extends Element implements ContextAware
{
  use ContextAwareTrait;

  protected $_content = [];

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

  public function getContent()
  {
    return $this->_content;
  }
}
