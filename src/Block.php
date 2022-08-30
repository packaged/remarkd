<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Core\AbstractContainerTag;
use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Lists\ListItem;
use Packaged\Glimpse\Tags\Lists\UnorderedList;
use Packaged\Helpers\Arrays;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

class Block implements ISafeHtmlProducer
{
  protected $_closed = false;
  protected $_opener;
  protected $_attr;
  protected $_class = [];
  protected $_tag;
  protected $_title;
  protected $_allowChildren = true;
  protected $_closeOnEmptyLine = false;

  protected $_children = [];
  /**
   * @var ?Block
   */
  protected $_activeChild;

  public function setTitle($title)
  {
    $this->_title = $title;
    return $this;
  }

  public function setAttr($attribute)
  {
    $this->_attr = $attribute;
    return $this;
  }

  public function addClass($class)
  {
    $this->_class[] = $class;
    return $this;
  }

  public function setOpener($tag)
  {
    $this->_opener = $tag;
    return $this;
  }

  public function setTag($tag)
  {
    $this->_tag = $tag;
    return $this;
  }

  public function tag()
  {
    return $this->_tag;
  }

  public function setAllowChildren(bool $bool)
  {
    $this->_allowChildren = $bool;
    return $this;
  }

  public function allowChildren(): bool
  {
    return $this->_allowChildren;
  }

  public function setCloseOnEmptyLine(bool $bool)
  {
    $this->_closeOnEmptyLine = $bool;
    return $this;
  }

  public function closesOnEmptyLine(): bool
  {
    return $this->_closeOnEmptyLine;
  }

  public function isClosed(): bool
  {
    return $this->_closed;
  }

  public function close(): array
  {
    $blockIDs = [];
    foreach($this->_children as $child)
    {
      if($child instanceof Block)
      {
        $blockIDs = array_merge($blockIDs, $child->close());
      }
    }
    $blockIDs[] = $this->_opener;
    $this->_closed = true;
    return $blockIDs;
  }

  public function addChild(Block $block)
  {
    if($this->_activeChild && !$this->_activeChild->isClosed() && $this->_activeChild->allowChildren())
    {
      $this->_activeChild->addChild($block);
    }
    else
    {
      $this->_children[] = $block;
      $this->_activeChild = $block;
    }
    return $this;
  }

  public function addLine($line)
  {
    if(empty($line))
    {
      if($this->closesOnEmptyLine())
      {
        $this->close();
      }
      return $this;
    }

    if($this->_activeChild && !$this->_activeChild->isClosed())
    {
      $this->_activeChild->addLine($line);
    }
    else
    {
      $this->_children[] = $line;
    }
    return $this;
  }

  public function produceSafeHTML(): SafeHtml
  {
    $content = Arrays::interleave(PHP_EOL, $this->_children);
    if($this->allowChildren())
    {
      $content = Div::create($content)->addClass('content');
    }
    if($this->_title)
    {
      $content = [Div::create($this->_title)->addClass('title'), $content];
    }

    if($this->_tag)
    {
      $ele = $this->_tag::create($content);
    }
    else
    {
      $ele = AbstractContainerTag::create($content);
    }

    if($ele instanceof HtmlElement)
    {
      $ele->addClass(...$this->_class);
    }

    return $ele->produceSafeHTML();
  }

}
