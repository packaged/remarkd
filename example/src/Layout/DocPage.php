<?php
namespace Packaged\RemarkdExample\Layout;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Ui\Html\TemplatedHtmlElement;

class DocPage extends TemplatedHtmlElement implements ContextAware
{
  use ContextAwareTrait;

  protected $_content;
  protected $_toc;

  public function setToc($toc)
  {
    $this->_toc = $toc;
    return $this;
  }

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

}
