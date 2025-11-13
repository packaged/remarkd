<?php
namespace Packaged\RemarkdExample\Layout;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Ui\Html\TemplatedHtmlElement;

class TestSuitePage extends TemplatedHtmlElement implements ContextAware
{
  use ContextAwareTrait;

  protected $_content;
  protected $_sidebar;
  protected $_title;
  protected $_jsScript;

  public function setTitle($title)
  {
    $this->_title = $title;
    return $this;
  }

  public function getTitle()
  {
    return $this->_title ?: 'Remarkd Test Suite';
  }

  public function setSidebar($sidebar)
  {
    $this->_sidebar = $sidebar;
    return $this;
  }

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

  public function setJsScript($script)
  {
    $this->_jsScript = $script;
    return $this;
  }

  protected function _getTemplateFile()
  {
    return __DIR__ . '/TestSuitePage.phtml';
  }
}


