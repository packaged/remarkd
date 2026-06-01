package remarkd

import (
	"html"
	"os"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"
)

type Remarkd struct{}

type Options struct {
	ProjectRoot string
}

type attrs struct {
	pos   []string
	named map[string]string
}

type reference struct {
	num     int
	code    string
	content string
}

type parser struct {
	lines       []string
	index       int
	attrs       map[string]string
	references  []reference
	conditions  []bool
	projectRoot string
}

func New() *Remarkd {
	return &Remarkd{}
}

func (r *Remarkd) Parse(markdown string, detectHeaders ...bool) string {
	return r.ParseWithOptions(markdown, Options{}, detectHeaders...)
}

func (r *Remarkd) ParseWithOptions(markdown string, options Options, detectHeaders ...bool) string {
	p := newParser(markdown, options)
	if len(detectHeaders) > 0 && detectHeaders[0] {
		p.processDocumentHeader()
	}
	return p.wrapDocument(p.parseBlocks("", 0))
}

func Parse(markdown string, detectHeaders ...bool) string {
	return New().Parse(markdown, detectHeaders...)
}

func ParseWithOptions(markdown string, options Options, detectHeaders ...bool) string {
	return New().ParseWithOptions(markdown, options, detectHeaders...)
}

func (p *parser) wrapDocument(body string) string {
	firstSection := strings.Index(body, `<div class="remarkd-section section--level1`)
	if firstSection == 0 {
		return body
	}
	if firstSection > 0 {
		preamble := body[:firstSection]
		state := "empty"
		if preamble != "" {
			state = "with-content"
		}
		return `<div class="remarkd-section section--level0 section--` + state + `">` + preamble + `</div>` + body[firstSection:]
	}
	state := "empty"
	if body != "" {
		state = "with-content"
	}
	return `<div class="remarkd-section section--level0 section--` + state + `">` + body + `</div>`
}

func newParser(markdown string, options ...Options) *parser {
	parserOptions := Options{}
	if len(options) > 0 {
		parserOptions = options[0]
	}
	lines := preprocessMarkdown(markdown)
	return &parser{
		lines:       preprocessPartials(lines, parserOptions.ProjectRoot, 0),
		attrs:       map[string]string{"plus": "+"},
		projectRoot: parserOptions.ProjectRoot,
	}
}

func preprocessMarkdown(markdown string) []string {
	markdown = strings.TrimRight(strings.ReplaceAll(markdown, "\r\n", "\n"), "\n")
	rawLines := []string{}
	if markdown != "" {
		rawLines = strings.Split(markdown, "\n")
	}
	lines := []string{}
	re := regexp.MustCompile(`{!(.*)!}`)
	for _, raw := range rawLines {
		line := re.ReplaceAllString(raw, "$1")
		if len(lines) > 0 && strings.HasSuffix(lines[len(lines)-1], `\`) {
			lines[len(lines)-1] = strings.TrimRight(strings.TrimSuffix(lines[len(lines)-1], `\`), " ") + "\n" + line
		} else {
			lines = append(lines, line)
		}
	}
	return lines
}

func preprocessPartials(lines []string, projectRoot string, depth int) []string {
	if depth > 10 {
		return lines
	}
	out := []string{}
	for _, line := range lines {
		m := regexp.MustCompile(`^t::partial::(.*)$`).FindStringSubmatch(line)
		if len(m) == 0 {
			out = append(out, line)
			continue
		}

		filename, attr := parsePartialTarget(m[1])
		content, ok := readPartialFile(projectRoot, filename)
		if !ok {
			out = append(out, "File not found: "+filename)
			continue
		}

		partial := preprocessMarkdown(content)
		if partialEnabled(attr, "strip-title") && len(partial) > 0 && strings.HasPrefix(partial[0], "=") {
			partial = partial[1:]
		}
		for len(partial) > 0 && strings.TrimSpace(partial[len(partial)-1]) == "" {
			partial = partial[:len(partial)-1]
		}
		if dropLast, err := strconv.Atoi(attr["drop-last"]); err == nil && dropLast > 0 {
			if dropLast > len(partial) {
				partial = []string{}
			} else {
				partial = partial[:len(partial)-dropLast]
			}
		}
		out = append(out, preprocessPartials(partial, projectRoot, depth+1)...)
	}
	return out
}

func parsePartialTarget(raw string) (string, map[string]string) {
	filename := strings.TrimSpace(raw)
	attr := map[string]string{}
	m := regexp.MustCompile(`^(.*?)\[([^]]*)]$`).FindStringSubmatch(filename)
	if len(m) > 0 {
		filename = strings.TrimSpace(m[1])
		for _, item := range strings.Split(m[2], ",") {
			item = strings.TrimSpace(item)
			if item == "" {
				continue
			}
			parts := strings.SplitN(item, "=", 2)
			if len(parts) == 1 {
				attr[item] = "true"
			} else {
				attr[strings.TrimSpace(parts[0])] = strings.Trim(strings.TrimSpace(parts[1]), `"'`)
			}
		}
	}
	return filename, attr
}

func partialEnabled(attr map[string]string, key string) bool {
	value, ok := attr[key]
	if !ok {
		return false
	}
	value = strings.ToLower(value)
	return value != "0" && value != "false" && value != "no"
}

func readPartialFile(projectRoot, filename string) (string, bool) {
	path := filename
	if projectRoot != "" && !filepath.IsAbs(filename) {
		path = filepath.Join(projectRoot, filename)
	}
	content, err := os.ReadFile(path)
	if err != nil {
		return "", false
	}
	return string(content), true
}

