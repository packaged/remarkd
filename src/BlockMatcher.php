<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Text\Paragraph;
use Packaged\Helpers\Strings;

class BlockMatcher
{
  protected $_begin;
  protected $_beginPartial = false;
  protected $_continue;
  protected $_tag;
  protected $_allowChildren = true;
  protected $_class;
  protected $_pendingLine;

  public static function i($begin, $partial = false)
  {
    $matcher = new static();
    $matcher->_begin = $begin;
    $matcher->_beginPartial = $partial;
    return $matcher;
  }

  public function setContinue($continue)
  {
    $this->_continue = $continue;
    return $this;
  }

  public function setTag($tag)
  {
    $this->_tag = $tag;
    return $this;
  }

  public function setAllowChildren($allowChildren)
  {
    $this->_allowChildren = $allowChildren;
    return $this;
  }

  public function setClass($class)
  {
    $this->_class = $class;
    return $this;
  }

  public function pendingLine()
  {
    return $this->_pendingLine;
  }

  public function match($line)
  {
    $this->_pendingLine = null;
    if($this->_beginPartial)
    {
      if(Strings::startsWith($line, $this->_begin))
      {
        $this->_pendingLine = substr($line, strlen($this->_begin));
        return true;
      }
    }

    return preg_match(
      '/^' . preg_quote($this->_begin)
      . '(' . preg_quote($this->_continue ?: $this->_begin) . '){0,10}' . '$/',
      $line
    );
  }

  public function createBlock($line, $title = null, $attribute = null)
  {
    $block = new Block();
    if($this->_tag)
    {
      $block->setTag($this->_tag);
    }
    else if(is_scalar($line))
    {
      $block->setTag(Paragraph::class);
      $block->setCloseOnEmptyLine(true);
    }

    $block->setTitle($title);
    $block->setAttr($attribute);
    $block->setAllowChildren($this->_allowChildren);
    $block->setOpener($line);
    $block->addClass($this->_class);

    return $block;
  }
}
