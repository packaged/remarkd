# Adding Remarkd Features

Use this checklist when prompting or implementing a new Remarkd syntax feature.
The project has PHP, Go, and TypeScript parsers, and user-facing syntax should
normally land in all three.

## Good Feature Prompt Shape

Describe the syntax, the expected rendered HTML, and any parser options or file
system behavior needed.

Include:

- The exact Remarkd source example.
- The exact expected HTML output.
- Whether the feature must work in PHP, Go, TypeScript, and browser docs.
- Any project-root, include-file, or host-provided data requirements.
- Edge cases such as missing inputs, nested use, recursion, or disabled output.

Example:

````markdown
Add `t::partial::path/file.remarkd` as a shared Remarkd feature.

It should inject the referenced Remarkd file before parsing. Paths resolve from
the parser project root. Missing files should render `File not found: path`.
It must work in PHP, Go, TypeScript, and the docs preview.

Fixture source:

```remarkd
t::partial::requirements/features/partial/content.remarkd

After the partial.
```

Expected HTML:

```html
<div class="remarkd-section section--level0 section--with-content"><p>Partial <strong>content</strong>
Second line</p><p>After the partial.</p></div>
```
````

## Implementation Checklist

1. Add a shared fixture in `requirements/features/<feature>/`.
   - `input.remarkd`
   - `expected.html`
   - Supporting files if the feature reads external content.

2. Update PHP.
   - Keep default behavior wired through `Remarkd`.
   - Add focused coverage in `tests/ParserCoverageTest.php` when behavior needs setup beyond the shared fixture.
   - Make `tests/RequirementsTest.php` pass against the shared fixture.

3. Update Go.
   - Keep `Parse(...)` backward compatible.
   - Add options only when the feature needs host configuration, such as `ProjectRoot`.
   - Ensure fragment/nested parsing carries those options forward.

4. Update TypeScript.
   - Keep `Remarkd.parse(...)` backward compatible.
   - Avoid Node-only imports in browser-shipped code. Use runtime detection or host-provided options.
   - Update `ts/index.d.ts`.
   - Rebuild `ts/dist/index.js`.

5. Update docs.
   - Add the feature to `docs/assets/features.js`.
   - Update support/conformance text in `docs/conformance.html`.
   - Update `README.md` for public API or option changes.
   - If browser previews need external content, provide it through `docs/assets/preview.js`.

6. Regenerate generated docs assets.
   - `npm run build:docs`

## Verification Commands

Run these before finishing:

```sh
vendor/bin/phpunit tests/RequirementsTest.php tests/ParserCoverageTest.php
go test ./go
npm run test:ts
npm run check:examples
npm run build:docs
```

Note: `vendor/bin/phpunit` without an explicit path may only print usage in this
repo because there is no default PHPUnit XML configuration.

## Design Notes

- Shared syntax belongs in `requirements/features` so PHP, Go, and TypeScript
  stay aligned.
- Docs examples are checked against fixtures, so update both together.
- Browser docs cannot access local files directly. Features that read files need
  a host-provided content map or another browser-safe mechanism.
- Keep generated files current when source changes:
  - `ts/dist/index.js`
  - `docs/assets/remarkd.js`
  - `docs/assets/conformance-status.json`
