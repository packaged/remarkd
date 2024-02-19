<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\ListItem;
use Packaged\Ui\Html\HtmlElement;

class CalloutBlock extends BasicBlock implements BlockMatcher
{
  protected $_tag = ListItem::class;
  protected $_class = ['callout'];
  protected $_contentType = Block::TYPE_SIMPLE;

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^(\<(\d+|\d+\.\d+|\w|[^\>])\> )(.*)/', $line, $match))
    {
      $block = clone $this;
      $block->_setSubstrim($match[1]);
      return $block;
    }
    return null;
  }

  public function allowLine(string $line): bool
  {
    return empty($this->_children) || $line[0] != '<';
  }

  protected function _produceElement(): HtmlElement
  {
    $ele = parent::_produceElement();
    $ele->addAttributes(['data-marker' => substr($this->_substrim, 1, -2)]);
    return $ele;
  }

}
