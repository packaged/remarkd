<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\LineBreak;
use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\SafeHtml\SafeHtml;

class BlockEngine
{
  /** @var array|\Packaged\Remarkd\Blocks\BlockMatcher[] */
  protected $_matchers = [];

  /** @var \Packaged\Remarkd\Blocks\Block */
  protected $_activeRoot;
  protected $_rootBlocks = [];

  /**
   * @var \Packaged\Remarkd\RemarkdContext
   */
  protected RemarkdContext $_context;

  public function clearBlocks()
  {
    $this->_activeRoot = null;
    $this->_rootBlocks = [];
  }

  public function __clone()
  {
    $this->clearBlocks();
  }

  public function __construct(RemarkdContext $ctx)
  {
    $this->_context = $ctx;
  }

  public function blocks() { return $this->_rootBlocks; }

  /**
   * @return array|\Packaged\Remarkd\Blocks\BlockMatcher[]
   */
  public function getMatchers(): array
  {
    return $this->_matchers;
  }

  /**
   * @param array|\Packaged\Remarkd\Blocks\BlockMatcher[] $matchers
   */
  public function setMatchers(array $matchers)
  {
    $this->_matchers = $matchers;
    return $this;
  }

  public function addMatcher(BlockMatcher $matcher)
  {
    $this->_matchers[] = $matcher;
    return $this;
  }

  public function addLine($line, string $title = null, ?Attributes $attribute = null)
  {
    $append = false;
    $block = $this->_activeRoot;

    if($this->_activeRoot === null || !$this->_activeRoot->isOpen())
    {
      $block = $this->getBlock($line, $attribute);
      $append = true;
    }

    $added = $this->_addLine($line, $block, $append, $title, $attribute);
    if($added === false)
    {
      $this->_addLine($line, $this->getBlock($line, $attribute), true, $title, $attribute);
    }
    else if($added === null)
    {
      $this->_activeRoot = null;
    }
  }

  protected function _addLine(
    $line, ?Block $block, $appendBlock = false, string $title = null, ?Attributes $attribute = null
  )
  {
    if($block === null)
    {
      $this->_rootBlocks[] = new SafeHtml($line);
      return true;
    }
    if($appendBlock)
    {
      $this->_activeRoot = $block;
      $this->_rootBlocks[] = $block;
    }
    $result = $this->_addBlockLine($line, $block, $attribute);
    if($result !== false)
    {
      if(!empty($title))
      {
        $block->setTitle($title);
      }
      if($attribute !== null)
      {
        $block->setAttributes($attribute);
      }
    }
    return $result;
  }

  /**
   * @param string $line
   *
   * true = line appended
   * false = line rejected
   * null = block complete
   *
   * @return null|bool
   */
  protected function _addBlockLine($line, Block $block, ?Attributes $attributes): ?bool
  {
    if(empty($line) && $block->closesOnEmptyLine())
    {
      $block->close();
      return null;
    }

    $allowLine = $block->allowLine($line);

    if($allowLine === false)
    {
      $block->close();
      return false;
    }

    if(!empty($line) && $line === $block->closer())
    {
      if(!empty($block->children()))
      {
        $block->close();
        return null;
      }
      return true;
    }

    if($allowLine !== null)
    {
      foreach($block->children() as $child)
      {
        if($child instanceof Block && $child->isOpen())
        {
          $res = $this->_addBlockLine($line, $child, $attributes);
          if($res !== false)
          {
            return true;
          }
        }
      }
    }

    if($line == '+')
    {
      switch($block->contentType())
      {
        case Block::TYPE_COMPOUND:
        case Block::TYPE_SIMPLE:
          $line = new LineBreak();
          break;
      }
    }

    if(/*$block->isContainer() && */ $block->trimLeftLength() > 0 && is_scalar($line)
      && substr($line, 0, $block->trimLeftLength()) === $block->trimLeftStr())
    {
      $line = substr($line, $block->trimLeftLength());
    }

    if($block->allowChildren())
    {
      switch($block->contentType())
      {
        case Block::TYPE_COMPOUND:
          $child = $this->getBlock($line, $attributes, $block);
          if($child !== null && $block->allowChild($child))
          {
            $block->addChild($child);
            $childAdd = $this->_addBlockLine($line, $child, $attributes);
            //avoid a null return for the child block
            return $childAdd !== false;
          }
        default:
          $this->_append($block, $line);
          return true;
      }
    }

    return $this->_append($block, $line);
  }

  protected function _append(Block $block, $line)
  {
    if(is_object($line))
    {
      $block->addChild($line);
      return true;
    }

    $append = null;
    switch($block->contentType())
    {
      case Block::TYPE_SIMPLE:
      case Block::TYPE_COMPOUND:
        if(substr($line, -2) == ' +')
        {
          $line = substr($line, 0, -2);
          $append = new LineBreak();
        }
        break;
    }

    $ret = $block->appendLine($this->_context, $line);

    if($append !== null)
    {
      $block->addChild($append);
    }

    if($ret)
    {
      //still open to append
      return true;
    }

    //return null to complete the block
    $block->close();
    return null;
  }

  public function getBlock(string $line, ?Attributes $attr = null, ?Block $parent = null): ?Block
  {
    if($line === '')
    {
      return null;
    }

    $block = null;
    foreach($this->_matchers as $matcher)
    {
      $block = $matcher->match($line, $parent);
      if($block !== null)
      {
        if($attr !== null)
        {
          $block->setAttributes($attr);
        }
        return $block;
      }
    }

    if(!empty(trim($line)))
    {
      if($attr && $attr->position(0) == 'source')
      {
        $block = new CodeBlock();
        $block->setCloseOnEmptyLine(true);
      }
      else if(substr($line, 0, 2) !== '{{' && substr($line, -2) !== '}}')
      {
        $block = new ParagraphBlock();
      }
      else
      {
        $block = new BasicBlock();
      }
    }

    if($attr !== null && $block !== null)
    {
      $block->setAttributes($attr);
    }
    return $block;
  }
}
