<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\ListItem;

class ListItemBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_SIMPLE;
  protected $_tag = ListItem::class;

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^((\d+\.)+) (.*)/', $line, $matches))
    {
      $block = clone $this;
      $block->_setSubstrim($matches[1]);
      if($parent instanceof OrderedListBlock)
      {
        return $block;
      }

      $parent = new OrderedListBlock();
      $parent->addChild($block);
      return $parent;
    }
    if(preg_match('/^((\*|\-){1,10}) (.*)/', $line, $matches))
    {
      $block = clone $this;
      $block->_setSubstrim($matches[1]);
      if($parent instanceof UnorderedListBlock)
      {
        return $block;
      }

      $parent = new UnorderedListBlock();
      $parent->addChild($block);
      return $parent;
    }
    return null;
  }

  public function isContainer(): bool
  {
    return true;
  }

  public function allowLine(string $line): ?bool
  {
    if(substr($line, 0, $this->trimLeftLength() + 1) == $this->trimLeftStr() . " ")
    {
      return empty($this->children());
    }

    if($line !== '')
    {
      return true;
    }

    return parent::allowLine($line);
  }
}
