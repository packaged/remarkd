<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class BlockEngine
{
  protected $_engine;

  protected $_startCodes = [];
  /** @var array|\Packaged\Remarkd\Blocks\BlockLineMatcher[] */
  protected $_matchers = [];

  public function __construct(RuleEngine $engine)
  {
    $this->_engine = $engine;
  }

  public static function trimLine($line)
  {
    return ltrim($line, "\t\r\n\0\x0B ");
  }

  public function registerBlock(BlockInterface $block)
  {
    if($block instanceof BlockStartCodes)
    {
      foreach($block->startCodes() as $code)
      {
        $this->_startCodes[$code] = get_class($block);
      }
    }

    if($block instanceof BlockLineMatcher)
    {
      $this->_matchers[] = $block;
    }
    return $this;
  }

  public function parseLines(array $lines, $subBlock = false)
  {
    $newLines = [];
    foreach($this->parseBlocks($lines, $subBlock) as $block)
    {
      $newLines[] = $block instanceof BlockInterface ? $block->complete($this, $this->_engine) : $block;
    }
    return $newLines;
  }

  public function parseBlocks(array $lines, $subBlock = false)
  {
    $blocks = [];
    $currentBlock = null;
    foreach($lines as $line)
    {
      if(isset($line[0]) && ($line === '***' || $line === '___' ||
          ($line[0] === '-' && preg_match('/^-{3,}$/', $line))))
      {
        $line = '<hr/>';
      }

      if($currentBlock !== null)
      {
        //Attempt to add the line to the current block
        if($currentBlock->addNewLine($line))
        {
          //line added, continue to the next
          continue;
        }
        else
        {
          $blocks[] = $currentBlock;
          $currentBlock = false;
        }
      }

      if($currentBlock === null)
      {
        $currentBlock = $this->_detectBlock($line, $subBlock);
        if($currentBlock !== null)
        {
          $currentBlock->addNewLine($line);
        }
        else
        {
          $blocks[] = $line;
        }
      }
      else if($currentBlock === false)
      {
        $currentBlock = null;
      }
    }
    if($currentBlock !== null)
    {
      $blocks[] = $currentBlock;
    }
    return $blocks;
  }

  protected function _detectBlock($line, $subBlock = false): ?BlockInterface
  {
    if(empty($line))
    {
      return null;
    }

    if($line[0] === "\t" || ($line[0] == ' ' && substr($line, 0, 4) == '    '))
    {
      return new CodeBlock();
    }

    $line = ltrim($line, "\t\r\n\0\x0B ");

    if(empty($line))
    {
      return null;
    }

    $blockClass = $this->_startCodes[$line[0] . ($line[1] ?? ' ')] ?? null;
    if($blockClass !== null)
    {
      return new $blockClass();
    }

    foreach($this->_matchers as $matcher)
    {
      $locatedBlock = $matcher->match($line, $subBlock);
      if($locatedBlock)
      {
        return $locatedBlock;
      }
    }

    return null;
  }
}