func (p *parser) processDocumentHeader() {
	// Skip leading blank lines and "// " comments before the title.
	for p.index < len(p.lines) {
		line := p.lines[p.index]
		if strings.TrimSpace(line) == "" || strings.HasPrefix(line, "// ") {
			p.index++
			continue
		}
		break
	}
	if p.index < len(p.lines) && strings.HasPrefix(p.lines[p.index], "= ") {
		p.index++
		metadataLines := 0
		for p.index < len(p.lines) {
			line := p.lines[p.index]
			if strings.TrimSpace(line) == "" {
				p.index++
				break
			}
			if strings.HasPrefix(line, "// ") {
				p.index++
				continue
			}
			if regexp.MustCompile(`^:!?[\w.-]+!?:`).MatchString(line) {
				p.addDocumentAttr(line)
				p.index++
				continue
			}
			if metadataLines < 2 {
				metadataLines++
				p.index++
				continue
			}
			break
		}
	}
}

func (p *parser) parseBlocks(stop string, stopSectionLevel int) string {
	parts := []string{}
	var pendingTitle string
	var pendingAttrs *attrs
	for p.index < len(p.lines) {
		line := p.replaceAttrs(p.lines[p.index])
		if m := regexp.MustCompile(`^(={2,6}) (.*)$`).FindStringSubmatch(line); len(m) > 0 && stopSectionLevel > 0 && len(m[1])-1 <= stopSectionLevel {
			break
		}
		if stop != "" && line == stop {
			p.index++
			break
		}
		handled, inline := p.parseConditional(line)
		if handled {
			if inline == "" {
				continue
			}
			line = p.replaceAttrs(inline)
		}
		if p.conditionExcluded() {
			p.index++
			continue
		}
		if strings.TrimSpace(line) == "" {
			p.index++
			continue
		}
		if regexp.MustCompile(`^:!?[\w.-]+!?:`).MatchString(line) {
			p.addDocumentAttr(line)
			p.index++
			continue
		}
		if strings.HasPrefix(line, "// ") {
			p.index++
			continue
		}
		if line == "////" {
			p.index++
			for p.index < len(p.lines) && p.lines[p.index] != "////" {
				p.index++
			}
			if p.index < len(p.lines) {
				p.index++
			}
			continue
		}
		if strings.HasPrefix(line, ".") && !strings.HasPrefix(line, "..") && line != "." {
			pendingTitle = line[1:]
			p.index++
			continue
		}
		if regexp.MustCompile(`^\[[^\]]*]$`).MatchString(line) {
			attr := parseAttrs(line)
			pendingAttrs = &attr
			p.index++
			continue
		}
		parts = append(parts, p.parseBlock(line, pendingTitle, pendingAttrs))
		pendingTitle = ""
		pendingAttrs = nil
	}
	return strings.Join(parts, "")
}

func (p *parser) parseBlock(line, title string, attr *attrs) string {
	switch {
	case strings.HasPrefix(line, "_|_#"):
		return p.parseTabs()
	case strings.HasPrefix(line, "_-_#"):
		return p.parseAccordion()
	case strings.HasPrefix(line, "_|- "):
		return p.parseSteps()
	case line == "|===":
		return p.parseTable(title, attr)
	case line == "____":
		return p.parseDelimited(attr)
	case line == "```":
		return p.parseVerbatim("```", "code")
	case line == "....":
		return p.parseVerbatim("....", "code")
	case regexp.MustCompile(`^!{4,10}$`).MatchString(line):
		return p.parseVerbatim(line, "code")
	case regexp.MustCompile(`^-{4,10}$`).MatchString(line):
		return p.parseListing(line)
	case regexp.MustCompile(`^={4,10}$`).MatchString(line):
		return p.parseCompound(line, "example-block")
	case regexp.MustCompile(`^\*{4,10}$`).MatchString(line):
		return p.parseCompound(line, "sidebar-block")
	case regexp.MustCompile(`^!![\w-]+!!$`).MatchString(line):
		return p.parseID(line)
	case regexp.MustCompile(`^include::[^\[]+\[.*]\s*$`).MatchString(line):
		return p.parseInclude(line)
	case regexp.MustCompile(`^[^:]+:: .+`).MatchString(line):
		return p.parseDefinitions(line)
	case regexp.MustCompile(`^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])`).MatchString(line):
		return p.parseAdmonition(line)
	case regexp.MustCompile(`^<(\d+|\d+\.\d+|\w|[^>])> `).MatchString(line):
		return p.parseCalloutBlock(line)
	case regexp.MustCompile(`^#{1,6} `).MatchString(line):
		level := strings.IndexFunc(line, func(r rune) bool { return r != '#' })
		p.index++
		lvl := itoa(level)
		return "<h" + lvl + ">" + p.inline(strings.TrimSpace(line[level:])) + "</h" + lvl + ">"
	case regexp.MustCompile(`^={2,6} `).MatchString(line):
		return p.parseSection(line, attr)
	case strings.TrimSpace(line) == "<<<":
		p.index++
		return `<p><div style="break-after:page"></div></p>`
	case strings.TrimSpace(line) == "---":
		p.index++
		return "<p><hr /></p>"
	case regexp.MustCompile(`^((\*|-){1,10}) `).MatchString(line):
		return p.parseUnorderedList()
	case regexp.MustCompile(`^((\d*\.)+) `).MatchString(line):
		return p.parseOrderedList()
	case strings.HasPrefix(line, "{{") || strings.Contains(line, "{{ref") || strings.Contains(line, "{{reflist"):
		p.index++
		return p.inline(line)
	default:
		return p.parseParagraph(line, title, attr)
	}
}

