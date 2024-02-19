<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\Glimpse\Tags\Table\Table;
use Packaged\Glimpse\Tags\Table\TableCell;
use Packaged\Glimpse\Tags\Table\TableHeading;
use Packaged\Glimpse\Tags\Table\TableRow;
use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\Ui\Html\HtmlElement;

class DefinitionListBlock extends BasicBlock implements BlockMatcher
{
  protected $_titles = [];
  protected $_definitions = [];
  protected $_currentID = -1;

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^[^:]+:: .*/', $line))
    {
      return new static();
    }
    return null;
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if(preg_match('/^([^:]+):: (.*)/', $line, $matches))
    {
      $this->_currentID++;
      $this->_titles[$this->_currentID] = $matches[1];
      $this->_definitions[$this->_currentID] = $matches[2];
      return true;
    }
    else if(!empty($line))
    {
      $this->_definitions[$this->_currentID] .= "\n" . $line;
      return true;
    }

    return false;
  }

  protected function _horizontalElement($subStop = ''): HtmlElement
  {
    $ele = new Table();
    foreach($this->_titles as $id => $title)
    {
      $ele->appendContent(
        TableRow::create(TableHeading::create($title . $subStop), TableCell::create($this->_definitions[$id]))
      );
    }
    $ele->addClass('horizontal-definitions');
    return $ele;
  }

  protected function _produceElement(): HtmlElement
  {
    if($this->_attr === null)
    {
      $this->_attr = new Attributes();
    }
    $subStop = $this->_attr->get('subject-stop');

    if($this->_attr->has('horizontal'))
    {
      return $this->_horizontalElement($subStop);
    }

    $ele = new CustomHtmlTag('dl');
    foreach($this->_titles as $id => $title)
    {
      $ele->appendContent(new CustomHtmlTag('dt', $title . $subStop));
      $ele->appendContent(new CustomHtmlTag('dd', $this->_definitions[$id]));
    }
    return $ele;
  }

}
