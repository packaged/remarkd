<?php
namespace Packaged\Remarkd\Modules;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\Blocks\Block;
use Packaged\Remarkd\RemarkdContext;

abstract class AbstractRemarkdModule implements RemarkdModule
{
  /** @var \Packaged\Remarkd\Attributes */
  protected $_attr;

  protected $_key;

  protected $_addedLine = false;

  public function setAttributes(Attributes $attributes)
  {
    $this->_attr = $attributes;
    return $this;
  }

  public function setKey(string $key): self
  {
    $this->_key = $key;
    return $this;
  }

  abstract public function identifier(): string;

  public function contentType(): string
  {
    return Block::TYPE_SIMPLE;
  }

  public function setTitle(string $title)
  {
    return $this;
  }

  public function closer(): ?string
  {
    return null;
  }

  public function close(): array
  {
    return [];
  }

  public function isOpen(): bool
  {
    return !$this->_addedLine;
  }

  public function isContainer(): bool
  {
    return false;
  }

  public function closesOnEmptyLine(): bool
  {
    return true;
  }

  public function trimLeftLength(): int
  {
    return 0;
  }

  public function trimLeftStr(): string
  {
    return '';
  }

  public function allowChildren(): bool
  {
    return false;
  }

  public function children(): array
  {
    return [];
  }

  public function allowChild($child): bool
  {
    return false;
  }

  public function addChild($child)
  {
    return $this;
  }

  public function allowLine(string $line): ?bool
  {
    if($this->_addedLine)
    {
      return false;
    }
    $this->_addedLine = true;
    return true;
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    return false;
  }

}