func (p *parser) parseInclude(line string) string {
	p.index++
	match := regexp.MustCompile(`^include::([^\[]+)\[.*]\s*$`).FindStringSubmatch(line)
	if len(match) == 0 {
		return ""
	}
	filename := strings.TrimSpace(match[1])
	content, ok := readPartialFile(p.projectRoot, filename)
	if !ok {
		return `<!-- unable to include ` + filename + `-->`
	}
	child := newParser(content, Options{ProjectRoot: p.projectRoot})
	child.attrs = p.attrs
	child.references = p.references
	out := child.wrapDocument(child.parseBlocks("", 0))
	p.references = child.references
	return out
}

func (p *parser) parseParagraph(first, title string, attr *attrs) string {
	hardbreaks := false
	if attr != nil {
		for _, pos := range attr.pos {
			if pos == "%hardbreaks" {
				hardbreaks = true
			}
		}
	}
	explicitBreaks := strings.HasSuffix(first, " +")
	lines := []string{strings.TrimSuffix(strings.TrimSpace(first), " +")}
	p.index++
	for p.index < len(p.lines) {
		line := p.replaceAttrs(p.lines[p.index])
		if strings.TrimSpace(line) == "" || p.isBlockStart(line) {
			break
		}
		trimmed := strings.TrimSpace(line)
		if trimmed == "+" {
			explicitBreaks = true
			lines = append(lines, "")
		} else {
			if strings.HasSuffix(trimmed, " +") {
				explicitBreaks = true
			}
			lines = append(lines, strings.TrimSuffix(trimmed, " +"))
		}
		p.index++
	}
	classAttr := ""
	if attr != nil && len(attr.pos) > 0 && strings.HasPrefix(attr.pos[0], ".") {
		classAttr = ` class="` + attr.pos[0][1:] + `"`
	}
	content := ""
	if title != "" {
		content += `<div class="title">` + title + `</div>`
	}
	joiner := "\n"
	if hardbreaks || explicitBreaks {
		joiner = "\n<br />\n"
	}
	content += p.inline(strings.Join(lines, joiner))
	return "<p" + classAttr + ">" + content + "</p>"
}

func (p *parser) parseSection(line string, attr *attrs) string {
	m := regexp.MustCompile(`^(={2,6}) (.*)$`).FindStringSubmatch(line)
	level := len(m[1]) - 1
	title := m[2]
	id := hyphenate(title)
	if attr != nil {
		for _, pos := range attr.pos {
			if strings.HasPrefix(pos, "#") {
				id = pos[1:]
				break
			}
		}
	}
	p.index++
	content := p.parseBlocks("", level)
	state := "empty"
	if content != "" {
		state = "with-content"
	}
	headingLevel := itoa(level + 1)
	return `<div class="remarkd-section section--level` + itoa(level) + ` section--` + state + `" id="` + id + `"><h` + headingLevel + `>` + p.inline(title) + `</h` + headingLevel + `>` + content + `</div>`
}

func (p *parser) parseVerbatim(closer, tag string) string {
	p.index++
	lines := []string{}
	for p.index < len(p.lines) && p.lines[p.index] != closer {
		lines = append(lines, html.EscapeString(p.lines[p.index]))
		p.index++
	}
	if p.index < len(p.lines) {
		p.index++
	}
	return "<" + tag + ">" + strings.Join(lines, "\n") + "</" + tag + ">"
}

func (p *parser) parseListing(closer string) string {
	p.index++
	lines := []string{}
	for p.index < len(p.lines) && p.lines[p.index] != closer {
		lines = append(lines, html.EscapeString(p.lines[p.index]))
		p.index++
	}
	if p.index < len(p.lines) {
		p.index++
	}
	return `<div class="listing-block"><div class="content">` + strings.Join(lines, "\n") + `</div></div>`
}

func (p *parser) parseCompound(closer, class string) string {
	p.index++
	return `<div class="` + class + `"><div class="content">` + p.parseBlocks(closer, 0) + `</div></div>`
}

func (p *parser) parseDelimited(attr *attrs) string {
	p.index++
	lines := []string{}
	for p.index < len(p.lines) && p.lines[p.index] != "____" {
		lines = append(lines, p.lines[p.index])
		p.index++
	}
	if p.index < len(p.lines) {
		p.index++
	}
	if attr != nil && len(attr.pos) > 0 && attr.pos[0] == "verse" {
		return `<pre class="verse-block">` + strings.Join(lines, "\n") + `</pre>`
	}
	for i := range lines {
		lines[i] = strings.TrimSpace(lines[i])
	}
	return "<blockquote><p>" + strings.TrimSpace(strings.Join(lines, " ")) + "</p></blockquote>"
}

func (p *parser) parseDefinitions(first string) string {
	rows := [][2]string{}
	line := first
	for p.index < len(p.lines) && regexp.MustCompile(`^([^:]+):: `).MatchString(line) {
		m := regexp.MustCompile(`^([^:]+):: (.*)$`).FindStringSubmatch(line)
		rows = append(rows, [2]string{m[1], m[2]})
		p.index++
		if p.index < len(p.lines) {
			line = p.lines[p.index]
		}
	}
	var b strings.Builder
	b.WriteString("<dl>")
	for _, row := range rows {
		b.WriteString("<dt>" + row[0] + "</dt><dd>" + row[1] + "</dd>")
	}
	b.WriteString("</dl>")
	return b.String()
}

