// build-conformance.mjs — bakes per-language conformance results into
// docs/assets/conformance-status.json.
//
// The TypeScript result is also computed live in the browser, but PHP and Go
// cannot run there, so this build step runs all three parsers locally and
// records, per fixture, whether each reproduces expected.html. If the PHP or Go
// toolchain is missing, that language is recorded as unavailable (null) and the
// build still succeeds.
//
// Usage:  npm run build:conformance

import { readdirSync, readFileSync, writeFileSync, existsSync } from "node:fs";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";

const repoRoot = fileURLToPath(new URL("../../", import.meta.url));
const featuresDir = `${repoRoot}requirements/features`;
const bundleUrl = new URL("../../docs/assets/remarkd.js", import.meta.url);
const outPath = `${repoRoot}docs/assets/conformance-status.json`;

const norm = (value) => value.replace(/\r\n?/g, "\n").trim();

function tsResults(Remarkd) {
  const map = {};
  for (const slug of readdirSync(featuresDir).sort()) {
    const dir = `${featuresDir}/${slug}`;
    const input = `${dir}/input.remarkd`;
    const expected = `${dir}/expected.html`;
    if (!existsSync(input) || !existsSync(expected)) continue;
    const actual = Remarkd.parse(readFileSync(input, "utf8"));
    map[slug] = norm(actual) === norm(readFileSync(expected, "utf8"));
  }
  return map;
}

function runReporter(label, command, args) {
  try {
    const stdout = execFileSync(command, args, { cwd: repoRoot, encoding: "utf8" });
    return JSON.parse(stdout.trim());
  } catch (error) {
    console.warn(`  ! ${label} unavailable — recording as unknown (${error.message.split("\n")[0]})`);
    return null;
  }
}

const { Remarkd } = await import(bundleUrl.href);

console.log("Computing conformance results…");
const ts = tsResults(Remarkd);
const php = runReporter("PHP", "php", ["tools/conformance.php"]);
const go = runReporter("Go", "go", ["run", "./tools/conformance"]);

const slugs = Object.keys(ts).sort();
const features = {};
for (const slug of slugs) {
  features[slug] = {
    php: php ? php[slug] ?? false : null,
    go: go ? go[slug] ?? false : null,
    ts: ts[slug],
  };
}

const payload = {
  generatedAt: new Date().toISOString(),
  available: { php: php !== null, go: go !== null, ts: true },
  features,
};

writeFileSync(outPath, JSON.stringify(payload, null, 2) + "\n");

const tally = (lang) => slugs.filter((s) => features[s][lang] === true).length;
console.log(
  `Wrote ${outPath}\n  PHP ${php ? tally("php") + "/" + slugs.length : "n/a"} · ` +
    `Go ${go ? tally("go") + "/" + slugs.length : "n/a"} · TS ${tally("ts")}/${slugs.length}`,
);
