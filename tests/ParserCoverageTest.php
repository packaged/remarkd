<?php

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\Modules\IncludeModule;
use Packaged\Remarkd\Objects\AnchorObject;
use Packaged\Remarkd\Objects\References\ReferenceListObject;
use Packaged\Remarkd\Objects\References\ReferenceObject;
use Packaged\Remarkd\Parser;
use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class ParserCoverageHarness extends Parser
{
  public function evalCondition(string $condition): bool
  {
    return $this->_ifevalValidate($condition);
  }
}

class ParserCoverageTest extends TestCase
{
  public function testDocumentHeadersPopulateMetadataAndSkipHeaderLines(): void
  {
    $parser = new Parser(
      [
        '',
        '// ignored',
        '= Document Title',
        'Jane Doe; John Smith',
        'v1.2, 2026-06-01: Released',
        ':product: Remarkd',
        '',
        'This is {product}.',
      ],
      new Remarkd()
    );

    $document = $parser->parse(true);

    self::assertSame('Document Title', $document->title);
    self::assertSame(['Jane Doe', 'John Smith'], $document->authors);
    self::assertSame('v1.2', $document->revisionNumber);
    self::assertSame('2026-06-01', $document->revisionDate);
    self::assertSame('Released', $document->revisionRemark);
    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>',
      (string)$document->produceSafeHTML()
    );
  }

  public function testConditionalEvalOperators(): void
  {
    $parser = new ParserCoverageHarness([], new Remarkd());

    self::assertTrue($parser->evalCondition('5 === 5'));
    self::assertTrue($parser->evalCondition('5 == 5'));
    self::assertTrue($parser->evalCondition('5 != 6'));
    self::assertTrue($parser->evalCondition('5 <= 5'));
    self::assertTrue($parser->evalCondition('4 < 5'));
    self::assertTrue($parser->evalCondition('5 >= 5'));
    self::assertTrue($parser->evalCondition('6 > 5'));
    self::assertTrue($parser->evalCondition('1 && 1'));
    self::assertTrue($parser->evalCondition('0 || 1'));
    self::assertTrue($parser->evalCondition('a in a,b'));
    self::assertTrue($parser->evalCondition('c nin a,b'));
    self::assertFalse($parser->evalCondition('not-an-expression'));
  }

  public function testIncludeModuleRendersExistingFilesAndMissingFallback(): void
  {
    $root = sys_get_temp_dir() . '/remarkd-include-' . uniqid('', true);
    mkdir($root);
    file_put_contents($root . '/included.remarkd', 'Included **content**');

    $remarkd = new Remarkd();
    $remarkd->registerModule(IncludeModule::create($remarkd, $root));

    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><div class="remarkd-section section--level0 section--with-content"><p>Included <strong>content</strong></p></div></div>',
      $remarkd->parse('include::included.remarkd[]')
    );
    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><!-- unable to include missing.remarkd--></div>',
      $remarkd->parse('include::missing.remarkd[]')
    );
  }

  public function testPartialTraitInjectsFileLines(): void
  {
    $root = sys_get_temp_dir() . '/remarkd-partial-' . uniqid('', true);
    mkdir($root);
    file_put_contents($root . '/partial.remarkd', "Partial **content**\nSecond line");

    $remarkd = new Remarkd();
    $remarkd->ctx()->setProjectRoot($root);

    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><p>Partial <strong>content</strong>' . "\n" . 'Second line</p></div>',
      $remarkd->parse('t::partial::partial.remarkd')
    );
  }

  public function testPartialTraitRendersMissingFileFallback(): void
  {
    $root = sys_get_temp_dir() . '/remarkd-partial-' . uniqid('', true);
    mkdir($root);

    $remarkd = new Remarkd();
    $remarkd->ctx()->setProjectRoot($root);

    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><p>File not found: missing.remarkd</p></div>',
      $remarkd->parse('t::partial::missing.remarkd')
    );
  }

  public function testPartialTraitSupportsExplicitContentTrimming(): void
  {
    $root = sys_get_temp_dir() . '/remarkd-partial-' . uniqid('', true);
    mkdir($root);
    file_put_contents(
      $root . '/document.remarkd',
      "= Partial Title\n\nBody **content**\nFooter one\nFooter two\nFooter three\n\n"
    );

    $remarkd = new Remarkd();
    $remarkd->ctx()->setProjectRoot($root);

    self::assertSame(
      '<div class="remarkd-section section--level0 section--with-content"><p>Body <strong>content</strong></p></div>',
      $remarkd->parse('t::partial::document.remarkd[strip-title,drop-last=3]')
    );
  }

  public function testObjectFallbackBranches(): void
  {
    self::assertSame('[MISSING-CONTEXT]', (new ReferenceObject())->render());
    self::assertSame('[MISSING-CONTEXT]', (new ReferenceListObject())->render());
    self::assertSame(
      '[ANCHOR MISSING NAME]',
      (new AnchorObject())->create(new Remarkd()->ctx(), new Attributes(), null)->render()
    );
  }
}