func (p *parser) parseAdmonition(first string) string {
	re := regexp.MustCompile(`^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])\s?(.*)$`)
	m := re.FindStringSubmatch(first)
	level, style := m[1], m[2]
	lines := []string{m[3]}
	p.index++
	for p.index < len(p.lines) && strings.TrimSpace(p.lines[p.index]) != "" {
		lines = append(lines, p.lines[p.index])
		p.index++
	}
	caption := ""
	if style == ":" {
		caption = `<strong class="hint-caption">` + level + `: </strong> `
	}
	for i := range lines {
		lines[i] = p.inline(lines[i])
	}
	return `<div class="hint-` + strings.ToLower(level) + `">` + caption + strings.Join(lines, "\n") + `</div>`
}

func (p *parser) parseCalloutBlock(line string) string {
	m := regexp.MustCompile(`^<([^>]+)> (.*)$`).FindStringSubmatch(line)
	p.index++
	return `<li class="callout" data-marker="` + m[1] + `">` + p.inline(m[2]) + `</li>`
}

func (p *parser) parseID(line string) string {
	id := strings.TrimSuffix(strings.TrimPrefix(line, "!!"), "!!")
	p.index++
	lines := []string{}
	for p.index < len(p.lines) {
		lines = append(lines, p.lines[p.index])
		p.index++
	}
	return `<div id="` + id + `"><p>` + p.inline(strings.Join(lines, "\n")) + `</p></div>`
}

func (p *parser) parseUnorderedList() string {
	items := []string{}
	re := regexp.MustCompile(`^((\*|-){1,10}) `)
	for p.index < len(p.lines) && re.MatchString(p.lines[p.index]) {
		items = append(items, re.ReplaceAllString(p.lines[p.index], ""))
		p.index++
	}
	out := []string{}
	for _, item := range items {
		out = append(out, "<li><p>"+p.inline(item)+"</p></li>")
	}
	return "<ul>" + strings.Join(out, "\n") + "</ul>"
}

func (p *parser) parseOrderedList() string {
	items := []string{}
	re := regexp.MustCompile(`^((\d*\.)+) `)
	for p.index < len(p.lines) && re.MatchString(p.lines[p.index]) {
		items = append(items, re.ReplaceAllString(p.lines[p.index], ""))
		p.index++
	}
	var nested func(int) string
	nested = func(i int) string {
		if i >= len(items) {
			return ""
		}
		child := nested(i + 1)
		sep := ""
		if child != "" {
			sep = "\n"
		}
		return "<ol><li><p>" + p.inline(items[i]) + "</p>" + sep + child + "</li></ol>"
	}
	return nested(0)
}

func (p *parser) parseTable(title string, attr *attrs) string {
	p.index++
	rows := [][]string{}
	row := []string{}
	for p.index < len(p.lines) && p.lines[p.index] != "|===" {
		line := p.lines[p.index]
		if line == "" {
			if len(row) > 0 {
				rows = append(rows, row)
			}
			row = []string{}
		} else if strings.HasPrefix(line, "|") {
			for _, cell := range regexp.MustCompile(`\s+\|`).Split(strings.TrimPrefix(line, "|"), -1) {
				cell = strings.TrimSpace(cell)
				if cell != "" {
					row = append(row, p.inline(cell))
				}
			}
		}
		p.index++
	}
	if len(row) > 0 {
		rows = append(rows, row)
	}
	if p.index < len(p.lines) {
		p.index++
	}
	head := ""
	prosCons := isProsCons(attr)
	if len(rows) > 0 {
		for idx, cell := range rows[0] {
			content := cell
			if prosCons && enabled(attr, "header-icons", true) {
				content = prosConsIcon(attr, idx) + " " + cell
			}
			head += "<th" + prosConsCellClass(prosCons, idx) + ">" + content + "</th>"
		}
	}
	body := ""
	for _, row := range rows[1:] {
		body += "<tr>"
		for idx, cell := range row {
			body += "<td" + prosConsCellClass(prosCons, idx) + ">" + cell + "</td>"
		}
		body += "</tr>"
	}
	tableClass := "remarkd-table"
	if prosCons {
		tableClass += " pros-cons-table"
		if enabled(attr, "background-colour", true) && enabled(attr, "background-color", true) {
			tableClass += " pros-cons-table--background"
		}
		if enabled(attr, "text-colour", true) && enabled(attr, "text-color", true) {
			tableClass += " pros-cons-table--text-color"
		}
		if enabled(attr, "header-icons", true) {
			tableClass += " pros-cons-table--header-icons"
		}
	}
	table := `<table class="` + tableClass + `"><thead><tr>` + head + `</tr></thead><tbody>` + body + `</tbody></table>`
	if title != "" {
		blockClass := "table-block"
		if prosCons {
			blockClass += " pros-cons-block"
		}
		return `<div class="` + blockClass + `"><div class="title">` + title + `</div>` + table + `</div>`
	}
	return table
}

