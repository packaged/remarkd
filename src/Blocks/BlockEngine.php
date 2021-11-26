<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class BlockEngine
{
  protected $_startCodes = [];
  /** @var array|\Packaged\Remarkd\Blocks\BlockLineMatcher[] */
  protected $_matchers = [];

  protected $_codeBlock;
  protected $_defaultBlock;
  /**
   * @var \Packaged\Remarkd\RemarkdContext
   */
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $ctx)
  {
    $this->_context = $ctx;
  }

  public static function trimLine($line)
  {
    return ltrim($line, "\r\n\0\x0B ");
  }

  public static function trimLeftSpace($line, $max = 2)
  {
    $line = ltrim($line, "\r\n\0\x0B");

    $parts = str_split($line, $max);
    if(empty(trim($parts[0])))
    {
      return implode("", array_slice($parts, 1));
    }
    else
    {
      return ltrim($line, " ");
    }
  }

  public function setDefaultBlock(BlockInterface $block)
  {
    $this->_defaultBlock = get_class($block);
    return $this;
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
    $nlCount = 0;
    foreach($this->parseBlocks($lines, $subBlock) as $block)
    {
      if(empty($block))
      {
        if($nlCount)
        {
          $newLines[] = '<br/>';
        }
        $nlCount++;
      }
      else
      {
        $nlCount = 0;
        if($block instanceof BlockInterface)
        {
          $newLines[] = $block->complete($this->_context);
        }
        else
        {
          $newLines[] = $block;
        }
      }
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
            if($currentBlock)
            {
              if(!$currentBlock->addNewLine($line))
              {
                $currentBlock = null;
              }
            }
            else
            {
              $blocks[] = $line;
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

    $default = $subBlock ? null : $this->_defaultBlock;
    return !$default || $line[0] === '<' ? null : new $default();
  }
}
