export class Remarkd {
  parse(markdown: string, detectHeaders = false, options: RemarkdOptions = {}): string {
    return parse(markdown, detectHeaders, options);
  }

  static parse(markdown: string, detectHeaders = false, options: RemarkdOptions = {}): string {
    return parse(markdown, detectHeaders, options);
  }
}

type Attrs = { raw: string; pos: string[]; named: Record<string, string> };
type Reference = { code: string; content: string; num: number };
export type RemarkdOptions = { projectRoot?: string; partials?: Record<string, string> };

export function parse(markdown: string, detectHeaders = false, options: RemarkdOptions = {}): string {
  const parser = new Parser(markdown, detectHeaders, options);
  return parser.parse();
}

class Parser {
  private readonly lines: string[];
  private index = 0;
  private attrs: Record<string, string | boolean> = { plus: "+" };
  private references: Reference[] = [];
  private conditions: boolean[] = [];

  constructor(markdown: string, private readonly detectHeaders: boolean, private readonly options: RemarkdOptions = {}) {
    this.lines = preprocessPartials(preprocessMarkdown(markdown), options);
  }

  parse(): string {
    if (this.detectHeaders) {
      this.processDocumentHeader();
    }
    return this.wrapDocument(this.parseBlocks());
  }

  private processDocumentHeader(): void {
    // Skip leading blank lines and "// " comments before the title.
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

  private wrapDocument(html: string): string {
    const firstSection = html.indexOf('<div class="remarkd-section section--level1');
    if (firstSection === 0) return html;
    if (firstSection > 0) {
      const preamble = html.slice(0, firstSection);
      return `<div class="remarkd-section section--level0 section--${preamble ? "with-content" : "empty"}">${preamble}</div>${html.slice(firstSection)}`;
    }
    return `<div class="remarkd-section section--level0 section--${html ? "with-content" : "empty"}">${html}</div>`;
  }

  private parseBlocks(stop?: string, stopSectionLevel?: number): string {
    const parts: string[] = [];
    let pendingTitle: string | null = null;
    let pendingAttrs: Attrs | null = null;

    while (this.index < this.lines.length) {
      let line = this.replaceAttrs(this.lines[this.index] ?? "");

      const sectionMatch = line.match(/^(={2,6}) (.*)$/);
      if (sectionMatch && stopSectionLevel !== undefined && sectionMatch[1].length - 1 <= stopSectionLevel) {
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
        while (this.index < this.lines.length && this.lines[this.index] !== "////") this.index++;
        if (this.index < this.lines.length) this.index++;
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

  private parseBlock(line: string, title: string | null, attrs: Attrs | null): string {
    if (line.startsWith("_|_#")) return this.parseTabs();
    if (line.startsWith("_-_#")) return this.parseAccordion();
    if (line.startsWith("_|- ")) return this.parseSteps();
    if (line === "|===") return this.parseTable(title, attrs);
    if (line === "____") return this.parseDelimited(attrs);
    if (line === "```") return this.parseVerbatim("```", "code");
    if (line === "....") return this.parseVerbatim("....", "code");
    if (/^!{4,10}$/.test(line)) return this.parseVerbatim(line, "code");
    if (/^-{4,10}$/.test(line)) return this.parseListing(line);
    if (/^={4,10}$/.test(line)) return this.parseCompound(line, "example-block");
    if (/^\*{4,10}$/.test(line)) return this.parseCompound(line, "sidebar-block");
    if (/^!![\w-]+!!$/.test(line)) return this.parseId(line);
    if (/^include::[^\[]+\[.*]\s*$/.test(line)) return this.parseInclude(line);
    if (/^[^:]+:: .+/.test(line)) return this.parseDefinitions(line);
    if (/^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/.test(line)) return this.parseAdmonition(line);
    if (/^<(\d+|\d+\.\d+|\w|[^>])> /.test(line)) return this.parseCalloutBlock(line);
    if (/^(#{1,6}) /.test(line)) {
      const [, hashes, text] = line.match(/^(#{1,6}) (.*)$/) ?? [];
      this.index++;
      return `<h${hashes.length}>${this.inline(text)}</h${hashes.length}>`;
    }
    if (/^(={2,6}) /.test(line)) return this.parseSection(line, attrs);
    if (line.trim() === "<<<") {
      this.index++;
      return '<p><div style="break-after:page"></div></p>';
    }
    if (/^-{3}$/.test(line.trim())) {
      this.index++;
      return "<p><hr /></p>";
    }
    if (/^((\*|-){1,10}) /.test(line)) return this.parseUnorderedList();
    if (/^((\d*\.)+) /.test(line)) return this.parseOrderedList();
    if (/^{{/.test(line) || /{{ref\b/.test(line) || /{{reflist\b/.test(line)) {
      this.index++;
      return this.inline(line);
    }
    return this.parseParagraph(line, title, attrs);
  }

  private parseInclude(line: string): string {
    this.index++;
    const match = line.match(/^include::([^\[]+)\[.*]\s*$/);
    const filename = match?.[1]?.trim() ?? "";
    const content = readPartialFile(this.options, filename);
    if (content === null) return `<!-- unable to include ${filename}-->`;
    const parser = new Parser(content, false, this.options);
    (parser as unknown as { attrs: Record<string, string | boolean> }).attrs = this.attrs;
    (parser as unknown as { references: Reference[] }).references = this.references;
    const output = parser.parse();
    this.references = (parser as unknown as { references: Reference[] }).references;
    return output;
  }

  private parseParagraph(first: string, title: string | null, attrs: Attrs | null): string {
    const hardbreaks = !!attrs?.pos.includes("%hardbreaks");
    const lines = [first.trim().replace(/ \+$/, "")];
    this.index++;
    while (this.index < this.lines.length) {
      const line = this.replaceAttrs(this.lines[this.index] ?? "");
      if (line.trim() === "" || this.isBlockStart(line)) break;
      lines.push(line.trim() === "+" ? "" : line.trim().replace(/ \+$/, ""));
      this.index++;
    }
    const klass = attrs?.pos[0]?.startsWith(".") ? ` class="${attrs.pos[0].slice(1)}"` : "";
    const joiner = hardbreaks || first.endsWith(" +") || lines.includes("") ? "\n<br />\n" : "\n";
    const content = `${title ? `<div class="title">${title}</div>` : ""}${this.inline(lines.join(joiner))}`;
    return `<p${klass}>${content}</p>`;
  }

  private parseSection(line: string, attrs: Attrs | null): string {
    const [, marker, title] = line.match(/^(={2,6}) (.*)$/) ?? [];
    const level = marker.length - 1;
    const id = attrs?.pos.find((pos) => pos.startsWith("#"))?.slice(1) || hyphenate(title);
    this.index++;
    const content = this.parseBlocks(undefined, level);
    return `<div class="remarkd-section section--level${level} section--${content ? "with-content" : "empty"}" id="${id}"><h${level + 1}>${this.inline(title)}</h${level + 1}>${content}</div>`;
  }

  private parseVerbatim(closer: string, tag: "code"): string {
    this.index++;
    const lines: string[] = [];
    while (this.index < this.lines.length && this.lines[this.index] !== closer) {
      lines.push(escapeHtml(this.lines[this.index] ?? ""));
      this.index++;
    }
    if (this.index < this.lines.length) this.index++;
    return `<${tag}>${lines.join("\n")}</${tag}>`;
  }

  private parseListing(closer: string): string {
    this.index++;
    const lines: string[] = [];
    while (this.index < this.lines.length && this.lines[this.index] !== closer) {
      lines.push(escapeHtml(this.lines[this.index] ?? ""));
      this.index++;
    }
    if (this.index < this.lines.length) this.index++;
    return `<div class="listing-block"><div class="content">${lines.join("\n")}</div></div>`;
  }

  private parseCompound(closer: string, klass: string): string {
    this.index++;
    const inner = this.parseBlocks(closer);
    return `<div class="${klass}"><div class="content">${inner}</div></div>`;
  }

  private parseDelimited(attrs: Attrs | null): string {
    this.index++;
    const lines: string[] = [];
    while (this.index < this.lines.length && this.lines[this.index] !== "____") {
      lines.push(this.lines[this.index] ?? "");
      this.index++;
    }
    if (this.index < this.lines.length) this.index++;
    if (attrs?.pos[0] === "verse") {
      return `<pre class="verse-block">${lines.join("\n")}</pre>`;
    }
    return `<blockquote><p>${lines.map((part) => part.trim()).join(" ").trim()}</p></blockquote>`;
  }

  private parseDefinitions(first: string): string {
    const rows: Array<[string, string]> = [];
    let line = first;
    while (this.index < this.lines.length && /^([^:]+):: /.test(line)) {
      const [, term, definition] = line.match(/^([^:]+):: (.*)$/) ?? [];
      rows.push([term, definition]);
      this.index++;
      line = this.lines[this.index] ?? "";
    }
    return `<dl>${rows.map(([term, definition]) => `<dt>${term}</dt><dd>${definition}</dd>`).join("")}</dl>`;
  }

  private parseAdmonition(first: string): string {
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

  private parseCalloutBlock(line: string): string {
    const [, marker, text] = line.match(/^<([^>]+)> (.*)$/) ?? [];
    this.index++;
    return `<li class="callout" data-marker="${marker}">${this.inline(text)}</li>`;
  }

  private parseId(line: string): string {
    const id = line.slice(2, -2);
    this.index++;
    const lines: string[] = [];
    while (this.index < this.lines.length) {
      lines.push(this.lines[this.index] ?? "");
      this.index++;
    }
    return `<div id="${id}"><p>${this.inline(lines.join("\n"))}</p></div>`;
  }

  private parseUnorderedList(): string {
    const items: string[] = [];
    while (this.index < this.lines.length && /^((\*|-){1,10}) /.test(this.lines[this.index] ?? "")) {
      items.push((this.lines[this.index] ?? "").replace(/^((\*|-){1,10}) /, ""));
      this.index++;
    }
    return `<ul>${items.map((item) => `<li><p>${this.inline(item)}</p></li>`).join("\n")}</ul>`;
  }

  private parseOrderedList(): string {
    const items: string[] = [];
    while (this.index < this.lines.length && /^((\d*\.)+) /.test(this.lines[this.index] ?? "")) {
      items.push((this.lines[this.index] ?? "").replace(/^((\d*\.)+) /, ""));
      this.index++;
    }
    const nested = (i: number): string => {
      if (i >= items.length) return "";
      const child = nested(i + 1);
      return `<ol><li><p>${this.inline(items[i])}</p>${child ? `\n${child}` : ""}</li></ol>`;
    };
    return nested(0);
  }

  private parseTable(title: string | null, attrs: Attrs | null): string {
    this.index++;
    const rows: string[][] = [];
    let row: string[] = [];
    while (this.index < this.lines.length && this.lines[this.index] !== "|===") {
      const line = this.lines[this.index] ?? "";
      if (line === "") {
        if (row.length) rows.push(row);
        row = [];
      } else if (line.startsWith("|")) {
        row.push(...line.replace(/^\|/, "").split(/\s+\|/).map((cell) => this.inline(cell.trim())).filter(Boolean));
      }
      this.index++;
    }
    if (row.length) rows.push(row);
    if (this.index < this.lines.length) this.index++;
    const head = rows[0] ?? [];
    const body = rows.slice(1);
    const prosCons = isProsCons(attrs);
    const tableClasses = ["remarkd-table"];
    if (prosCons) {
      tableClasses.push("pros-cons-table");
      if (enabled(attrs, "background-colour", true) && enabled(attrs, "background-color", true)) tableClasses.push("pros-cons-table--background");
      if (enabled(attrs, "text-colour", true) && enabled(attrs, "text-color", true)) tableClasses.push("pros-cons-table--text-color");
      if (enabled(attrs, "header-icons", true)) tableClasses.push("pros-cons-table--header-icons");
    }
    const sideClass = (idx: number) => (idx === 0 ? "pros-cons-cell--con" : "pros-cons-cell--pro");
    const classAttr = (idx: number) => (prosCons ? ` class="pros-cons-cell ${sideClass(idx)}"` : "");
    const headerCell = (cell: string, idx: number) => {
      const content = prosCons && enabled(attrs, "header-icons", true) ? `${prosConsIcon(attrs, idx)} ${cell}` : cell;
      return `<th${classAttr(idx)}>${content}</th>`;
    };
    const bodyCell = (cell: string, idx: number) => `<td${classAttr(idx)}>${cell}</td>`;
    const html = `<table class="${tableClasses.join(" ")}"><thead><tr>${head.map(headerCell).join("")}</tr></thead><tbody>${body.map((cells) => `<tr>${cells.map(bodyCell).join("")}</tr>`).join("")}</tbody></table>`;
    if (!title) return html;
    const blockClass = prosCons ? "table-block pros-cons-block" : "table-block";
    return `<div class="${blockClass}"><div class="title">${title}</div>${html}</div>`;
  }

  private parseTabs(): string {
    const tabs: Array<{ id: string; name: string; content: string }> = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_|_#")) {
      const line = this.lines[this.index] ?? "";
      const [, id, rawAttrs] = line.match(/^_\|_#([\w-]+)\s*(.*)$/) ?? [];
      this.index++;
      const content: string[] = [];
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

  private parseAccordion(): string {
    const panels: Array<{ name: string; content: string }> = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_-_#")) {
      const line = this.lines[this.index] ?? "";
      const [, id, rawAttrs] = line.match(/^_-_#([\w-]+)\s*(.*)$/) ?? [];
      this.index++;
      const content: string[] = [];
      while (this.index < this.lines.length && !(this.lines[this.index] ?? "").startsWith("_-_#")) {
        content.push(this.lines[this.index] ?? "");
        this.index++;
      }
      panels.push({ name: parseAttrs(rawAttrs).named.name || titleize(id), content: parseFragment(content.join("\n"), this) });
    }
    return `<div class="accordion-container">${panels.map((panel) => `<button class="accordion">${panel.name}</button><div class="panel">${panel.content}</div>`).join("\n")}</div>`;
  }

  private parseSteps(): string {
    const steps: Array<{ title: string; img?: string; content: string }> = [];
    while (this.index < this.lines.length && (this.lines[this.index] ?? "").startsWith("_|- ")) {
      const line = this.lines[this.index] ?? "";
      const match = line.match(/^_\|- (.*?)(\[.*\])?\s*$/);
      this.index++;
      const content: string[] = [];
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

  private inline(input: string): string {
    let text = input;
    const passthrough: string[] = [];
    text = text.replace(/\bpass:\[([^\]]*)]/gs, (_m, raw) => {
      const token = `\u001aRMDPASS${passthrough.length}\u001a`;
      passthrough.push(raw);
      return token;
    }).replace(/\+\+\+(.+?)\+\+\+/gs, (_m, raw) => {
      const token = `\u001aRMDPASS${passthrough.length}\u001a`;
      passthrough.push(raw);
      return token;
    });
    text = text.replace(/{{([^}: ]+)(?::([^ }]+))?([^}]*)}}/g, (_m, type, key = "", raw = "") => this.renderObject(type, key, parseAttrs(raw)));
    text = text.replace(/"`([^`]+)`"/g, "&ldquo;$1&rdquo;");
    text = text.replace(/``([^`]+)``/g, (_m, code) => `<span class="monospace">${escapeHtml(code)}</span>`);
    text = text.replace(/`([^`\n]+)`/g, (_m, code) => `<span class="monospace">${escapeHtml(code)}</span>`);
    text = text.replace(/___(.+?)___/g, "<u>$1</u>");
    text = text.replace(/\(c\)|\(C\)/g, "©").replace(/\(r\)|\(R\)/g, "®").replace(/\(tm\)|\(TM\)/g, "™").replace(/\(p\)|\(P\)/g, "§").replace(/\(\+-\)/g, "±").replace(/-\.-/g, "&#8226;");
    text = text.replace(/:(\S+):/g, (match, raw) => emojiAliases[raw.replaceAll("-", "_")] || match);
    text = text.replace(/kbd:\[([^\]]+)]/g, "<kbd>$1</kbd>");
    text = text.replace(/[^#&]#([^#]+)#/g, '<mark class="highlight">$1</mark>');
    text = text.replace(/{(.*?)}\((.*?)\)/g, '<span class="tooltip" title="$2">$1</span>');
    text = text.replace(/!\[([^\]]*)]\((.*?)\s*"([^"]+)"\s*\)/g, '<img src="$2" alt="$1" title="$3"/>');
    text = text.replace(/!\[([^\]]*)]\(([^)]*)\)/g, (_m, alt, src) => alt ? `<img src="${src}" alt="${alt}"/>` : `<img src="${src}"/>`);
    text = text.replace(/image::([^\[]+)\[([^\]]*)]/g, (_m, src, raw) => {
      const [alt, width, height] = raw.split(",").map((part: string) => part.trim());
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
      if (value === " ") return '<input type="checkbox" readonly="readonly">';
      if (value === "x") return '<input type="checkbox" readonly="readonly" checked>';
      if (value === "_") return '<input type="checkbox">';
      return '<input type="checkbox" checked>';
    });
    text = text.replace(/(\[[^\]]+])##([^#]+)##/g, (_m, raw, value) => `<span class="${parseAttrs(raw).pos[0]}">${value}</span>`);
    text = text.replace(/<(\d+|\d+\.\d+)>/g, '<i class="conum" data-value="$1"></i>');
    return passthrough.reduce((out, raw, idx) => out.replaceAll(`\u001aRMDPASS${idx}\u001a`, raw), text);
  }

  private renderObject(type: string, key: string, attrs: Attrs): string {
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
        if (attrs.named.end) opts.unshift(`end=${attrs.named.end}`);
        if (attrs.named.start) opts.unshift(`start=${attrs.named.start}`);
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
        if (attrs.named.float) style += `float: ${attrs.named.float};`;
        const classes = attrs.pos.filter((pos) => pos.startsWith(".")).map((pos) => pos.slice(1));
        return `<img src="${attrs.named.src || ""}"${attrs.named.alt ? ` alt="${attrs.named.alt}"` : ""} style="${style}"${classes.length ? ` class="${classes.join(" ")}"` : ""} />`;
      }
      default:
        return "";
    }
  }

  private replaceAttrs(line: string): string {
    for (const [key, value] of Object.entries(this.attrs)) {
      line = line.replaceAll(`{${key}}`, String(value));
    }
    return line;
  }

  private addDocumentAttr(line: string): void {
    const match = line.match(/^:(!?[\w.-]+)(!)?:(.*)?$/);
    if (!match) return;
    this.attrs[match[1]] = match[3]?.trim() || !match[2];
  }

  private parseConditional(line: string): { handled: boolean; inline?: string } {
    const match = line.match(/^(end)?if(def|ndef|eval|nempty|empty|true|false)?::([^\[]*)\[([^]*)]$/);
    if (!match) return { handled: false };
    this.index++;
    if (match[1] === "end") {
      this.conditions.pop();
      return { handled: true };
    }
    const active = !this.conditions.includes(false);
    const valid = active && this.validateCondition(match[2] || "def", match[3], match[4]);
    if (match[2] !== "eval" && match[4]) {
      return { handled: true, inline: valid ? match[4] : undefined };
    }
    this.conditions.push(valid);
    return { handled: true };
  }

  private validateCondition(type: string, condition: string, expression: string): boolean {
    if (type === "eval") return evaluateExpression(expression);
    const groups = condition.split(",");
    return groups.some((group) => group.split("+").every((name) => {
      const value = this.attrs[name];
      if (type === "def") return value !== undefined;
      if (type === "ndef") return value === undefined;
      if (type === "true") return value === true || value === "true" || value === "1";
      if (type === "false") return value === false || value === "false" || value === "0";
      if (type === "empty") return value === undefined || value === "";
      if (type === "nempty") return value !== undefined && value !== "";
      return false;
    }));
  }

  private isBlockStart(line: string): boolean {
    return line === "```" || line === "____" || line === "|===" || /^[-=*.!]{4,10}$/.test(line)
      || /^#{1,6} /.test(line) || /^={2,6} /.test(line) || line.trim() === "<<<" || /^((\d*\.)+) /.test(line) || /^((\*|-){1,10}) /.test(line)
      || /^(end)?if(def|ndef|eval|nempty|empty|true|false)?::/.test(line)
      || /^!![\w-]+!!$/.test(line) || /^include::[^\[]+\[.*]\s*$/.test(line) || line.startsWith("_|_#") || line.startsWith("_-_#") || line.startsWith("_|- ")
      || /^[^:]+:: .+/.test(line) || /^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])/.test(line)
      || /^<(\d+|\d+\.\d+|\w|[^>])> /.test(line) || /^-{3}$/.test(line.trim());
  }
}

function preprocessMarkdown(markdown: string): string[] {
  const lines: string[] = [];
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

function parseFragment(markdown: string, parent: Parser): string {
  const parser = new Parser(
    markdown,
    false,
    (parent as unknown as { options: RemarkdOptions }).options,
  );
  (parser as unknown as { attrs: Record<string, string | boolean> }).attrs = (parent as unknown as { attrs: Record<string, string | boolean> }).attrs;
  return parser.parse().replace(/^<div class="remarkd-section section--level0 section--with-content">/, "").replace(/<\/div>$/, "");
}

function preprocessPartials(lines: string[], options: RemarkdOptions, depth = 0): string[] {
  if (depth > 10) return lines;
  const result: string[] = [];
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

function parsePartialTarget(raw: string): [string, Record<string, string>] {
  let filename = raw.trim();
  const attrs: Record<string, string> = {};
  const match = filename.match(/^(.*?)\[([^\]]*)]$/);
  if (match) {
    filename = match[1].trim();
    for (const item of match[2].split(",")) {
      const attr = item.trim();
      if (!attr) continue;
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

function partialEnabled(attrs: Record<string, string>, key: string): boolean {
  if (!(key in attrs)) return false;
  return !["0", "false", "no"].includes(attrs[key].toLowerCase());
}

function readPartialFile(options: RemarkdOptions, filename: string): string | null {
  if (options.partials && Object.prototype.hasOwnProperty.call(options.partials, filename)) {
    return options.partials[filename];
  }

  const proc = (globalThis as unknown as { process?: { cwd?: () => string; getBuiltinModule?: (name: string) => unknown } }).process;
  const fs = proc?.getBuiltinModule?.("node:fs") as { existsSync?: (path: string) => boolean; readFileSync?: (path: string, encoding: string) => string } | undefined;
  if (!fs?.existsSync || !fs?.readFileSync) return null;

  const path = joinPath(options.projectRoot || proc?.cwd?.() || "", filename);
  if (!fs.existsSync(path)) return null;
  return fs.readFileSync(path, "utf8");
}

function joinPath(root: string, filename: string): string {
  if (/^(\/|[A-Za-z]:[\\/])/.test(filename) || !root) return filename;
  return `${root.replace(/[\\/]+$/, "")}/${filename.replace(/^[\\/]+/, "")}`;
}

function parseAttrs(raw: string): Attrs {
  const clean = raw.trim().replace(/^\[/, "").replace(/]$/, "");
  const pos: string[] = [];
  const named: Record<string, string> = {};
  const re = /(^|[\s,]+)([^, =}]+)(=(("([^"]*)")|([^\s,}]*)))?/g;
  let match: RegExpExecArray | null;
  while ((match = re.exec(clean))) {
    const key = match[2];
    const value = match[6] || match[7] || "";
    pos.push(key);
    named[key] = value;
  }
  return { raw: clean, pos, named };
}

function isProsCons(attrs: Attrs | null): boolean {
  return !!attrs && (attrs.pos[0] === "pros-cons" || attrs.pos[0] === "proscons" || "pros-cons" in attrs.named || "proscons" in attrs.named);
}

function enabled(attrs: Attrs | null, key: string, fallback: boolean): boolean {
  if (!attrs || !(key in attrs.named)) return fallback;
  return !["0", "false", "no", "off"].includes((attrs.named[key] || "").toLowerCase());
}

function prosConsIcon(attrs: Attrs | null, index: number): string {
  const icon = index === 0 ? attrs?.named["con-icon"] || "❌" : attrs?.named["pro-icon"] || "✅";
  return `<span class="pros-cons-icon">${escapeHtml(icon)}</span>`;
}

const emojiAliases: Record<string, string> = {
  "+1": "👍",
  thumbsup: "👍",
  "-1": "👎",
  thumbsdown: "👎",
  eyes: "👀",
  grinning: "😀",
  grin: "😁",
  joy: "😂",
  smiley: "😃",
  smile: "😄",
  sweat_smile: "😅",
  satisfied: "😆",
  laughing: "😆",
  innocent: "😇",
  smiling_imp: "😈",
  wink: "😉",
  blush: "😊",
  yum: "😋",
  relieved: "😌",
  heart_eyes: "😍",
  sunglasses: "😎",
  smirk: "😏",
  neutral_face: "😐",
  expressionless: "😑",
  unamused: "😒",
  sweat: "😓",
  pensive: "😔",
  confused: "😕",
  confounded: "😖",
  kissing: "😗",
  kissing_heart: "😘",
  kissing_smiling_eyes: "😙",
  kissing_closed_eyes: "😚",
  stuck_out_tongue: "😛",
  stuck_out_tongue_winking_eye: "😜",
  stuck_out_tongue_closed_eyes: "😝",
  disappointed: "😞",
  worried: "😟",
  angry: "😠",
  rage: "😡",
  cry: "😢",
  persevere: "😣",
  triumph: "😤",
  disappointed_relieved: "😥",
  frowning: "😦",
  anguished: "😧",
  fearful: "😨",
  weary: "😩",
  sleepy: "😪",
  tired_face: "😫",
};

function titleize(value: string): string {
  return value.replace(/[-_]+/g, " ").replace(/\b\w/g, (char) => char.toUpperCase());
}

function hyphenate(value: string): string {
  return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
}

function evaluateExpression(expression: string): boolean {
  const match = expression.trim().match(/^(.+?)\s*(===|==|!=|<=|<|>=|>|in|nin)\s*(.+)$/);
  if (!match) return false;
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

function escapeHtml(input: string): string {
  return input.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

export default Remarkd;
