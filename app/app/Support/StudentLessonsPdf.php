<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Student;
use Illuminate\Support\Collection;

class StudentLessonsPdf
{
    /**
     * @param Collection<int, Appointment> $appointments
     */
    public static function make(Student $student, Collection $appointments, string $category): string
    {
        $rows = $appointments->values();
        $chunks = $rows->chunk(18);

        if ($chunks->isEmpty()) {
            $chunks = collect([collect()]);
        }

        $pages = [];
        $pageCount = $chunks->count();

        foreach ($chunks as $index => $chunk) {
            $pages[] = self::page($student, $chunk, $category, $index + 1, $pageCount);
        }

        return self::buildPdf($pages);
    }

    /**
     * @param Collection<int, Appointment> $appointments
     */
    private static function page(Student $student, Collection $appointments, string $category, int $page, int $pageCount): string
    {
        $school = config('receipt.school');
        $content = self::text((string) ($school['name'] ?? 'Autoescola'), 40, 555, 15, true)
            .self::text((string) ($school['address'] ?? ''), 40, 538, 9)
            .self::text('RELATORIO DE AULAS AGENDADAS', 324, 555, 14, true)
            .self::text('Pagina '.$page.' de '.$pageCount, 724, 555, 9)
            .self::line(40, 524, 802, 524)
            .self::text('Aluno: '.$student->nome, 40, 504, 10, true)
            .self::text('CPF: '.($student->cpf ?: '-'), 40, 488, 9)
            .self::text('Matricula: '.($student->matricula ?: '-'), 235, 488, 9)
            .self::text('Categoria exportada: '.($category === 'AB' ? 'A e B' : $category), 430, 488, 9)
            .self::text('Emitido em: '.now()->format('d/m/Y H:i'), 625, 488, 9);

        $headers = [
            ['#', 40, 454, 26],
            ['Data', 66, 454, 72],
            ['Horario', 138, 454, 80],
            ['Cat.', 218, 454, 44],
            ['Professor', 262, 454, 190],
            ['Veiculo', 452, 454, 82],
            ['Status', 534, 454, 126],
            ['Observacao', 660, 454, 142],
        ];

        $content .= self::box(40, 436, 762, 26, true);
        foreach ($headers as [$label, $x, $y]) {
            $content .= self::text($label, $x + 4, $y, 9, true);
        }

        $y = 416;
        if ($appointments->isEmpty()) {
            return $content.self::text('Nenhuma aula encontrada para a categoria selecionada.', 44, $y, 10);
        }

        foreach ($appointments as $appointment) {
            $content .= self::box(40, $y - 8, 762, 22);
            $content .= self::text((string) $appointment->getKey(), 44, $y, 8);
            $content .= self::text($appointment->starts_at?->format('d/m/Y') ?: '-', 70, $y, 8);
            $content .= self::text(self::timeRange($appointment), 142, $y, 8);
            $content .= self::text($appointment->lesson_category ?: '-', 222, $y, 8);
            $content .= self::text(self::limit($appointment->teacher?->nome ?: '-', 34), 266, $y, 8);
            $content .= self::text(self::limit($appointment->vehicle ? strtoupper($appointment->vehicle->placa) : '-', 14), 456, $y, 8);
            $content .= self::text(self::limit($appointment->effectiveLessonStatusLabel(), 22), 538, $y, 8);
            $content .= self::text(self::limit($appointment->notes ?: $appointment->lesson_status_notes ?: '-', 25), 664, $y, 8);
            $y -= 22;
        }

        return $content;
    }

    /**
     * @param list<string> $pages
     */
    private static function buildPdf(array $pages): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($index) => (3 + $index * 2).' 0 R', array_keys($pages))).'] /Count '.count($pages).' >>',
        ];

        foreach ($pages as $index => $pageStream) {
            $pageObject = 3 + $index * 2;
            $contentObject = $pageObject + 1;
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 '.(3 + count($pages) * 2).' 0 R /F2 '.(4 + count($pages) * 2).' 0 R >> >> /Contents '.$contentObject.' 0 R >>';
            $objects[] = '<< /Length '.strlen($pageStream).' >>'."\nstream\n".$pageStream."\nendstream";
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";
    }

    private static function timeRange(Appointment $appointment): string
    {
        $start = $appointment->starts_at?->format('H:i') ?: '-';
        $end = $appointment->ends_at?->format('H:i');

        return $end ? $start.' - '.$end : $start;
    }

    private static function text(string $text, int $x, int $y, int $size = 10, bool $bold = false): string
    {
        $font = $bold ? 'F2' : 'F1';

        return "BT /{$font} {$size} Tf {$x} {$y} Td (".self::escape($text).") Tj ET\n";
    }

    private static function line(int $x1, int $y1, int $x2, int $y2): string
    {
        return "{$x1} {$y1} m {$x2} {$y2} l S\n";
    }

    private static function box(int $x, int $y, int $width, int $height, bool $filled = false): string
    {
        if ($filled) {
            return "0.95 0.95 0.95 rg {$x} {$y} {$width} {$height} re f 0 0 0 rg {$x} {$y} {$width} {$height} re S\n";
        }

        return "{$x} {$y} {$width} {$height} re S\n";
    }

    private static function escape(string $text): string
    {
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text) ?: $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function limit(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 3).'...';
    }
}