func (p *parser) parseTabs() string {
	type tab struct{ id, name, content string }
	tabs := []tab{}
	for p.index < len(p.lines) && strings.HasPrefix(p.lines[p.index], "_|_#") {
		m := regexp.MustCompile(`^_\|_#([\w-]+)\s*(.*)$`).FindStringSubmatch(p.lines[p.index])
		p.index++
		content := []string{}
		for p.index < len(p.lines) && !strings.HasPrefix(p.lines[p.index], "_|_#") {
			content = append(content, p.lines[p.index])
			p.index++
		}
		attr := parseAttrs(m[2])
		name := attr.named["name"]
		if name == "" {
			name = titleize(m[1])
		}
		tabs = append(tabs, tab{m[1], name, p.parseFragment(strings.Join(content, "\n"))})
	}
	headers := []string{}
	bodies := []string{}
	for i, t := range tabs {
		class := ""
		if i == 0 {
			class = ` class="active"`
		}
		headers = append(headers, `<li><a href="#" data-tab-focus-key="`+t.id+`"`+class+`>`+t.name+`</a></li>`)
		bodies = append(bodies, `<div class="tab" data-tab-key="`+t.id+`"><div class="content">`+t.content+`</div></div>`)
	}
	return `<div class="tab-group"><ul class="tab-header">` + strings.Join(headers, "") + `</ul><div class="tabs">` + strings.Join(bodies, "\n") + `</div></div>`
}

func (p *parser) parseAccordion() string {
	type panel struct{ name, content string }
	panels := []panel{}
	for p.index < len(p.lines) && strings.HasPrefix(p.lines[p.index], "_-_#") {
		m := regexp.MustCompile(`^_-_#([\w-]+)\s*(.*)$`).FindStringSubmatch(p.lines[p.index])
		p.index++
		content := []string{}
		for p.index < len(p.lines) && !strings.HasPrefix(p.lines[p.index], "_-_#") {
			content = append(content, p.lines[p.index])
			p.index++
		}
		attr := parseAttrs(m[2])
		name := attr.named["name"]
		if name == "" {
			name = titleize(m[1])
		}
		panels = append(panels, panel{name, p.parseFragment(strings.Join(content, "\n"))})
	}
	out := []string{}
	for _, panel := range panels {
		out = append(out, `<button class="accordion">`+panel.name+`</button><div class="panel">`+panel.content+`</div>`)
	}
	return `<div class="accordion-container">` + strings.Join(out, "\n") + `</div>`
}

func (p *parser) parseSteps() string {
	type step struct{ title, image, content string }
	steps := []step{}
	for p.index < len(p.lines) && strings.HasPrefix(p.lines[p.index], "_|- ") {
		m := regexp.MustCompile(`^_\|- (.*?)(\[.*\])?\s*$`).FindStringSubmatch(p.lines[p.index])
		p.index++
		content := []string{}
		for p.index < len(p.lines) && !strings.HasPrefix(p.lines[p.index], "_|- ") {
			content = append(content, p.lines[p.index])
			p.index++
		}
		attr := parseAttrs("")
		if len(m) > 2 {
			attr = parseAttrs(m[2])
		}
		steps = append(steps, step{m[1], attr.named["img"], p.parseFragment(strings.Join(content, "\n"))})
	}
	out := []string{}
	for _, s := range steps {
		img := ""
		if s.image != "" {
			img = `<div class="step-image"><img src="` + s.image + `" alt="" /></div>`
		}
		out = append(out, `<div class="step"><div class="step-content"><h3>`+s.title+`</h3>`+s.content+`</div>`+img+`</div>`)
	}
	return `<div class="steps-container"><div class="content">` + strings.Join(out, "\n") + `</div></div>`
}

func (p *parser) parseFragment(markdown string) string {
	child := newParser(markdown, Options{ProjectRoot: p.projectRoot})
	child.attrs = p.attrs
	child.references = p.references
	out := child.parseBlocks("", 0)
	p.references = child.references
	return out
}

