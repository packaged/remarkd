import assert from "node:assert/strict";
import { existsSync, readdirSync, readFileSync } from "node:fs";
import { dirname, join, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { Remarkd } from "../dist/index.js";

const testDir = dirname(fileURLToPath(import.meta.url));
const repoRoot = resolve(testDir, "../..");
const featuresDir = join(repoRoot, "requirements/features");
const manifest = JSON.parse(readFileSync(join(testDir, "unsupported-features.json"), "utf8"));
const unsupported = new Set(manifest.unsupported ?? []);

let passed = 0;
let skipped = 0;

if (!existsSync(featuresDir)) {
  console.log("No requirements/features directory found.");
  process.exit(0);
}

for (const feature of readdirSync(featuresDir).sort()) {
  const featureDir = join(featuresDir, feature);
  const inputPath = join(featureDir, "input.remarkd");
  const expectedPath = join(featureDir, "expected.html");

  if (!existsSync(inputPath) || !existsSync(expectedPath)) {
    continue;
  }

  if (unsupported.has(feature)) {
    skipped++;
    console.log(`skip ${feature}`);
    continue;
  }

  const input = readFileSync(inputPath, "utf8");
  const expected = readFileSync(expectedPath, "utf8").trim();
  const actual = Remarkd.parse(input, false, { projectRoot: repoRoot }).trim();

  assert.equal(actual, expected, feature);
  passed++;
  console.log(`pass ${feature}`);
}

assert.equal(
  Remarkd.parse("= Document Title\n:product: Remarkd\n\nThis is {product}.", true).trim(),
  '<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>',
  "document header attributes after title"
);
passed++;
console.log("pass document-header attributes after title");

assert.equal(
  Remarkd.parse("= Document Title\nJane Doe\nv1.0, 2026-06-01: Released\n:product: Remarkd\n\nThis is {product}.", true).trim(),
  '<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>',
  "document header attributes after author and revision"
);
passed++;
console.log("pass document-header attributes after author and revision");

assert.equal(
  Remarkd.parse("// banner comment\n= Document Title\n:product: Remarkd\n\nThis is {product}.", true).trim(),
  '<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>',
  "document header attributes after a leading comment"
);
passed++;
console.log("pass document-header attributes after a leading comment");

console.log(`requirements: ${passed} passed, ${skipped} skipped`);
