<?php

namespace App\Support;

use App\Models\Student;

class StudentProfilePdf
{
    public static function make(Student $student): string
    {
        return self::buildPdf([self::page($student)]);
    }

    private static function page(Student $student): string
    {
        $school = config('receipt.school');
        $content = self::text((string) ($school['name'] ?? 'Autoescola'), 40, 800, 15, true)
            .self::text((string) ($school['address'] ?? ''), 40, 783, 9)
            .self::text('FICHA CADASTRAL DO ALUNO', 40, 756, 14, true)
            .self::text('Emitido em: '.now()->format('d/m/Y H:i'), 410, 756, 9)
            .self::line(40, 742, 555, 742);

        $sections = [
            'Identificação' => [
                ['Nome', $student->nome],
                ['Matrícula', $student->matricula],
                ['CPF', $student->cpf],
                ['RG', trim(($student->rg ?: '-').' '.($student->orgao_exp ?: '').'/'.($student->rg_estado ?: ''))],
                ['Nascimento', $student->data_nascimento?->format('d/m/Y')],
                ['Sexo', $student->sexo],
                ['Estado civil', $student->estado_civil],
                ['Escolaridade', $student->grau_escolaridade],
            ],
            'Endereço e contato' => [
                ['Endereço', self::address($student)],
                ['Cidade/UF', trim(($student->cidade ?: '-').'/'.($student->estado ?: '-'))],
                ['CEP', $student->cep],
                ['Telefone', $student->telefone],
                ['Email', $student->email],
            ],
            'Naturalidade e filiação' => [
                ['Naturalidade', trim(($student->naturalidade ?: '-').'/'.($student->naturalidade_estado ?: '-'))],
                ['Nacionalidade', $student->nacionalidade],
                ['Nome da mãe', $student->nome_mae],
                ['Nome do pai', $student->nome_pai],
            ],
            'Profissional' => [
                ['Empresa', $student->empresa],
                ['Profissão', $student->profissao],
                ['Telefone profissional', $student->telefone_profissional],
            ],
            'Contrato e aulas' => [
                ['Professor responsável', $student->teacher?->nome],
                ['Status', self::statusLabel($student->status)],
                ['Serviço', self::serviceLabel($student->servico_oferecido)],
                ['Categoria', $student->categoria_pretendida],
                ['Valor pago', $student->valor_pago !== null ? 'R$ '.number_format((float) $student->valor_pago, 2, ',', '.') : null],
                ['Aulas A contratadas', $student->quantidade_aulas_a_contratadas],
                ['Aulas B contratadas', $student->quantidade_aulas_b_contratadas],
            ],
            'Observações' => [
                ['Observação', $student->observacao],
            ],
        ];

        $y = 716;
        foreach ($sections as $title => $fields) {
            $content .= self::sectionTitle($title, $y);
            $y -= 24;

            foreach ($fields as [$label, $value]) {
                $content .= self::field($label, $value, $y);
                $y -= 16;
            }

            $y -= 8;
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
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.(3 + count($pages) * 2).' 0 R /F2 '.(4 + count($pages) * 2).' 0 R >> >> /Contents '.$contentObject.' 0 R >>';
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

    private static function sectionTitle(string $title, int $y): string
    {
        return self::box(40, $y - 8, 515, 20, true)
            .self::text($title, 48, $y - 1, 10, true);
    }

    private static function field(string $label, mixed $value, int $y): string
    {
        return self::text($label.':', 48, $y, 8, true)
            .self::text(self::limit(self::display($value), 78), 170, $y, 8);
    }

    private static function address(Student $student): string
    {
        return trim(implode(', ', array_filter([
            $student->endereco,
            $student->numero,
            $student->complemento,
            $student->bairro,
        ])));
    }

    private static function serviceLabel(?string $service): string
    {
        return [
            'primeira_habilitacao' => 'Primeira habilitação',
            'adicao_categoria' => 'Adição de categoria',
            'aula_habilitado' => 'Aula para habilitado',
            'prova_atualizacao' => 'Prova de Atualização',
            'prova_reciclagem' => 'Prova de Reciclagem',
        ][$service ?? ''] ?? '-';
    }

    private static function statusLabel(?string $status): string
    {
        return [
            'em_aula_teorica' => 'Em aula teórica',
            'passou_na_prova_teorica' => 'Passou na prova teórica',
            'em_aula_pratica' => 'Em aula prática',
            'finalizado' => 'Finalizado',
        ][$status ?? ''] ?? '-';
    }

    private static function display(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
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
