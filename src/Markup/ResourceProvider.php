<?php
namespace Packaged\Remarkd\Markup;

interface ResourceProvider
{
  public function getResource(): ?MarkupResource;
}
