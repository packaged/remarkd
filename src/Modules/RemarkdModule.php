<?php
namespace Packaged\Remarkd\Modules;

use Packaged\Remarkd\Blocks\Block;

interface RemarkdModule extends Block
{
  public function identifier(): string;

  public function setKey(string $key): self;
}
