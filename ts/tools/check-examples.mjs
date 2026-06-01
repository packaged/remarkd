// check-examples.mjs — verifies that the examples documented on the site stay
// in sync with the shared fixtures.
//
// The docs catalogue (docs/assets/features.js) embeds, for every feature, the
// Remarkd `source` and the `expected` HTML. This script compares each entry
// against requirements/features/<slug>/{input.remarkd,expected.html} and reports
// any drift, any documented feature with no fixture, and any fixture that is not
// yet documented.
//
// Usage:  npm run check:examples   (exits non-zero on drift)

import { readFile, readdir } from "node:fs/promises";
import { fileURLToPath } from "node:url";

const root = new URL("../../", import.meta.url); // repo root
const featuresUrl = new URL("docs/assets/features.js", root);
const fixturesDir = new URL("requirements/features/", root);

const stripTrailingNewline = (value) => value.replace(/\r\n?/g, "\n").replace(/\n$/, "");

async function readFixture(slug, file) {
  const url = new URL(`${slug}/${file}`, fixturesDir);
  try {
    return stripTrailingNewline(await readFile(url, "utf8"));
  } catch {
    return null;
  }
}

async function main() {
  const { FEATURES } = await import(featuresUrl.href);
  const documented = new Set(FEATURES.map((f) => f.slug));

  const problems = [];

  for (const feature of FEATURES) {
    const source = await readFixture(feature.slug, "input.remarkd");
    const expected = await readFixture(feature.slug, "expected.html");

    if (source === null || expected === null) {
      problems.push(`✗ ${feature.slug}: documented but no fixture in requirements/features/${feature.slug}/`);
      continue;
    }
    if (stripTrailingNewline(feature.source) !== source) {
      problems.push(`✗ ${feature.slug}: documented source does not match input.remarkd`);
    }
    if (stripTrailingNewline(feature.expected) !== expected) {
      problems.push(`✗ ${feature.slug}: documented expected HTML does not match expected.html`);
    }
  }

  const entries = await readdir(fileURLToPath(fixturesDir), { withFileTypes: true });
  for (const entry of entries) {
    if (entry.isDirectory() && !documented.has(entry.name)) {
      problems.push(`✗ ${entry.name}: fixture exists but is not documented in docs/assets/features.js`);
    }
  }

  if (problems.length) {
    console.error("Example drift detected:\n" + problems.join("\n"));
    process.exit(1);
  }
  console.log(`OK — ${FEATURES.length} documented features match their fixtures.`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
