<?php

namespace App\Support;

use Carbon\CarbonInterface;

class ReceiptPdf
{
    /**
     * @param array<string, mixed> $receipt
     */
    public static function make(array $receipt): string
    {
        $stream = self::receiptCopy($receipt, 800, 'VIA DO ALUNO')
            .self::receiptCopy($receipt, 386, 'VIA DA AUTOESCOLA');

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            '<< /Length '.strlen($stream).' >>'."\nstream\n".$stream."\nendstream",
        ];

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

    /**
     * @param array<string, mixed> $receipt
     */
    private static function receiptCopy(array $receipt, int $top, string $copyLabel): string
    {
        $school = $receipt['school'] ?? [];
        $lines = [
            ['RECIBO', 270, $top, 16, true],
            [$copyLabel, 448, $top, 9, false],
            [(string) ($school['name'] ?? 'Autoescola'), 48, $top - 24, 12, true],
            [(string) ($school['address'] ?? ''), 48, $top - 40, 9, false],
            [trim('Documento: '.($school['document'] ?? '').'  Telefone: '.($school['phone'] ?? '')), 48, $top - 54, 9, false],
            ['Recibo: '.($receipt['number'] ?? '-'), 48, $top - 82, 10, true],
            ['Data: '.self::formatDate($receipt['date'] ?? null), 390, $top - 82, 10, false],
            ['Recebemos de: '.($receipt['student_name'] ?? '-'), 48, $top - 108, 10, false],
            ['CPF: '.($receipt['student_document'] ?? '-'), 390, $top - 108, 10, false],
            ['Referente a: '.($receipt['description'] ?? '-'), 48, $top - 132, 10, false],
            ['Forma de pagamento: '.($receipt['payment_method'] ?? 'Nao informado'), 48, $top - 156, 10, false],
            ['Valor: R$ '.self::money($receipt['amount'] ?? null), 390, $top - 156, 12, true],
        ];

        $content = self::box(36, $top - 330, 523, 350)
            .self::line(36, $top - 66, 559, $top - 66)
            .self::line(36, $top - 178, 559, $top - 178)
            .self::line(36, $top - 250, 559, $top - 250);

        foreach ($lines as [$text, $x, $y, $size, $bold]) {
            $content .= self::text((string) $text, $x, $y, $size, $bold);
        }

        $y = $top - 198;
        foreach (($receipt['items'] ?? []) as $item) {
            $content .= self::text('- '.$item, 48, $y, 9, false);
            $y -= 14;
        }

        $notes = trim((string) ($receipt['notes'] ?? ''));
        if ($notes !== '') {
            $content .= self::text('Observacao: '.self::limit($notes, 110), 48, $top - 270, 9, false);
        }

        $content .= self::line(342, $top - 308, 526, $top - 308)
            .self::text('Assinatura', 405, $top - 322, 8, false);

        return $content;
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

    private static function box(int $x, int $y, int $width, int $height): string
    {
        return "{$x} {$y} {$width} {$height} re S\n";
    }

    private static function escape(string $text): string
    {
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text) ?: $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function money(mixed $amount): string
    {
        return number_format((float) ($amount ?? 0), 2, ',', '.');
    }

    private static function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y H:i');
        }

        return now()->format('d/m/Y H:i');
    }

    private static function limit(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 3).'...';
    }
}
