<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\ListItem;

class ListItemBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_allowChildren = true;
  protected $_tag = ListItem::class;
  protected $_contentContainer = false;

  const OL_MATCH = '/^((\d+\.)+) (.*)/';
  const UL_MATCH = '/^((\*|\-){1,10}) (.*)/';

  public function match($line, ?Block $parent): ?Block
  {
    if(!($parent instanceof ListBlock))
    {
      return null;
    }

    $matches = $this->_allowLine($line);
    if($matches)
    {
      $block = clone $this;
      $block->_setSubstrim($matches[1] . ' ');
      return $block;
    }

    return null;
  }

  protected function _allowLine($line)
  {
    if(preg_match(self::OL_MATCH, $line, $matches) || preg_match(self::UL_MATCH, $line, $matches))
    {
      return $matches;
    }
    return false;
  }

  public function isContainer(): bool
  {
    return true;
  }

  public function allowLine(string $line): ?bool
  {
    if(substr($line, 0, $this->trimLeftLength()) == $this->trimLeftStr())
    {
      return empty($this->children());
    }

    if($this->_allowLine($line))
    {
      foreach($this->children() as $child)
      {
        if($child instanceof Block && !($child instanceof ListBlock))
        {
          $child->close();
        }
      }
    }

    return $line !== '';
  }
}
