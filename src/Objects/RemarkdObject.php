<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;

interface RemarkdObject
{
  public function getIdentifier(): string;

  public function create(RemarkdContext $context, ?Attributes $configuration, $key = null);

  public function render(): string;
}
