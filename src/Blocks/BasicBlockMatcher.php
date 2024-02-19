<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Text\Paragraph;
use Packaged\Helpers\Strings;

class BasicBlockMatcher implements BlockMatcher
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

  public function match($line, ?Block $parent): ?Block
  {
    $this->_pendingLine = null;
    if($this->_beginPartial)
    {
      if(Strings::startsWith($line, $this->_begin))
      {
        $this->_pendingLine = substr($line, strlen($this->_begin));
        return $this->createBlock($line);
      }
    }

    if(preg_match(
      '/^' . preg_quote($this->_begin)
      . '(' . preg_quote($this->_continue ?: $this->_begin) . '){0,10}' . '$/',
      $line
    ))
    {
      return $this->createBlock($line);
    }
    return null;
  }

  public function createBlock($line, $title = null, $attribute = null)
  {
    $block = new BasicBlock();
    if($this->_tag)
    {
      $block->setTag($this->_tag);
    }
    else if(is_scalar($line))
    {
      $block->setTag(Paragraph::class);
    }

    if(!empty($title))
    {
      $block->setTitle($title);
    }
    if($attribute)
    {
      $block->setAttributes($attribute);
    }
    $block->setAllowChildren($this->_allowChildren);
    $block->setCloser($line);
    $block->addClass($this->_class);

    return $block;
  }
}
