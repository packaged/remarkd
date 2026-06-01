// ts/src/index.ts
var Remarkd = class {
  parse(markdown, detectHeaders = false, options = {}) {
    return parse(markdown, detectHeaders, options);
  }
  static parse(markdown, detectHeaders = false, options = {}) {
    return parse(markdown, detectHeaders, options);
  }
};
function parse(markdown, detectHeaders = false, options = {}) {
  const parser = new Parser(markdown, detectHeaders, options);
  return parser.parse();
}
var Parser = class _Parser {
  constructor(markdown, detectHeaders, options = {}) {
    this.detectHeaders = detectHeaders;
    this.options = options;
    this.lines = preprocessPartials(preprocessMarkdown(markdown), options);
  }
  lines;
  index = 0;
  attrs = { plus: "+" };
  references = [];
  conditions = [];
  parse() {
    if (this.detectHeaders) {
      this.processDocumentHeader();
    }
    return this.wrapDocument(this.parseBlocks());
  }
  processDocumentHeader() {
    while (this.index < this.lines.length) {
      const line = this.lines[this.index] ?? "";
      if (line.trim() === "" || line.startsWith("// ")) {
        this.index++;
        continue;
      }
      break;
    }
    if ((this.lines[this.index] ?? "").startsWith("= ")) {
      this.index++;
      let metadataLines = 0;
      while (this.index < this.lines.length) {
        const line = this.lines[this.index] ?? "";
        if (line.trim() === "") {
          this.index++;
          break;
        }
        if (line.startsWith("// ")) {
          this.index++;
          continue;
        }
        if (/^:!?[\w.-]+!?:/.test(line)) {
          this.addDocumentAttr(line);
          this.index++;
          continue;
        }
        if (metadataLines < 2) {
          metadataLines++;
          this.index++;
          continue;
        }
        break;
      }
    }
  }
  wrapDocument(html) {
    const firstSection = html.indexOf('<div class="remarkd-section section--level1');
    if (firstSection === 0)
      return html;
    if (firstSection > 0) {
      const preamble = html.slice(0, firstSection);
      return `<div class="remarkd-section section--level0 section--${preamble ? "with-content" : "empty"}">${preamble}</div>${html.slice(firstSection)}`;
    }
    return `<div class="remarkd-section section--level0 section--${html ? "with-content" : "empty"}">${html}</div>`;
  }
  parseBlocks(stop, stopSectionLevel) {
    const parts = [];
    let pendingTitle = null;
    let pendingAttrs = null;
    while (this.index < this.lines.length) {
      let line = this.replaceAttrs(this.lines[this.index] ?? "");
      const sectionMatch = line.match(/^(={2,6}) (.*)$/);
      if (sectionMatch && stopSectionLevel !== void 0 && sectionMatch[1].length - 1 <= stopSectionLevel) {
        break;
      }
      if (stop && line === stop) {
        this.index++;
        break;
      }
      const conditional = this.parseConditional(line);
      if (conditional.handled) {
        if (conditional.inline) {
          line = this.replaceAttrs(conditional.inline);
        } else {
          continue;
        }
      }
      if (this.conditions.includes(false)) {
        this.index++;
        continue;
      }
      if (line.trim() === "") {
        this.index++;
        continue;
      }
      if (/^:!?[\w.-]+!?:/.test(line)) {
        this.addDocumentAttr(line);
        this.index++;
        continue;
      }
      if (line.startsWith("// ")) {
        this.index++;
        continue;
      }
      if (line === "////") {
        this.index++;
        while (this.index < this.lines.length && this.lines[this.index] !== "////")
          this.index++;
        if (this.index < this.lines.length)
          this.index++;
        continue;
      }
      if (line.startsWith(".") && !line.startsWith("..") && line !== ".") {
        pendingTitle = line.slice(1);
        this.index++;
        continue;
      }
      if (/^\[[^\]]*]$/.test(line)) {
        pendingAttrs = parseAttrs(line);
        this.index++;
        continue;
      }
      const block = this.parseBlock(line, pendingTitle, pendingAttrs);
      pendingTitle = null;
      pendingAttrs = null;
      parts.push(block);
    }
    return parts.join("");
  }
  parseBlock(line, title, attrs) {
    if (line.startsWith("_|_#"))
      return this.parseTabs();
    if (line.startsWith("_-_#"))
      return this.parseAccordion();
    if (line.startsWith("_|- "))
      return this.parseSteps();
    if (line === "|===")
      return this.parseTable(title, attrs);
    if (line === "____")
      return this.parseDelimited(attrs);
    if (line === "```")
      return this.parseVerbatim("```", "code");
    if (line === "....")
      return this.parseVerbatim("....", "code");
    if (/^!{4,10}$/.test(line))
      return this.parseVerbatim(line, "code");
    if (/^-{4,10}$/.test(line))
      return this.parseListing(line);
    if (/^={4,10}$/.test(line))
      return this.parseCompound(line, "example-block");
    if (/^\*{4,10}$/.test(line))
      return this.parseCompound(line, "sidebar-block");
    if (/^!![\w-]+!!$/.test(line))
      return this.parseId(line);
    if (/^include::[^\[]+\[.*]\s*$/.test(line))
      return this.parseInclude(line);
    if (/^[^:]+:: .+/.test(line))
      return this.parseDefinitions(line);
    if (/^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/.test(line))
      return this.parseAdmonition(line);
    if (/^<(\d+|\d+\.\d+|\w|[^>])> /.test(line))
      return this.parseCalloutBlock(line);
    if (/^(#{1,6}) /.test(line)) {
      const [, hashes, text] = line.match(/^(#{1,6}) (.*)$/) ?? [];
      this.index++;
      return `<h${hashes.length}>${this.inline(text)}</h${hashes.length}>`;
    }
    if (/^(={2,6}) /.test(line))
      return this.parseSection(line, attrs);
    if (line.trim() === "<<<") {
      this.index++;
      return '<p><div style="break-after:page"></div></p>';
    }
    if (/^-{3}$/.test(line.trim())) {
      this.index++;
      return "<p><hr /></p>";
    }
    if (/^((\*|-){1,10}) /.test(line))
      return this.parseUnorderedList();
    if (/^((\d*\.)+) /.test(line))
      return this.parseOrderedList();
    if (/^{{/.test(line) || /{{ref\b/.test(line) || /{{reflist\b/.test(line)) {
      this.index++;
      return this.inline(line);
    }
    return this.parseParagraph(line, title, attrs);
  }
  parseInclude(line) {
    this.index++;
    const match = line.match(/^include::([^\[]+)\[.*]\s*$/);
    const filename = match?.[1]?.trim() ?? "";
    const content = readPartialFile(this.options, filename);
    if (content === null)
      return `<!-- unable to include ${filename}-->`;
    const parser = new _Parser(content, false, this.options);
    parser.attrs = this.attrs;
    parser.references = this.references;
    const output = parser.parse();
    this.references = parser.references;
    return output;
  }
  parseParagraph(first, title, attrs) {
    const hardbreaks = !!attrs?.pos.includes("%hardbreaks");
    const lines = [first.trim().replace(/ \+$/, "")];
    this.index++;
    while (this.index < this.lines.length) {
      const line = this.replaceAttrs(this.lines[this.index] ?? "");
      if (line.trim() === "" || this.isBlockStart(line))
        break;
      lines.push(line.trim() === "+" ? "" : line.trim().replace(/ \+$/, ""));
      this.index++;
    }
    const klass = attrs?.pos[0]?.startsWith(".") ? ` class="${attrs.pos[0].slice(1)}"` : "";
    const joiner = hardbreaks || first.endsWith(" +") || lines.includes("") ? "\n<br />\n" : "\n";
    const content = `${title ? `<div class="title">${title}</div>` : ""}${this.inline(lines.join(joiner))}`;
    return `<p${klass}>${content}</p>`;
  }
  parseSection(line, attrs) {
    const [, marker, title] = line.match(/^(={2,6}) (.*)$/) ?? [];
    const level = marker.length - 1;
    const id = attrs?.pos.find((pos) => pos.startsWith("#"))?.slice(1) || hyphenate(title);
    this.index++;
    const content = this.parseBlocks(void 0, level);
    return `<div class="remarkd-section section--level${level} section--${content ? "with-content" : "empty"}" id="${id}"><h${level + 1}>${this.inline(title)}</h${level + 1}>${content}</div>`;
  }
  parseVerbatim(closer, tag) {
    this.index++;
    const lines = [];
    while (this.index < this.lines.length && this.lines[this.index] !== closer) {
      lines.push(escapeHtml(this.lines[this.index] ?? ""));
      this.index++;
    }
    if (this.index < this.lines.length)
      this.index++;
    return `<${tag}>${lines.join("\n")}</${tag}>`;
  }
  parseListing(closer) {
    this.index++;
    const lines = [];
    while (this.index < this.lines.length && this.lines[this.index] !== closer) {
      lines.push(escapeHtml(this.lines[this.index] ?? ""));
      this.index++;
    }
    if (this.index < this.lines.length)
      this.index++;
    return `<div class="listing-block"><div class="content">${lines.join("\n")}</div></div>`;
  }
  parseCompound(closer, klass) {
    this.index++;
    const inner = this.parseBlocks(closer);
    return `<div class="${klass}"><div class="content">${inner}</div></div>`;
  }
  parseDelimited(attrs) {
    this.index++;
    const lines = [];
    while (this.index < this.lines.length && this.lines[this.index] !== "____") {
      lines.push(this.lines[this.index] ?? "");
      this.index++;
    }
    if (this.index < this.lines.length)
      this.index++;
    if (attrs?.pos[0] === "verse") {
      return `<pre class="verse-block">${lines.join("\n")}</pre>`;
    }
    return `<blockquote><p>${lines.map((part) => part.trim()).join(" ").trim()}</p></blockquote>`;
  }
  parseDefinitions(first) {
    const rows = [];
    let line = first;
    while (this.index < this.lines.length && /^([^:]+):: /.test(line)) {
      const [, term, definition] = line.match(/^([^:]+):: (.*)$/) ?? [];
      rows.push([term, definition]);
      this.index++;
      line = this.lines[this.index] ?? "";
    }
    return `<dl>${rows.map(([term, definition]) => `<dt>${term}</dt><dd>${definition}</dd>`).join("")}</dl>`;
  }
  parseAdmonition(first) {
    const [, level, style] = first.match(/^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])\s?(.*)$/) ?? [];
    const lines = [RegExp.$3 || first.replace(/^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])\s?/, "")];
    this.index++;
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").trim() !== "") {
      lines.push(this.lines[this.index] ?? "");
      this.index++;
    }
    const caption = style === ":" ? `<strong class="hint-caption">${level}: </strong> ` : "";
    return `<div class="hint-${level.toLowerCase()}">${caption}${lines.map((l) => this.inline(l)).join("\n")}</div>`;
  }
  parseCalloutBlock(line) {
    const [, marker, text] = line.match(/^<([^>]+)> (.*)$/) ?? [];
    this.index++;
    return `<li class="callout" data-marker="${marker}">${this.inline(text)}</li>`;
  }
  parseId(line) {
    const id = line.slice(2, -2);
    this.index++;
    const lines = [];
    while (this.index < this.lines.length) {
      lines.push(this.lines[this.index] ?? "");
      this.index++;
    }
    return `<div id="${id}"><p>${this.inline(lines.join("\n"))}</p></div>`;
  }
  parseUnorderedList() {
    const items = [];
    while (this.index < this.lines.length && /^((\*|-){1,10}) /.test(this.lines[this.index] ?? "")) {
      items.push((this.lines[this.index] ?? "").replace(/^((\*|-){1,10}) /, ""));
      this.index++;
    }
    return `<ul>${items.map((item) => `<li><p>${this.inline(item)}</p></li>`).join("\n")}</ul>`;
  }
  parseOrderedList() {
    const items = [];
    while (this.index < this.lines.length && /^((\d*\.)+) /.test(this.lines[this.index] ?? "")) {
      items.push((this.lines[this.index] ?? "").replace(/^((\d*\.)+) /, ""));
      this.index++;
    }
    const nested = (i) => {
      if (i >= items.length)
        return "";
      const child = nested(i + 1);
      return `<ol><li><p>${this.inline(items[i])}</p>${child ? `
${child}` : ""}</li></ol>`;
    };
    return nested(0);
  }
  parseTable(title, attrs) {
    this.index++;
    const rows = [];
    let row = [];
    while (this.index < this.lines.length && this.lines[this.index] !== "|===") {
      const line = this.lines[this.index] ?? "";
      if (line === "") {
        if (row.length)
          rows.push(row);
        row = [];
      } else if (line.startsWith("|")) {
        row.push(...line.replace(/^\|/, "").split(/\s+\|/).map((cell) => this.inline(cell.trim())).filter(Boolean));
      }
      this.index++;
    }
    if (row.length)
      rows.push(row);
    if (this.index < this.lines.length)
      this.index++;
    const head = rows[0] ?? [];
    const body = rows.slice(1);
    const prosCons = isProsCons(attrs);
    const tableClasses = ["remarkd-table"];
    if (prosCons) {
      tableClasses.push("pros-cons-table");
      if (enabled(attrs, "background-colour", true) && enabled(attrs, "background-color", true))
        tableClasses.push("pros-cons-table--background");
      if (enabled(attrs, "text-colour", true) && enabled(attrs, "text-color", true))
        tableClasses.push("pros-cons-table--text-color");
      if (enabled(attrs, "header-icons", true))
        tableClasses.push("pros-cons-table--header-icons");
    }
    const sideClass = (idx) => idx === 0 ? "pros-cons-cell--con" : "pros-cons-cell--pro";
    const classAttr = (idx) => prosCons ? ` class="pros-cons-cell ${sideClass(idx)}"` : "";
    const headerCell = (cell, idx) => {
      const content = prosCons && enabled(attrs, "header-icons", true) ? `${prosConsIcon(attrs, idx)} ${cell}` : cell;
      return `<th${classAttr(idx)}>${content}</th>`;
    };
    const bodyCell = (cell, idx) => `<td${classAttr(idx)}>${cell}</td>`;
    const html = `<table class="${tableClasses.join(" ")}"><thead><tr>${head.map(headerCell).join("")}</tr></thead><tbody>${body.map((cells) => `<tr>${cells.map(bodyCell).join("")}</tr>`).join("")}</tbody></table>`;
    if (!title)
      return html;
    const blockClass = prosCons ? "table-block pros-cons-block" : "table-block";
    return `<div class="${blockClass}"><div class="title">${title}</div>${html}</div>`;
  }
  parseTabs() {
    const tabs = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_|_#")) {
      const line = this.lines[this.index] ?? "";
      const [, id, rawAttrs] = line.match(/^_\|_#([\w-]+)\s*(.*)$/) ?? [];
      this.index++;
      const content = [];
      while (this.index < this.lines.length && !(this.lines[this.index] ?? "").startsWith("_|_#")) {
        content.push(this.lines[this.index] ?? "");
        this.index++;
      }
      tabs.push({ id, name: parseAttrs(rawAttrs).named.name || titleize(id), content: parseFragment(content.join("\n"), this) });
    }
    const headers = tabs.map((tab, i) => `<li><a href="#" data-tab-focus-key="${tab.id}"${i === 0 ? ' class="active"' : ""}>${tab.name}</a></li>`).join("");
    const bodies = tabs.map((tab) => `<div class="tab" data-tab-key="${tab.id}"><div class="content">${tab.content}</div></div>`).join("\n");
    return `<div class="tab-group"><ul class="tab-header">${headers}</ul><div class="tabs">${bodies}</div></div>`;
  }
  parseAccordion() {
    const panels = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_-_#")) {
      const line = this.lines[this.index] ?? "";
      const [, id, rawAttrs] = line.match(/^_-_#([\w-]+)\s*(.*)$/) ?? [];
      this.index++;
      const content = [];
      while (this.index < this.lines.length && !(this.lines[this.index] ?? "").startsWith("_-_#")) {
        content.push(this.lines[this.index] ?? "");
        this.index++;
      }
      panels.push({ name: parseAttrs(rawAttrs).named.name || titleize(id), content: parseFragment(content.join("\n"), this) });
    }
    return `<div class="accordion-container">${panels.map((panel) => `<button class="accordion">${panel.name}</button><div class="panel">${panel.content}</div>`).join("\n")}</div>`;
  }
  parseSteps() {
    const steps = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_|- ")) {
      const line = this.lines[this.index] ?? "";
      const match = line.match(/^_\|- (.*?)(\[.*\])?\s*$/);
      this.index++;
      const content = [];
      while (this.index < this.lines.length && !(this.lines[this.index] ?? "").startsWith("_|- ")) {
        content.push(this.lines[this.index] ?? "");
        this.index++;
      }
      const attrs = parseAttrs(match?.[2] ?? "");
      steps.push({ title: match?.[1] ?? "", img: attrs.named.img, content: parseFragment(content.join("\n"), this) });
    }
    const body = steps.map((step) => `<div class="step"><div class="step-content"><h3>${step.title}</h3>${step.content}</div>${step.img ? `<div class="step-image"><img src="${step.img}" alt="" /></div>` : ""}</div>`).join("\n");
    return `<div class="steps-container"><div class="content">${body}</div></div>`;
  }
  inline(input) {
    let text = input;
    const passthrough = [];
    text = text.replace(/\bpass:\[([^\]]*)]/gs, (_m, raw) => {
      const token = `RMDPASS${passthrough.length}`;
      passthrough.push(raw);
      return token;
    }).replace(/\+\+\+(.+?)\+\+\+/gs, (_m, raw) => {
      const token = `RMDPASS${passthrough.length}`;
      passthrough.push(raw);
      return token;
    });
    text = text.replace(/{{([^}: ]+)(?::([^ }]+))?([^}]*)}}/g, (_m, type, key = "", raw = "") => this.renderObject(type, key, parseAttrs(raw)));
    text = text.replace(/"`([^`]+)`"/g, "&ldquo;$1&rdquo;");
    text = text.replace(/``([^`]+)``/g, (_m, code) => `<span class="monospace">${escapeHtml(code)}</span>`);
    text = text.replace(/`([^`\n]+)`/g, (_m, code) => `<span class="monospace">${escapeHtml(code)}</span>`);
    text = text.replace(/___(.+?)___/g, "<u>$1</u>");
    text = text.replace(/\(c\)|\(C\)/g, "\xA9").replace(/\(r\)|\(R\)/g, "\xAE").replace(/\(tm\)|\(TM\)/g, "\u2122").replace(/\(p\)|\(P\)/g, "\xA7").replace(/\(\+-\)/g, "\xB1").replace(/-\.-/g, "&#8226;");
    text = text.replace(/:(\S+):/g, (match, raw) => emojiAliases[raw.replaceAll("-", "_")] || match);
    text = text.replace(/kbd:\[([^\]]+)]/g, "<kbd>$1</kbd>");
    text = text.replace(/[^#&]#([^#]+)#/g, '<mark class="highlight">$1</mark>');
    text = text.replace(/{(.*?)}\((.*?)\)/g, '<span class="tooltip" title="$2">$1</span>');
    text = text.replace(/!\[([^\]]*)]\((.*?)\s*"([^"]+)"\s*\)/g, '<img src="$2" alt="$1" title="$3"/>');
    text = text.replace(/!\[([^\]]*)]\(([^)]*)\)/g, (_m, alt, src) => alt ? `<img src="${src}" alt="${alt}"/>` : `<img src="${src}"/>`);
    text = text.replace(/image::([^\[]+)\[([^\]]*)]/g, (_m, src, raw) => {
      const [alt, width, height] = raw.split(",").map((part) => part.trim());
      return `<img class="block" src="${src}"${alt ? ` alt="${alt}"` : ""}${width ? ` width="${width}"` : ""}${height ? ` height="${height}"` : ""}/>`;
    });
    text = text.replace(/\[([^\]]*)]\(([^)]*)\)/g, '<a href="$2">$1</a>');
    text = text.replace(/([^="(])((?:http|ftp|https|mailto):\/\/[\w_-]+(?:(?:\.[\w_-]+)+)[\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])(?:\[([^\]\n]+)])?/g, (_m, lead, href, label) => `${lead}<a href="${href}">${label || href}</a>`);
    text = text.replace(/<<([\w\-_]+)(,([^>]+))?>>/g, (_m, id, _label, label) => `<a href="#${id.replace(/^_/, "").replace(/_/g, "-")}">${label || titleize(id)}</a>`);
    text = text.replace(/footnote:\[([^\]]+)]/g, '<sup class="footnote">$1</sup>');
    text = text.replace(/\*\*([^*]+?)\*\*/g, "<strong>$1</strong>");
    text = text.replace(/__([^_]+?)__/g, "<em>$1</em>");
    text = text.replace(/([^\\w])\*([^*]+)\*/g, "$1<strong>$2</strong>");
    text = text.replace(/(\s|^)_([^_]+)_/g, "$1<em>$2</em>");
    text = text.replace(/~~(.+?)~~/g, "<del>$1</del>");
    text = text.replace(/~([^~]+?)~/g, "<sub>$1</sub>");
    text = text.replace(/\^([^^]+?)\^/g, "<sup>$1</sup>");
    text = text.replace(/\[( |x|_|\*)]/g, (_m, value) => {
      if (value === " ")
        return '<input type="checkbox" readonly="readonly">';
      if (value === "x")
        return '<input type="checkbox" readonly="readonly" checked>';
      if (value === "_")
        return '<input type="checkbox">';
      return '<input type="checkbox" checked>';
    });
    text = text.replace(/(\[[^\]]+])##([^#]+)##/g, (_m, raw, value) => `<span class="${parseAttrs(raw).pos[0]}">${value}</span>`);
    text = text.replace(/<(\d+|\d+\.\d+)>/g, '<i class="conum" data-value="$1"></i>');
    return passthrough.reduce((out, raw, idx) => out.replaceAll(`RMDPASS${idx}`, raw), text);
  }
  renderObject(type, key, attrs) {
    switch (type) {
      case "link": {
        const text = attrs.named.text || titleize(key);
        const target = attrs.named.target ? ` target="${attrs.named.target}"` : "";
        const hreflang = attrs.named.hreflang ? ` hreflang="${attrs.named.hreflang}"` : "";
        return `<a href="${attrs.named.href || key}"${target}${hreflang}>${text}</a>`;
      }
      case "button": {
        const text = attrs.named.text || titleize(key);
        const color = attrs.named.color || "gray";
        const target = attrs.named.target ? ` target="${attrs.named.target}"` : "";
        return `<a href="${attrs.named.href || `#${key}`}" class="btn btn--${color}"${target}>${text}</a>`;
      }
      case "br":
        return "<br>";
      case "anchor":
        return attrs.named.name ? `<a name="${attrs.named.name}"></a>` : "[ANCHOR MISSING NAME]";
      case "meter": {
        const id = attrs.named.id || "remarkd-meter-150";
        const label = attrs.named.label ? `<label for="${id}" class="remarkd-meter-label">${attrs.named.label}</label>` : "";
        return `${label}<meter id="${id}" class="remarkd-meter"  min="${attrs.named.min || "0"}" max="${attrs.named.max || "100"}" value="${attrs.named.value || "0"}"">${attrs.named.text || ""}</meter>`;
      }
      case "video": {
        const padding = attrs.named.aspect === "4:3" ? "75" : attrs.named.aspect === "1:1" ? "100" : attrs.named.aspect === "3:2" ? "66.66" : attrs.named.aspect === "8:5" ? "62.5" : "56.25";
        if (attrs.named.source === "self") {
          return `<div class="video-container" style="padding-top: ${padding}%"><video controls><source src="${key}" type="${attrs.named.type || "video/mp4"}"></video></div>`;
        }
        const opts = [`rel=0`];
        if (attrs.named.end)
          opts.unshift(`end=${attrs.named.end}`);
        if (attrs.named.start)
          opts.unshift(`start=${attrs.named.start}`);
        return `<div class="video-container" style="padding-top: ${padding}%"><iframe src="https://www.youtube.com/embed/${key}?${opts.join(";")}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>`;
      }
      case "ref": {
        const ref = { num: this.references.length + 1, code: attrs.named.code || `${this.references.length + 1}RM`, content: attrs.named.content || "" };
        this.references.push(ref);
        return `<sup class="reference"><a id="rmdref-bdy-${ref.code}" href="#rmdref-ft-${ref.code}">[${ref.num}]</a></sup>`;
      }
      case "reflist":
        return this.references.length ? `<ol class="reference">${this.references.map((ref) => `<li id="rmdref-ft-${ref.code}"><a href="#rmdref-bdy-${ref.code}" class="reference-tobody">^</a> ${this.inline(ref.content)}</li>`).join("")}</ol>` : "";
      case "img": {
        let style = `display: ${attrs.named.display || "inline-block"};max-width: ${attrs.named["max-width"] || "100%"};`;
        if (attrs.named.float)
          style += `float: ${attrs.named.float};`;
        const classes = attrs.pos.filter((pos) => pos.startsWith(".")).map((pos) => pos.slice(1));
        return `<img src="${attrs.named.src || ""}"${attrs.named.alt ? ` alt="${attrs.named.alt}"` : ""} style="${style}"${classes.length ? ` class="${classes.join(" ")}"` : ""} />`;
      }
      default:
        return "";
    }
  }
  replaceAttrs(line) {
    for (const [key, value] of Object.entries(this.attrs)) {
      line = line.replaceAll(`{${key}}`, String(value));
    }
    return line;
  }
  addDocumentAttr(line) {
    const match = line.match(/^:(!?[\w.-]+)(!)?:(.*)?$/);
    if (!match)
      return;
    this.attrs[match[1]] = match[3]?.trim() || !match[2];
  }
  parseConditional(line) {
    const match = line.match(/^(end)?if(def|ndef|eval|nempty|empty|true|false)?::([^\[]*)\[([^]*)]$/);
    if (!match)
      return { handled: false };
    this.index++;
    if (match[1] === "end") {
      this.conditions.pop();
      return { handled: true };
    }
    const active = !this.conditions.includes(false);
    const valid = active && this.validateCondition(match[2] || "def", match[3], match[4]);
    if (match[2] !== "eval" && match[4]) {
      return { handled: true, inline: valid ? match[4] : void 0 };
    }
    this.conditions.push(valid);
    return { handled: true };
  }
  validateCondition(type, condition, expression) {
    if (type === "eval")
      return evaluateExpression(expression);
    const groups = condition.split(",");
    return groups.some((group) => group.split("+").every((name) => {
      const value = this.attrs[name];
      if (type === "def")
        return value !== void 0;
      if (type === "ndef")
        return value === void 0;
      if (type === "true")
        return value === true || value === "true" || value === "1";
      if (type === "false")
        return value === false || value === "false" || value === "0";
      if (type === "empty")
        return value === void 0 || value === "";
      if (type === "nempty")
        return value !== void 0 && value !== "";
      return false;
    }));
  }
  isBlockStart(line) {
    return line === "```" || line === "____" || line === "|===" || /^[-=*.!]{4,10}$/.test(line) || /^#{1,6} /.test(line) || /^={2,6} /.test(line) || line.trim() === "<<<" || /^((\d*\.)+) /.test(line) || /^((\*|-){1,10}) /.test(line) || /^(end)?if(def|ndef|eval|nempty|empty|true|false)?::/.test(line) || /^!![\w-]+!!$/.test(line) || /^include::[^\[]+\[.*]\s*$/.test(line) || line.startsWith("_|_#") || line.startsWith("_-_#") || line.startsWith("_|- ") || /^[^:]+:: .+/.test(line) || /^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/.test(line) || /^<(\d+|\d+\.\d+|\w|[^>])> /.test(line) || /^-{3}$/.test(line.trim());
  }
};
function preprocessMarkdown(markdown) {
  const lines = [];
  for (const rawLine of markdown.replace(/\r\n?/g, "\n").replace(/\n$/, "").split("\n")) {
    const line = rawLine.replace(/{!(.*)!}/g, "$1");
    if (lines.length && lines[lines.length - 1].endsWith("\\")) {
      lines[lines.length - 1] = lines[lines.length - 1].replace(/[\\ ]+$/, "") + "\n" + line;
    } else {
      lines.push(line);
    }
  }
  return lines;
}
function parseFragment(markdown, parent) {
  const parser = new Parser(
    markdown,
    false,
    parent.options
  );
  parser.attrs = parent.attrs;
  return parser.parse().replace(/^<div class="remarkd-section section--level0 section--with-content">/, "").replace(/<\/div>$/, "");
}
function preprocessPartials(lines, options, depth = 0) {
  if (depth > 10)
    return lines;
  const result = [];
  for (const line of lines) {
    const match = line.match(/^t::partial::(.*)$/);
    if (!match) {
      result.push(line);
      continue;
    }
    const [filename, attrs] = parsePartialTarget(match[1] ?? "");
    const content = readPartialFile(options, filename);
    if (content === null) {
      result.push(`File not found: ${filename}`);
      continue;
    }
    let partialLines = preprocessMarkdown(content);
    if (partialEnabled(attrs, "strip-title") && (partialLines[0] ?? "").startsWith("=")) {
      partialLines = partialLines.slice(1);
    }
    while (partialLines.length && partialLines[partialLines.length - 1].trim() === "") {
      partialLines.pop();
    }
    const dropLast = Number.parseInt(attrs["drop-last"] ?? "0", 10);
    if (dropLast > 0) {
      partialLines.splice(-dropLast);
    }
    result.push(...preprocessPartials(partialLines, options, depth + 1));
  }
  return result;
}
function parsePartialTarget(raw) {
  let filename = raw.trim();
  const attrs = {};
  const match = filename.match(/^(.*?)\[([^\]]*)]$/);
  if (match) {
    filename = match[1].trim();
    for (const item of match[2].split(",")) {
      const attr = item.trim();
      if (!attr)
        continue;
      const eq = attr.indexOf("=");
      if (eq === -1) {
        attrs[attr] = "true";
      } else {
        attrs[attr.slice(0, eq).trim()] = attr.slice(eq + 1).trim().replace(/^["']|["']$/g, "");
      }
    }
  }
  return [filename, attrs];
}
function partialEnabled(attrs, key) {
  if (!(key in attrs))
    return false;
  return !["0", "false", "no"].includes(attrs[key].toLowerCase());
}
function readPartialFile(options, filename) {
  if (options.partials && Object.prototype.hasOwnProperty.call(options.partials, filename)) {
    return options.partials[filename];
  }
  const proc = globalThis.process;
  const fs = proc?.getBuiltinModule?.("node:fs");
  if (!fs?.existsSync || !fs?.readFileSync)
    return null;
  const path = joinPath(options.projectRoot || proc?.cwd?.() || "", filename);
  if (!fs.existsSync(path))
    return null;
  return fs.readFileSync(path, "utf8");
}
function joinPath(root, filename) {
  if (/^(\/|[A-Za-z]:[\\/])/.test(filename) || !root)
    return filename;
  return `${root.replace(/[\\/]+$/, "")}/${filename.replace(/^[\\/]+/, "")}`;
}
function parseAttrs(raw) {
  const clean = raw.trim().replace(/^\[/, "").replace(/]$/, "");
  const pos = [];
  const named = {};
  const re = /(^|[\s,]+)([^, =}]+)(=(("([^"]*)")|([^\s,}]*)))?/g;
  let match;
  while (match = re.exec(clean)) {
    const key = match[2];
    const value = match[6] || match[7] || "";
    pos.push(key);
    named[key] = value;
  }
  return { raw: clean, pos, named };
}
function isProsCons(attrs) {
  return !!attrs && (attrs.pos[0] === "pros-cons" || attrs.pos[0] === "proscons" || "pros-cons" in attrs.named || "proscons" in attrs.named);
}
function enabled(attrs, key, fallback) {
  if (!attrs || !(key in attrs.named))
    return fallback;
  return !["0", "false", "no", "off"].includes((attrs.named[key] || "").toLowerCase());
}
function prosConsIcon(attrs, index) {
  const icon = index === 0 ? attrs?.named["con-icon"] || "\u274C" : attrs?.named["pro-icon"] || "\u2705";
  return `<span class="pros-cons-icon">${escapeHtml(icon)}</span>`;
}
var emojiAliases = {
  "+1": "\u{1F44D}",
  thumbsup: "\u{1F44D}",
  "-1": "\u{1F44E}",
  thumbsdown: "\u{1F44E}",
  eyes: "\u{1F440}",
  grinning: "\u{1F600}",
  grin: "\u{1F601}",
  joy: "\u{1F602}",
  smiley: "\u{1F603}",
  smile: "\u{1F604}",
  sweat_smile: "\u{1F605}",
  satisfied: "\u{1F606}",
  laughing: "\u{1F606}",
  innocent: "\u{1F607}",
  smiling_imp: "\u{1F608}",
  wink: "\u{1F609}",
  blush: "\u{1F60A}",
  yum: "\u{1F60B}",
  relieved: "\u{1F60C}",
  heart_eyes: "\u{1F60D}",
  sunglasses: "\u{1F60E}",
  smirk: "\u{1F60F}",
  neutral_face: "\u{1F610}",
  expressionless: "\u{1F611}",
  unamused: "\u{1F612}",
  sweat: "\u{1F613}",
  pensive: "\u{1F614}",
  confused: "\u{1F615}",
  confounded: "\u{1F616}",
  kissing: "\u{1F617}",
  kissing_heart: "\u{1F618}",
  kissing_smiling_eyes: "\u{1F619}",
  kissing_closed_eyes: "\u{1F61A}",
  stuck_out_tongue: "\u{1F61B}",
  stuck_out_tongue_winking_eye: "\u{1F61C}",
  stuck_out_tongue_closed_eyes: "\u{1F61D}",
  disappointed: "\u{1F61E}",
  worried: "\u{1F61F}",
  angry: "\u{1F620}",
  rage: "\u{1F621}",
  cry: "\u{1F622}",
  persevere: "\u{1F623}",
  triumph: "\u{1F624}",
  disappointed_relieved: "\u{1F625}",
  frowning: "\u{1F626}",
  anguished: "\u{1F627}",
  fearful: "\u{1F628}",
  weary: "\u{1F629}",
  sleepy: "\u{1F62A}",
  tired_face: "\u{1F62B}"
};
function titleize(value) {
  return value.replace(/[-_]+/g, " ").replace(/\b\w/g, (char) => char.toUpperCase());
}
function hyphenate(value) {
  return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
}
function evaluateExpression(expression) {
  const match = expression.trim().match(/^(.+?)\s*(===|==|!=|<=|<|>=|>|in|nin)\s*(.+)$/);
  if (!match)
    return false;
  const left = match[1].trim();
  const op = match[2];
  const right = match[3].trim();
  switch (op) {
    case "===":
      return left === right;
    case "==":
      return left == right;
    case "!=":
      return left != right;
    case "<=":
      return left <= right;
    case "<":
      return left < right;
    case ">=":
      return left >= right;
    case ">":
      return left > right;
    case "in":
      return right.split(",").map((part) => part.trim()).includes(left);
    case "nin":
      return !right.split(",").map((part) => part.trim()).includes(left);
    default:
      return false;
  }
}
function escapeHtml(input) {
  return input.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
var src_default = Remarkd;
export {
  Remarkd,
  src_default as default,
  parse
};
