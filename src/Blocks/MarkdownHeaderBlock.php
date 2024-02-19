<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Text\HeadingFive;
use Packaged\Glimpse\Tags\Text\HeadingFour;
use Packaged\Glimpse\Tags\Text\HeadingOne;
use Packaged\Glimpse\Tags\Text\HeadingSix;
use Packaged\Glimpse\Tags\Text\HeadingThree;
use Packaged\Glimpse\Tags\Text\HeadingTwo;
use Packaged\Remarkd\RemarkdContext;

class MarkdownHeaderBlock extends BasicBlock implements BlockMatcher
{
  protected $_allowChildren = false;
  protected $_contentType = Block::TYPE_SIMPLE;

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^(#{1,6}) (.*)$/', $line, $matches))
    {
      $block = new static();
      $block->_setSubstrim($matches[1]);
      switch(strlen($matches[1]))
      {
        case 1:
          $block->_tag = HeadingOne::class;
          break;
        case 2:
          $block->_tag = HeadingTwo::class;
          break;
        case 3:
          $block->_tag = HeadingThree::class;
          break;
        case 4:
          $block->_tag = HeadingFour::class;
          break;
        case 5:
          $block->_tag = HeadingFive::class;
          break;
        case 6:
          $block->_tag = HeadingSix::class;
          break;
        default:
          return null;
      }
      return $block;
    }
    return null;
  }

  protected function _formatLine(RemarkdContext $ctx, string $line)
  {
    $line = ltrim($line, '# ');
    return parent::_formatLine($ctx, $line);
  }

}
