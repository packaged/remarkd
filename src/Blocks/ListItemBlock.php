<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\ListItem;

class ListItemBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_allowChildren = true;
  protected $_tag = ListItem::class;
  protected $_contentContainer = false;

  const OL_MATCH = '/^((\d*\.)+) (.*)/';
  const UL_MATCH = '/^((\*|\-){1,10}) (.*)/';

  protected $_marker = '';

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
      $block->_marker = $matches[1];
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
    $matches = $this->_allowLine($line);
    if($matches)
    {
      if($this->_isSameLevel($matches[1]))
      {
        return empty($this->children());
      }

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

  protected function _isSameLevel(string $marker): bool
  {
    return self::_isOrdered($marker) === self::_isOrdered($this->_marker)
      && self::_markerDepth($marker) <= self::_markerDepth($this->_marker);
  }

  private static function _isOrdered(string $marker): bool
  {
    return $marker !== '' && $marker[0] !== '*' && $marker[0] !== '-';
  }

  private static function _markerDepth(string $marker): int
  {
    return self::_isOrdered($marker) ? substr_count($marker, '.') : strlen($marker);
  }
}
