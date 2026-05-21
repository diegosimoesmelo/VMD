<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentProfilePdf;
use Illuminate\Http\Response;

class StudentProfileReportController extends Controller
{
    public function download(Student $student): Response
    {
        $student->loadMissing('teacher');
        $filename = 'ficha-'.$this->slug($student->nome).'.pdf';

        return response(StudentProfilePdf::make($student), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?? '');

        return trim($value, '-') ?: 'aluno';
    }
}
