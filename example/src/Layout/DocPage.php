<?php
namespace Packaged\RemarkdExample\Layout;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Ui\Html\TemplatedHtmlElement;

class DocPage extends TemplatedHtmlElement implements ContextAware
{
  use ContextAwareTrait;

  protected $_content;

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

}
