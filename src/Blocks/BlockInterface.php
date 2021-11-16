<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

interface BlockInterface
{
  public function addNewLine(string $line);

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string;
}
