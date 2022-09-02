<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\ListItem;

class ListItemBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_SIMPLE;
  protected $_tag = ListItem::class;

  public function match($line): ?Block
  {
    if(preg_match('/^(((\d*\.)|\*|\-){1,10}) (.*)/', $line, $matches))
    {
      $block = clone $this;
      $block->_setSubstrim($matches[1]);
      return $block;
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
    return parent::allowLine($line);
  }
}
