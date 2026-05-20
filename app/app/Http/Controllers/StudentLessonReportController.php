<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Student;
use App\Support\StudentLessonsPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StudentLessonReportController extends Controller
{
    public function download(Request $request, Student $student): Response
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['A', 'B', 'AB'])],
        ]);

        $appointments = $student->appointments()
            ->with(['teacher', 'vehicle'])
            ->where('type', Appointment::TYPE_LESSON)
            ->when($validated['category'] !== 'AB', fn ($query) => $query->where('lesson_category', $validated['category']))
            ->orderBy('starts_at')
            ->get();

        $filename = 'aulas-'.$this->slug($student->nome).'-categoria-'.$validated['category'].'.pdf';

        return response(StudentLessonsPdf::make($student, $appointments, $validated['category']), 200, [
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
