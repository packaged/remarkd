<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class HeadingBlock implements BlockInterface, BlockStartCodes
{
  protected $_heading = '';
  protected $_level;

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    $this->_level = strlen(preg_replace('/^(#+).+/', '\1', $line));
    $this->_heading = trim(substr(trim($line), $this->_level));
    return null;
  }

  public function complete(RemarkdContext $ctx): string
  {
    $anchor = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($this->_heading)));
    preg_replace_callback('/^(.+?)\s+{#([a-z0-9\-]+?)}$/', function ($matches) use (&$anchor) {
      $anchor = $matches[2];
      $this->_heading = $matches[1];
    }, $this->_heading);

    return $ctx->ruleEngine()->parse(
      '<h' . $this->_level . ' id="' . $anchor . '">' . $this->_heading . '</h' . $this->_level . '>'
    );
  }

  public function startCodes(): array
  {
    return ['# ', '##',];
  }

}
