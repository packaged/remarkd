<?php
namespace Packaged\Remarkd\Blocks;

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

  public function __construct(RemarkdContext $ctx)
  {
    $this->_context = $ctx;
  }

  public function blocks() { return $this->_rootBlocks; }

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
      $block = $this->getBlock($line);
      $append = true;
    }

    $added = $this->_addLine($line, $block, $append, $title, $attribute);
    if($added === false)
    {
      $this->_addLine($line, $this->getBlock($line), true, $title, $attribute);
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
    $result = $this->_addBlockLine($line, $block);
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
  protected function _addBlockLine($line, Block $block): ?bool
  {
    if(!$block->allowLine($line))
    {
      $block->close();
      return false;
    }

    foreach($block->children() as $child)
    {
      if($child instanceof Block && $block->isOpen())
      {
        return $this->_addBlockLine($line, $child);
      }
    }

    if($block->allowChildren())
    {
      if($block->isContainer() && $block->trimLeftLength() > 0 && is_scalar($line))
      {
        $line = substr($line, $block->trimLeftLength());
      }

      if($line !== $block->closer())
      {
        $child = $this->getBlock($line);
        if($child !== null)
        {
          $block->addChild($child);
          return $this->_addBlockLine($line, $child);
        }
      }
    }

    if(empty($line) && $block->closesOnEmptyLine())
    {
      $block->close();
      return null;
    }

    if($block->addLine($this->_context, $line))
    {
      //still open to append
      return true;
    }

    //return null to complete the block
    $block->close();
    return null;
  }

  public function getBlock(string $line): ?Block
  {
    foreach($this->_matchers as $matcher)
    {
      $block = $matcher->match($line);
      if($block !== null)
      {
        return $block;
      }
    }

    if(!empty($line))
    {
      return new ParagraphBlock();
    }
    return null;
  }
}
