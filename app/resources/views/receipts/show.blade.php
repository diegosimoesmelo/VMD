<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipt['title'] }} {{ $receipt['number'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e5e7eb;
            color: #111827;
            font-family: Arial, sans-serif;
        }
        .toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 16px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border: 0;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.secondary {
            background: #fff;
            color: #111827;
            border: 1px solid #d1d5db;
        }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            padding: 10mm;
            background: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
        }
        .receipt-copy {
            height: 135mm;
            padding: 8mm;
            border: 1px solid #111827;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .receipt-copy + .receipt-copy {
            margin-top: 7mm;
        }
        .receipt-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
        }
        .school-name {
            margin: 0 0 5px;
            font-size: 18px;
            text-transform: uppercase;
        }
        .school-info,
        .muted {
            color: #4b5563;
            font-size: 12px;
        }
        .copy-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .receipt-title {
            text-align: center;
            margin: 14px 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 18px;
            font-size: 13px;
        }
        .receipt-grid .wide { grid-column: 1 / -1; }
        .label {
            color: #4b5563;
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .amount {
            font-size: 18px;
            font-weight: 700;
        }
        .items {
            margin: 12px 0 0;
            padding-left: 18px;
            font-size: 13px;
        }
        .notes {
            margin-top: 10px;
            min-height: 28px;
            font-size: 13px;
        }
        .signature {
            align-self: flex-end;
            width: 76mm;
            padding-top: 9mm;
            border-bottom: 1px solid #111827;
            text-align: center;
            font-size: 11px;
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn secondary" href="{{ $downloadRoute }}">Baixar PDF</a>
        <a class="btn secondary" href="{{ $backRoute }}">Voltar</a>
    </div>

    <main class="sheet">
        @foreach (['Via do aluno', 'Via da autoescola'] as $copyLabel)
            <section class="receipt-copy">
                <div>
                    <header class="receipt-header">
                        <div>
                            <h1 class="school-name">{{ $receipt['school']['name'] }}</h1>
                            <div class="school-info">{{ $receipt['school']['address'] }}</div>
                            <div class="school-info">
                                @if (! empty($receipt['school']['document']))
                                    Documento: {{ $receipt['school']['document'] }}
                                @endif
                                @if (! empty($receipt['school']['phone']))
                                    Telefone: {{ $receipt['school']['phone'] }}
                                @endif
                            </div>
                        </div>
                        <div class="copy-label">{{ $copyLabel }}</div>
                    </header>

                    <h2 class="receipt-title">Recibo</h2>

                    <div class="receipt-grid">
                        <div>
                            <span class="label">Recibo</span>
                            {{ $receipt['number'] }}
                        </div>
                        <div>
                            <span class="label">Data</span>
                            {{ $receipt['date']?->format('d/m/Y H:i') }}
                        </div>
                        <div class="wide">
                            <span class="label">Recebemos de</span>
                            {{ $receipt['student_name'] }}
                        </div>
                        <div>
                            <span class="label">CPF</span>
                            {{ $receipt['student_document'] }}
                        </div>
                        <div>
                            <span class="label">Tipo de pagamento</span>
                            {{ $receipt['payment_method'] }}
                        </div>
                        <div class="wide">
                            <span class="label">Referente a</span>
                            {{ $receipt['description'] }}
                        </div>
                        <div>
                            <span class="label">Valor</span>
                            <span class="amount">R$ {{ number_format((float) ($receipt['amount'] ?? 0), 2, ',', '.') }}</span>
                        </div>
                        @if (! empty($receipt['issued_by']))
                            <div>
                                <span class="label">Emitido por</span>
                                {{ $receipt['issued_by'] }}
                            </div>
                        @endif
                    </div>

                    @if (! empty($receipt['items']))
                        <ul class="items">
                            @foreach ($receipt['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="notes">
                        <span class="label">Observação</span>
                        {{ $receipt['notes'] ?: 'Sem observações.' }}
                    </div>
                </div>

                <div class="signature"></div>
            </section>
        @endforeach
    </main>
</body>
</html>