func (p *parser) inline(input string) string {
	passes := []string{}
	text := regexp.MustCompile(`\bpass:\[([^\]]*)]`).ReplaceAllStringFunc(input, func(m string) string {
		raw := regexp.MustCompile(`\bpass:\[([^\]]*)]`).FindStringSubmatch(m)[1]
		token := "\x1aRMDPASS" + itoa(len(passes)) + "\x1a"
		passes = append(passes, raw)
		return token
	})
	text = regexp.MustCompile(`\+\+\+(.+?)\+\+\+`).ReplaceAllStringFunc(text, func(m string) string {
		raw := regexp.MustCompile(`\+\+\+(.+?)\+\+\+`).FindStringSubmatch(m)[1]
		token := "\x1aRMDPASS" + itoa(len(passes)) + "\x1a"
		passes = append(passes, raw)
		return token
	})
	text = regexp.MustCompile(`{{([^}: ]+)(:([^ }]+))?([^}]*)}}`).ReplaceAllStringFunc(text, func(m string) string {
		match := regexp.MustCompile(`{{([^}: ]+)(:([^ }]+))?([^}]*)}}`).FindStringSubmatch(m)
		return p.renderObject(match[1], match[3], parseAttrs(match[4]))
	})
	repls := [][2]string{
		{`"` + "`" + `([^` + "`" + `]+)` + "`" + `"`, `&ldquo;$1&rdquo;`},
		{"``([^`]+)``", `<span class="monospace">$1</span>`},
		{"`([^`\n]+)`", `<span class="monospace">$1</span>`},
		{`___(.+?)___`, `<u>$1</u>`},
		{`kbd:\[([^\]]+)]`, `<kbd>$1</kbd>`},
		{`[^#&]#([^#]+)#`, `<mark class="highlight">$1</mark>`},
		{`{(.*?)}\((.*?)\)`, `<span class="tooltip" title="$2">$1</span>`},
		{`!\[([^\]]*)]\((.*?)\s*"([^"]+)"\s*\)`, `<img src="$2" alt="$1" title="$3"/>`},
		{`!\[([^\]]*)]\(([^)]*)\)`, `<img src="$2" alt="$1"/>`},
		{`\[([^\]]*)]\(([^)]*)\)`, `<a href="$2">$1</a>`},
		{`footnote:\[([^\]]+)]`, `<sup class="footnote">$1</sup>`},
		{`\*\*([^*]+?)\*\*`, `<strong>$1</strong>`},
		{`__([^_]+?)__`, `<em>$1</em>`},
		{`([^\w])\*([^*]+)\*`, `$1<strong>$2</strong>`},
		{`(\s|^)_([^_]+)_`, `$1<em>$2</em>`},
		{`~~(.+?)~~`, `<del>$1</del>`},
		{`~([^~]+?)~`, `<sub>$1</sub>`},
		{`\^([^^]+?)\^`, `<sup>$1</sup>`},
		{`<(\d+|\d+\.\d+)>`, `<i class="conum" data-value="$1"></i>`},
	}
	text = regexp.MustCompile(`image::([^\[]+)\[([^\]]*)]`).ReplaceAllStringFunc(text, func(m string) string {
		match := regexp.MustCompile(`image::([^\[]+)\[([^\]]*)]`).FindStringSubmatch(m)
		parts := strings.Split(match[2], ",")
		for i := range parts {
			parts[i] = strings.TrimSpace(parts[i])
		}
		out := `<img class="block" src="` + match[1] + `"`
		if len(parts) > 0 && parts[0] != "" {
			out += ` alt="` + parts[0] + `"`
		}
		if len(parts) > 1 && parts[1] != "" {
			out += ` width="` + parts[1] + `"`
		}
		if len(parts) > 2 && parts[2] != "" {
			out += ` height="` + parts[2] + `"`
		}
		return out + `/>`
	})
	text = regexp.MustCompile(`([^="(])((http|ftp|https|mailto)://[\w_-]+(?:(?:\.[\w_-]+)+)[\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])(\[([^\]\n]+)])?`).ReplaceAllString(text, `$1<a href="$2">$5</a>`)
	for _, repl := range repls {
		text = regexp.MustCompile(repl[0]).ReplaceAllString(text, repl[1])
	}
	text = regexp.MustCompile(`<<([\w\-_]+)(,([^>]+))?>>`).ReplaceAllStringFunc(text, func(m string) string {
		match := regexp.MustCompile(`<<([\w\-_]+)(,([^>]+))?>>`).FindStringSubmatch(m)
		id := strings.ReplaceAll(strings.TrimPrefix(match[1], "_"), "_", "-")
		label := match[3]
		if label == "" {
			label = titleize(id)
		}
		return `<a href="#` + id + `">` + label + `</a>`
	})
	text = strings.NewReplacer("(c)", "©", "(C)", "©", "(r)", "®", "(R)", "®", "(tm)", "™", "(TM)", "™", "(p)", "§", "(P)", "§", "(+-)", "±", "-.-", "&#8226;").Replace(text)
	text = regexp.MustCompile(`:(\S+):`).ReplaceAllStringFunc(text, func(m string) string {
		key := strings.ReplaceAll(strings.Trim(m, ":"), "-", "_")
		emoji := map[string]string{
			"+1": "👍", "thumbsup": "👍", "-1": "👎", "thumbsdown": "👎", "eyes": "👀", "grinning": "😀", "grin": "😁", "joy": "😂", "smiley": "😃", "smile": "😄",
			"sweat_smile": "😅", "satisfied": "😆", "laughing": "😆", "innocent": "😇", "smiling_imp": "😈", "wink": "😉", "blush": "😊", "yum": "😋", "relieved": "😌", "heart_eyes": "😍",
			"sunglasses": "😎", "smirk": "😏", "neutral_face": "😐", "expressionless": "😑", "unamused": "😒", "sweat": "😓", "pensive": "😔", "confused": "😕", "confounded": "😖", "kissing": "😗",
			"kissing_heart": "😘", "kissing_smiling_eyes": "😙", "kissing_closed_eyes": "😚", "stuck_out_tongue": "😛", "stuck_out_tongue_winking_eye": "😜", "stuck_out_tongue_closed_eyes": "😝",
			"disappointed": "😞", "worried": "😟", "angry": "😠", "rage": "😡", "cry": "😢", "persevere": "😣", "triumph": "😤", "disappointed_relieved": "😥", "frowning": "😦",
			"anguished": "😧", "fearful": "😨", "weary": "😩", "sleepy": "😪", "tired_face": "😫",
		}
		if val, ok := emoji[key]; ok {
			return val
		}
		return m
	})
	text = strings.NewReplacer("[ ]", `<input type="checkbox" readonly="readonly">`, "[x]", `<input type="checkbox" readonly="readonly" checked>`, "[_]", `<input type="checkbox">`, "[*]", `<input type="checkbox" checked>`).Replace(text)
	text = regexp.MustCompile(`(\[[^\]]+])##([^#]+)##`).ReplaceAllStringFunc(text, func(m string) string {
		match := regexp.MustCompile(`(\[[^\]]+])##([^#]+)##`).FindStringSubmatch(m)
		attr := parseAttrs(match[1])
		class := ""
		if len(attr.pos) > 0 {
			class = attr.pos[0]
		}
		return `<span class="` + class + `">` + match[2] + `</span>`
	})
	for i, raw := range passes {
		text = strings.ReplaceAll(text, "\x1aRMDPASS"+itoa(i)+"\x1a", raw)
	}
	return text
}

