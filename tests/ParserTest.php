<?php
namespace Packaged\Tests\Remarkd;

use Packaged\Remarkd\Document;
use Packaged\Remarkd\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
  public function testFullFile()
  {
    $content = file(__DIR__ . '/parserTest.remarkd');
    $parser = new Parser($content);
    $document = $parser->parse();

    self::assertInstanceOf(Document::class, $document);

    self::assertEquals("Document Title", $document->title);

    var_dump($document);
  }

  public function testResources()
  {
    $content = file(dirname(__DIR__) . '/example/resources/remark.remarkd');
    $parser = new Parser($content);
    $document = $parser->parse();

    self::assertInstanceOf(Document::class, $document);
    var_dump($document);
  }
}
