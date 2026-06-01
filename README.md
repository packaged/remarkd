# remarkd

remarkd is a PHP-first Markdown parser with selected AsciiDoc-style blocks,
document attributes, conditionals, and object macros.

Full usage documentation is available as a static, multi-page site in
[docs/](docs/). The site covers getting started in all three languages, the
concepts behind the format, a complete feature reference with live previews, an
interactive playground, and the cross-language conformance suite.

The pages use ES modules, which browsers block over `file://`. Serve the
directory over HTTP to preview locally (GitHub Pages already serves over HTTP,
so the published site works as-is):

```sh
npm run serve:docs   # then open http://localhost:8000
```

## Installing the parser (developers)

Writers don't need any of this — the [documentation site](docs/) covers how to
*write* Remarkd. The instructions below are for developers embedding a parser.

### PHP (Composer)

```sh
composer require packaged/remarkd
```

```php
use Packaged\Remarkd\Remarkd;

$html = (new Remarkd())->parse("# Heading\n\nA **bold** paragraph.");
```

### Go

```sh
go get github.com/packaged/remarkd
```

```go
import "github.com/packaged/remarkd"

html := remarkd.Parse("# Heading\n\nA **bold** paragraph.")
```

### TypeScript / JavaScript

```sh
npm install
npm run build
```

```ts
import { Remarkd } from "remarkd-js";

const html = Remarkd.parse("# Heading\n\nA **bold** paragraph.");
```

### Document-header mode

Pass a truthy second argument to enable AsciiDoc-style document detection: a
leading `= Title`, optional author and revision lines, and document attributes
are read as a header before the body is parsed.

```php
$html = $remarkd->parse($text, true);   // PHP
```
```go
html := remarkd.Parse(text, true)       // Go
```
```ts
const html = Remarkd.parse(text, true); // TS
```

### Includes And Partials

Use `include::path/to/file.remarkd[]` to render another Remarkd document in
place. The included file is parsed as its own document, so its section wrapper is
preserved.

```remarkd
include::shared/intro.remarkd[]
```

Remarkd includes local partials with `t::partial::path/to/file.remarkd`.
Paths resolve from the parser's project root, or from the current process path
when no project root is set.

```php
$remarkd = new Remarkd();
$remarkd->ctx()->setProjectRoot(__DIR__ . '/docs');

$html = $remarkd->parse("t::partial::shared/intro.remarkd");
```
```go
html := remarkd.ParseWithOptions(
  "t::partial::shared/intro.remarkd",
  remarkd.Options{ProjectRoot: "docs"},
)
```
```ts
const html = Remarkd.parse("t::partial::shared/intro.remarkd", false, {
  projectRoot: "docs",
});
```

For document-shaped partials, use explicit options to remove a leading document
title and trailing lines:

```remarkd
t::partial::shared/page.remarkd[strip-title,drop-last=3]
```

This repository includes PHP, Go, and TypeScript parsers. All three
implementations are kept aligned with the shared fixtures in
`requirements/features`.

## Shared Requirements

Each requirement fixture contains `input.remarkd` and `expected.html`. The
current suite covers 51 user-facing syntax features. Run the language-specific
conformance checks with:

```sh
vendor/bin/phpunit tests/RequirementsTest.php
go test ./go
npm run test:ts
```

## Documentation Site

The help site is designed for GitHub Pages from the `docs/` directory. Pages are
hand-authored HTML that share a stylesheet and a few small ES modules; the
shared navigation is injected by `docs/assets/site.js`, and the feature
reference and conformance pages render from one data module,
`docs/assets/features.js`. The playground and previews are powered by the
TypeScript parser bundle.

Refresh the generated assets after parser changes:

```sh
npm run build:docs
```

This rebuilds the playground bundle and regenerates
`docs/assets/conformance-status.json`, which records whether each parser (PHP,
Go, TypeScript) reproduces every fixture. The conformance page reads it for the
PHP and Go columns and computes the TypeScript column live in the browser.
Regenerating the JSON requires the `php` and `go` toolchains locally; if either
is missing, that column is recorded as unbuilt and the build still succeeds.

The documented examples (sources and expected HTML) are checked against the
fixtures so they cannot silently drift:

```sh
npm run check:examples
```

Then configure GitHub Pages to publish from `docs/` on the desired branch.