func (p *parser) renderObject(kind, key string, attr attrs) string {
	switch kind {
	case "link":
		text := attr.named["text"]
		if text == "" {
			text = titleize(key)
		}
		target := ""
		if attr.named["target"] != "" {
			target = ` target="` + attr.named["target"] + `"`
		}
		hreflang := ""
		if attr.named["hreflang"] != "" {
			hreflang = ` hreflang="` + attr.named["hreflang"] + `"`
		}
		return `<a href="` + first(attr.named["href"], key) + `"` + target + hreflang + `>` + text + `</a>`
	case "button":
		text := attr.named["text"]
		if text == "" {
			text = titleize(key)
		}
		target := ""
		if attr.named["target"] != "" {
			target = ` target="` + attr.named["target"] + `"`
		}
		return `<a href="` + first(attr.named["href"], "#"+key) + `" class="btn btn--` + first(attr.named["color"], "gray") + `"` + target + `>` + text + `</a>`
	case "br":
		return "<br>"
	case "anchor":
		if attr.named["name"] == "" {
			return "[ANCHOR MISSING NAME]"
		}
		return `<a name="` + attr.named["name"] + `"></a>`
	case "meter":
		id := first(attr.named["id"], "remarkd-meter-150")
		label := ""
		if attr.named["label"] != "" {
			label = `<label for="` + id + `" class="remarkd-meter-label">` + attr.named["label"] + `</label>`
		}
		return label + `<meter id="` + id + `" class="remarkd-meter"  min="` + first(attr.named["min"], "0") + `" max="` + first(attr.named["max"], "100") + `" value="` + first(attr.named["value"], "0") + `"">` + attr.named["text"] + `</meter>`
	case "video":
		padding := map[string]string{"1:1": "100", "4:3": "75", "3:2": "66.66", "8:5": "62.5"}[attr.named["aspect"]]
		if padding == "" {
			padding = "56.25"
		}
		if attr.named["source"] == "self" {
			return `<div class="video-container" style="padding-top: ` + padding + `%"><video controls><source src="` + key + `" type="` + first(attr.named["type"], "video/mp4") + `"></video></div>`
		}
		opts := []string{}
		if attr.named["start"] != "" {
			opts = append(opts, "start="+attr.named["start"])
		}
		if attr.named["end"] != "" {
			opts = append(opts, "end="+attr.named["end"])
		}
		opts = append(opts, "rel=0")
		return `<div class="video-container" style="padding-top: ` + padding + `%"><iframe src="https://www.youtube.com/embed/` + key + `?` + strings.Join(opts, ";") + `" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>`
	case "ref":
		ref := reference{len(p.references) + 1, first(attr.named["code"], itoa(len(p.references)+1)+"RM"), attr.named["content"]}
		p.references = append(p.references, ref)
		return `<sup class="reference"><a id="rmdref-bdy-` + ref.code + `" href="#rmdref-ft-` + ref.code + `">[` + itoa(ref.num) + `]</a></sup>`
	case "reflist":
		if len(p.references) == 0 {
			return ""
		}
		items := []string{}
		for _, ref := range p.references {
			items = append(items, `<li id="rmdref-ft-`+ref.code+`"><a href="#rmdref-bdy-`+ref.code+`" class="reference-tobody">^</a> `+p.inline(ref.content)+`</li>`)
		}
		return `<ol class="reference">` + strings.Join(items, "") + `</ol>`
	case "img":
		style := `display: ` + first(attr.named["display"], "inline-block") + `;max-width: ` + first(attr.named["max-width"], "100%") + `;`
		if attr.named["float"] != "" {
			style += `float: ` + attr.named["float"] + `;`
		}
		out := `<img src="` + attr.named["src"] + `"`
		if attr.named["alt"] != "" {
			out += ` alt="` + attr.named["alt"] + `"`
		}
		out += ` style="` + style + `"`
		classes := []string{}
		for _, pos := range attr.pos {
			if strings.HasPrefix(pos, ".") && len(pos) > 1 {
				classes = append(classes, pos[1:])
			}
		}
		if len(classes) > 0 {
			out += ` class="` + strings.Join(classes, " ") + `"`
		}
		return out + ` />`
	}
	return ""
}

func (p *parser) replaceAttrs(line string) string {
	for k, v := range p.attrs {
		line = strings.ReplaceAll(line, "{"+k+"}", v)
	}
	return line
}

func (p *parser) addDocumentAttr(line string) {
	m := regexp.MustCompile(`^:(!?[\w.-]+)(!)?:(.*)?$`).FindStringSubmatch(line)
	if len(m) == 0 {
		return
	}
	val := strings.TrimSpace(m[3])
	if val == "" {
		if m[2] == "!" {
			val = "0"
		} else {
			val = "1"
		}
	}
	p.attrs[m[1]] = val
}

func (p *parser) parseConditional(line string) (bool, string) {
	m := regexp.MustCompile(`^(end)?if(def|ndef|eval|nempty|empty|true|false)?::([^\[]*)\[([^]]*)]$`).FindStringSubmatch(line)
	if len(m) == 0 {
		return false, ""
	}
	p.index++
	if m[1] == "end" {
		if len(p.conditions) > 0 {
			p.conditions = p.conditions[:len(p.conditions)-1]
		}
		return true, ""
	}
	active := !p.conditionExcluded()
	kind := m[2]
	if kind == "" {
		kind = "def"
	}
	valid := active && p.validateCondition(kind, m[3], m[4])
	if kind != "eval" && m[4] != "" {
		if valid {
			return true, m[4]
		}
		return true, ""
	}
	p.conditions = append(p.conditions, valid)
	return true, ""
}

func (p *parser) conditionExcluded() bool {
	for _, condition := range p.conditions {
		if !condition {
			return true
		}
	}
	return false
}

