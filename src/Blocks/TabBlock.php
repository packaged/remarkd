<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\Ui\Html\HtmlElement;

class TabBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_tabID = null;

  public function tabID()
  {
    return $this->_tabID;
  }

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^_\|_#([\w-]+)\s*(.*)\s*$/', $line, $matches))
    {
      $block = new static();
      $block->_tabID = $matches[1];
      $block->setAttributes(new Attributes($matches[2]));

      if(!(($parent instanceof TabContainer) || ($parent instanceof TabBlock)))
      {
        $parent = new TabContainer();
        $parent->addChild($block);
        return $parent;
      }
      return $block;
    }
    return null;
  }

  public function allowLine(string $line): ?bool
  {
    if(substr($line, 0, 3) === '_|_')
    {
      return empty($this->_children);
    }
    return parent::allowLine($line);
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if(substr($line, 0, 3) == '_|_')
    {
      return true;
    }

    return parent::appendLine($ctx, $line);
  }

  protected function _produceElement(): HtmlElement
  {
    return Div::create(parent::_produceElement())->addClass('tab')->setAttribute('data-tab-key', $this->_tabID);
  }

}
