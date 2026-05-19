<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentLessonPurchase;
use App\Support\ReceiptPdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function registration(Student $student): View
    {
        return view('receipts.show', [
            'receipt' => $this->registrationReceipt($student),
            'downloadRoute' => route('students.receipts.registration.download', $student),
            'backRoute' => route('students.index'),
        ]);
    }

    public function registrationPdf(Student $student): Response
    {
        $receipt = $this->registrationReceipt($student);

        return $this->pdfResponse($receipt, 'recibo-cadastro-'.$student->matricula.'.pdf');
    }

    public function purchase(StudentLessonPurchase $purchase): View
    {
        $purchase->loadMissing(['student', 'user']);

        return view('receipts.show', [
            'receipt' => $this->purchaseReceipt($purchase),
            'downloadRoute' => route('lesson-purchases.receipts.download', $purchase),
            'backRoute' => route('students.index'),
        ]);
    }

    public function purchasePdf(StudentLessonPurchase $purchase): Response
    {
        $purchase->loadMissing(['student', 'user']);
        $receipt = $this->purchaseReceipt($purchase);

        return $this->pdfResponse($receipt, 'recibo-compra-aulas-'.$purchase->id.'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationReceipt(Student $student): array
    {
        return [
            'number' => 'ALU-'.$student->id,
            'title' => 'Recibo de pagamento',
            'description' => 'Cadastro de aluno',
            'date' => $student->created_at ?? now(),
            'student_name' => $student->nome,
            'student_document' => $student->cpf,
            'amount' => $student->valor_pago,
            'payment_method' => $this->paymentMethodLabel($student->payment_method),
            'notes' => $student->observacao,
            'issued_by' => auth()->user()?->name ?: auth()->user()?->username,
            'items' => array_values(array_filter([
                $student->servico_oferecido ? 'Servico: '.$this->serviceLabel($student->servico_oferecido) : null,
                $student->categoria_pretendida ? 'Categoria: '.$student->categoria_pretendida : null,
                $student->quantidade_aulas_a_contratadas !== null ? 'Aulas A contratadas: '.$student->quantidade_aulas_a_contratadas : null,
                $student->quantidade_aulas_b_contratadas !== null ? 'Aulas B contratadas: '.$student->quantidade_aulas_b_contratadas : null,
            ])),
            'school' => config('receipt.school'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseReceipt(StudentLessonPurchase $purchase): array
    {
        return [
            'number' => 'CPA-'.$purchase->id,
            'title' => 'Recibo de pagamento',
            'description' => 'Compra adicional de aulas',
            'date' => $purchase->purchased_at,
            'student_name' => $purchase->student->nome,
            'student_document' => $purchase->student->cpf,
            'amount' => $purchase->amount_paid,
            'payment_method' => $this->paymentMethodLabel($purchase->payment_method),
            'notes' => $purchase->notes,
            'issued_by' => $purchase->user?->name ?: $purchase->user?->username,
            'items' => [
                'Categoria: '.$purchase->lesson_category,
                'Quantidade: '.$purchase->quantity.' aula'.($purchase->quantity === 1 ? '' : 's'),
            ],
            'school' => config('receipt.school'),
        ];
    }

    private function pdfResponse(array $receipt, string $filename): Response
    {
        return response(ReceiptPdf::make($receipt), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        if (! $paymentMethod) {
            return 'Nao informado';
        }

        return config('receipt.payment_methods')[$paymentMethod] ?? $paymentMethod;
    }

    private function serviceLabel(string $service): string
    {
        return [
            'primeira_habilitacao' => 'Primeira habilitacao',
            'adicao_categoria' => 'Adicao de categoria',
            'aula_habilitado' => 'Aula para habilitado',
            'prova_atualizacao' => 'Prova de Atualizacao',
            'prova_reciclagem' => 'Prova de Reciclagem',
        ][$service] ?? $service;
    }
}