func (p *parser) validateCondition(kind, condition, expression string) bool {
	if kind == "eval" {
		return evaluateExpression(expression)
	}
	for _, group := range strings.Split(condition, ",") {
		all := true
		for _, name := range strings.Split(group, "+") {
			val, ok := p.attrs[name]
			matched := false
			switch kind {
			case "def":
				matched = ok
			case "ndef":
				matched = !ok
			case "true":
				matched = val == "1" || val == "true"
			case "false":
				matched = val == "0" || val == "false"
			case "empty":
				matched = !ok || val == ""
			case "nempty":
				matched = ok && val != ""
			}
			if !matched {
				all = false
				break
			}
		}
		if all {
			return true
		}
	}
	return false
}

func (p *parser) isBlockStart(line string) bool {
	return line == "```" || line == "____" || line == "|===" ||
		regexp.MustCompile(`^[-=*.!]{4,10}$|^#{1,6} |^={2,6} |^((\d*\.)+) |^((\*|-){1,10}) |^(end)?if(def|ndef|eval|nempty|empty|true|false)?::|^!![\w-]+!!$|^include::[^\[]+\[.*]\s*$|^[^:]+:: .+|^(SUCCESS|WARNING|CAUTION|NOTE|NOTICE|IMPORTANT|DANGER|TIP)([:|)])|^<(\d+|\d+\.\d+|\w|[^>])> `).MatchString(line) ||
		strings.HasPrefix(line, "_|_#") || strings.HasPrefix(line, "_-_#") || strings.HasPrefix(line, "_|- ") || strings.TrimSpace(line) == "---" || strings.TrimSpace(line) == "<<<"
}

func parseAttrs(raw string) attrs {
	clean := strings.Trim(strings.TrimSpace(raw), "[]")
	result := attrs{named: map[string]string{}}
	re := regexp.MustCompile(`(^|[\s,]+)([^, =}]+)(=(("([^"]*)")|([^\s,}]*)))?`)
	for _, m := range re.FindAllStringSubmatch(clean, -1) {
		key := m[2]
		val := m[6]
		if val == "" {
			val = m[7]
		}
		result.pos = append(result.pos, key)
		result.named[key] = val
	}
	return result
}

func isProsCons(attr *attrs) bool {
	if attr == nil {
		return false
	}
	if len(attr.pos) > 0 && (attr.pos[0] == "pros-cons" || attr.pos[0] == "proscons") {
		return true
	}
	_, hasHyphen := attr.named["pros-cons"]
	_, hasPlain := attr.named["proscons"]
	return hasHyphen || hasPlain
}

func enabled(attr *attrs, key string, fallback bool) bool {
	if attr == nil {
		return fallback
	}
	value, ok := attr.named[key]
	if !ok {
		return fallback
	}
	switch strings.ToLower(value) {
	case "0", "false", "no", "off":
		return false
	default:
		return true
	}
}

func prosConsIcon(attr *attrs, idx int) string {
	icon := "✅"
	key := "pro-icon"
	if idx == 0 {
		icon = "❌"
		key = "con-icon"
	}
	if attr != nil && attr.named[key] != "" {
		icon = attr.named[key]
	}
	return `<span class="pros-cons-icon">` + html.EscapeString(icon) + `</span>`
}

func prosConsCellClass(prosCons bool, idx int) string {
	if !prosCons {
		return ""
	}
	side := "pro"
	if idx == 0 {
		side = "con"
	}
	return ` class="pros-cons-cell pros-cons-cell--` + side + `"`
}

func titleize(value string) string {
	value = strings.ReplaceAll(strings.ReplaceAll(value, "-", " "), "_", " ")
	parts := strings.Fields(value)
	for i, part := range parts {
		if part != "" {
			parts[i] = strings.ToUpper(part[:1]) + part[1:]
		}
	}
	return strings.Join(parts, " ")
}

func hyphenate(value string) string {
	value = strings.ToLower(strings.TrimSpace(value))
	out := strings.Builder{}
	lastDash := false
	for _, r := range value {
		if (r >= 'a' && r <= 'z') || (r >= '0' && r <= '9') {
			out.WriteRune(r)
			lastDash = false
		} else if !lastDash {
			out.WriteByte('-')
			lastDash = true
		}
	}
	return strings.Trim(out.String(), "-")
}

func evaluateExpression(expression string) bool {
	m := regexp.MustCompile(`^(.+?)\s*(===|==|!=|<=|<|>=|>|in|nin)\s*(.+)$`).FindStringSubmatch(strings.TrimSpace(expression))
	if len(m) == 0 {
		return false
	}
	left := strings.TrimSpace(m[1])
	op := m[2]
	right := strings.TrimSpace(m[3])
	switch op {
	case "===":
		return left == right
	case "==":
		return left == right
	case "!=":
		return left != right
	case "<=":
		return left <= right
	case "<":
		return left < right
	case ">=":
		return left >= right
	case ">":
		return left > right
	case "in":
		for _, part := range strings.Split(right, ",") {
			if strings.TrimSpace(part) == left {
				return true
			}
		}
		return false
	case "nin":
		for _, part := range strings.Split(right, ",") {
			if strings.TrimSpace(part) == left {
				return false
			}
		}
		return true
	}
	return false
}

func first(value, fallback string) string {
	if value != "" {
		return value
	}
	return fallback
}

func itoa(v int) string {
	if v == 0 {
		return "0"
	}
	digits := []byte{}
	for v > 0 {
		digits = append([]byte{byte('0' + v%10)}, digits...)
		v /= 10
	}
	return string(digits)
}
