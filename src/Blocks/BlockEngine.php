<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class BlockEngine
{
  protected $_engine;

  protected $_startCodes = [];
  /** @var array|\Packaged\Remarkd\Blocks\BlockLineMatcher[] */
  protected $_matchers = [];

  protected $_codeBlock;

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

    if(!$this->_codeBlock && $block instanceof CodeBlock)
    {
      $this->_codeBlock = get_class($block);
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
      if(isset($line[0]) && preg_match('/^\s*?[- *_]{3,}\s*?$/', $line))
      {
        $line = '<hr/>';
      }

      if($currentBlock === null)
      {
        $currentBlock = $this->_detectBlock($line, $subBlock);
      }

      if($currentBlock !== null)
      {
        //Attempt to add the line to the current block
        $lineAccept = $currentBlock->addNewLine($line);
        if($lineAccept)
        {
          continue;
        }
        else
        {
          $blocks[] = $currentBlock;
          $currentBlock = null;
          if($lineAccept === false)
          {
            $currentBlock = $this->_detectBlock($line, $subBlock);
            if($currentBlock && !$currentBlock->addNewLine($line))
            {
              $currentBlock = null;
            }
          }
        }
      }
      else
      {
        $blocks[] = $line;
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

    if($this->_codeBlock && ($line[0] === "\t" || ($line[0] == ' ' && substr($line, 0, 4) == '    ')))
    {
      return new $this->_codeBlock();
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
