<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\Helpers\Arrays;
use Packaged\Remarkd\RemarkdContext;
use Packaged\Ui\Html\HtmlElement;

class IDBlock extends BasicBlock implements BlockMatcher
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_closeOnEmpty = false;
  protected $_id = null;

  public function id()
  {
    return $this->_id;
  }

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/!!([\w-]+)!!/', $line, $matches))
    {
      $block = new static();
      $block->_id = $matches[1];
      return $block;
    }
    return null;
  }

  public function allowLine(string $line): ?bool
  {
    if(!empty($this->_children) && substr($line, 0, 2) === '!!' && $line !== '!!!!')
    {
      return false;
    }
    return true;
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if($line == '!!!!')
    {
      return false;
    }
    if(substr($line, 0, 2) === '!!' && empty($this->_children))
    {
      return true;
    }
    return parent::appendLine($ctx, $line);
  }

  protected function _produceElement(): HtmlElement
  {
    $ele = Div::create(Arrays::interleave(PHP_EOL, $this->_children));
    $ele->setId($this->_id);
    return $ele;
  }
}
