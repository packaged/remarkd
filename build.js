const esbuild = require('esbuild');
const path = require('path');
const fs = require('fs');

const entryPoint = path.join(__dirname, 'resources/js/remarkd/index.js');
const outfile = path.join(__dirname, 'resources/js/remarkd.js');

esbuild.build({
  entryPoints: [entryPoint],
  bundle: true,
  outfile: outfile,
  format: 'iife',
  globalName: 'Remarkd',
  platform: 'browser',
  target: 'es2020',
  minify: false,
  sourcemap: false,
  banner: {
    js: `/**
 * Remarkd JavaScript Library
 * Bundled from resources/js/remarkd/
 * Generated: ${new Date().toISOString()}
 * 
 * Usage:
 *   const html = await Remarkd.parse('= Title\\n\\nContent');
 *   const parser = new Remarkd.Parser(lines, new Remarkd.Remarkd());
 */`
  },
  footer: {
    js: `
// Make available globally and fix parse function to use correct references
if (typeof window !== 'undefined') {
  window.Remarkd = Remarkd;
  // Fix parse function to use classes from exports
  if (Remarkd && Remarkd.parse) {
    const originalParse = Remarkd.parse;
    Remarkd.parse = async function(text, options) {
      const RemarkdClass = Remarkd.Remarkd;
      const ParserClass = Remarkd.Parser;
      if (!RemarkdClass || !ParserClass) {
        throw new Error('Remarkd classes not available');
      }
      const remarkd = new RemarkdClass();
      const lines = text.split(/\\r?\\n/);
      const parser = new ParserClass(lines, remarkd);
      const doc = await parser.parse();
      return doc.render();
    };
  }
}
`
  }
}).then(() => {
  console.log('✓ Built resources/js/remarkd.js');
  console.log('  Available globally as: window.Remarkd');
}).catch((error) => {
  console.error('✗ Build failed:', error);
  process.exit(1);
});

