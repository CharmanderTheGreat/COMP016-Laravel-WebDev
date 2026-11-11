<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Tiny .xlsx (Excel) file writer with NO external package.
 * An .xlsx file is just a zip of XML files; PHP's built-in ZipArchive does the zipping.
 * It was written this way on purpose so the Railway deploy needs no new Composer package.
 *
 * Usage:
 *   $xlsx = new XlsxWriter();
 *   $xlsx->addSheet('October 2026', [
 *       [['Date', XlsxWriter::HEADER], ['Status', XlsxWriter::HEADER]],
 *       ['2026-10-05', ['Present', XlsxWriter::PRESENT]],
 *   ], [14, 12]);
 *   $xlsx->save('/tmp/file.xlsx');
 *
 * A cell is either a plain value (string / int / float / null) or [value, style].
 */
class XlsxWriter
{
    // Cell styles (index into <cellXfs> in styles.xml below)
    public const DEFAULT = 0;  // plain text with a thin border
    public const HEADER = 1;   // white bold text on maroon
    public const CENTER = 2;   // centred, thin border
    public const PRESENT = 3;  // centred, green
    public const LATE = 4;     // centred, yellow
    public const ABSENT = 5;   // centred, red
    public const EXCUSED = 6;  // centred, blue
    public const TITLE = 7;    // big maroon heading, no border
    public const BOLD = 8;     // bold, thin border
    public const PLAIN = 9;    // no border

    /** @var array<int, array{name: string, rows: array, widths: array, freeze: int}> */
    private array $sheets = [];

    /**
     * @param  array<int, array<int, mixed>>  $rows   list of rows, each a list of cells
     * @param  array<int, int|float>  $widths  column widths (in characters), optional
     * @param  int  $freezeRows  keep this many top rows visible while scrolling
     */
    public function addSheet(string $name, array $rows, array $widths = [], int $freezeRows = 0): void
    {
        $this->sheets[] = [
            'name'   => $this->uniqueSheetName($name),
            'rows'   => $rows,
            'widths' => $widths,
            'freeze' => $freezeRows,
        ];
    }

    public function save(string $path): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is required to create .xlsx files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the .xlsx file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->sheetXml($sheet));
        }

        $zip->close();
    }

    // ------------------------------------------------------------------ parts

    private function contentTypes(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $_) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" '
                . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $sheets
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $sheet) {
            $sheets .= '<sheet name="' . $this->esc($sheet['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets></workbook>';
    }

    private function workbookRels(): string
    {
        $rels = '';
        foreach ($this->sheets as $i => $_) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" '
                . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                . 'Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '" '
            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    /** Fonts, fills, borders and the 10 cell styles referenced by the constants above. */
    private function styles(): string
    {
        $fills = ['800000', 'C6E7D2', 'FFF1B8', 'F8C9C5', 'C9DCF2']; // fill ids 2..6

        $fillXml = '<fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>';
        foreach ($fills as $rgb) {
            $fillXml .= '<fill><patternFill patternType="solid"><fgColor rgb="FF' . $rgb . '"/><bgColor indexed="64"/></patternFill></fill>';
        }

        $center = '<alignment horizontal="center" vertical="center"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="4">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FF800000"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="' . (2 + count($fills)) . '">' . $fillXml . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFD9D0C3"/></left><right style="thin"><color rgb="FFD9D0C3"/></right>'
            . '<top style="thin"><color rgb="FFD9D0C3"/></top><bottom style="thin"><color rgb="FFD9D0C3"/></bottom><diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="10">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'                                  // 0 DEFAULT
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $center . '</xf>' // 1 HEADER
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">' . $center . '</xf>'                    // 2 CENTER
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $center . '</xf>' // 3 PRESENT
            . '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $center . '</xf>' // 4 LATE
            . '<xf numFmtId="0" fontId="2" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $center . '</xf>' // 5 ABSENT
            . '<xf numFmtId="0" fontId="2" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $center . '</xf>' // 6 EXCUSED
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'                                    // 7 TITLE
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>'                    // 8 BOLD
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'                                                  // 9 PLAIN
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    /** @param array{name: string, rows: array, widths: array, freeze: int} $sheet */
    private function sheetXml(array $sheet): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        // Freeze the top rows (header stays visible while scrolling)
        if ($sheet['freeze'] > 0) {
            $n = $sheet['freeze'];
            $xml .= '<sheetViews><sheetView workbookViewId="0">'
                . '<pane ySplit="' . $n . '" topLeftCell="A' . ($n + 1) . '" activePane="bottomLeft" state="frozen"/>'
                . '<selection pane="bottomLeft"/></sheetView></sheetViews>';
        }

        if ($sheet['widths']) {
            $xml .= '<cols>';
            foreach (array_values($sheet['widths']) as $i => $width) {
                $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach (array_values($sheet['rows']) as $r => $row) {
            $xml .= '<row r="' . ($r + 1) . '">';
            foreach (array_values($row) as $c => $cell) {
                $xml .= $this->cellXml($this->columnName($c) . ($r + 1), $cell);
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    /** @param mixed $cell plain value or [value, style] */
    private function cellXml(string $ref, mixed $cell): string
    {
        $value = $cell;
        $style = self::DEFAULT;

        if (is_array($cell)) {
            $value = $cell[0] ?? null;
            $style = $cell[1] ?? self::DEFAULT;
        }

        if ($value === null || $value === '') {
            // Keep the style (so borders/colours show) but no content
            return '<c r="' . $ref . '" s="' . $style . '"/>';
        }

        if (is_int($value) || is_float($value)) {
            return '<c r="' . $ref . '" s="' . $style . '"><v>' . $value . '</v></c>';
        }

        return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
            . $this->esc((string) $value) . '</t></is></c>';
    }

    // ------------------------------------------------------------------ helpers

    /** 0 => A, 25 => Z, 26 => AA ... */
    private function columnName(int $index): string
    {
        $name = '';
        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + ($i % 26)) . $name;
        }

        return $name;
    }

    /** Escape text for XML and drop control characters Excel rejects. */
    private function esc(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Excel sheet names: max 31 chars, none of \ / ? * [ ] :, and unique inside the workbook. */
    private function uniqueSheetName(string $name): string
    {
        $name = trim(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $name) ?? '');
        $name = mb_substr($name === '' ? 'Sheet' : $name, 0, 31);

        $taken = array_map(fn ($s) => mb_strtolower($s['name']), $this->sheets);
        $candidate = $name;
        $n = 2;
        while (in_array(mb_strtolower($candidate), $taken, true)) {
            $suffix = ' (' . $n++ . ')';
            $candidate = mb_substr($name, 0, 31 - mb_strlen($suffix)) . $suffix;
        }

        return $candidate;
    }
}
