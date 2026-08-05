@extends('layouts.panel', ['title' => 'Marcar aulas'])

@section('content')
    <style>
        .student-schedule-toolbar {
            display: grid;
            gap: 14px;
            margin-bottom: 20px;
        }
        .student-schedule-toolbar form,
        .student-schedule-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .student-schedule-toolbar .field-inline {
            min-width: 220px;
            flex: 1 1 220px;
        }
        .student-schedule-toolbar select,
        .student-schedule-toolbar input[type="date"] {
            min-height: 48px;
            margin-bottom: 0;
        }
        .schedule-student-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .schedule-summary-item {
            padding: 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .schedule-summary-item span {
            display: block;
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .schedule-summary-item strong {
            color: var(--color-secondary);
        }
        .student-schedule-grid-wrap {
            overflow-x: auto;
        }
        .student-schedule-grid {
            width: 100%;
            min-width: 1080px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .student-schedule-grid th,
        .student-schedule-grid td {
            padding: 12px;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            border-right: 1px solid rgba(var(--color-secondary-rgb), 0.05);
            vertical-align: top;
        }
        .student-schedule-grid th {
            background: rgba(var(--color-secondary-rgb), 0.04);
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 700;
        }
        .schedule-slot {
            min-height: 116px;
            display: grid;
            gap: 8px;
            padding: 12px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
        }
        .schedule-slot.available {
            border-color: rgba(34, 197, 94, 0.26);
            background: rgba(240, 253, 244, 0.72);
        }
        .schedule-slot.locked {
            background: rgba(148, 163, 184, 0.14);
            border-color: rgba(100, 116, 139, 0.20);
        }
        .schedule-slot.existing {
            background: rgba(217, 119, 6, 0.08);
            border-color: rgba(217, 119, 6, 0.18);
        }
        .schedule-slot.existing.training-student,
        .schedule-slot.available.training-student {
            background: rgba(34, 197, 94, 0.16);
            border-color: rgba(22, 163, 74, 0.34);
        }
        .slot-pick {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--color-secondary);
            font-weight: 800;
        }
        .slot-pick input {
            width: 18px;
            height: 18px;
            accent-color: var(--color-primary);
        }
        .slot-reason {
            color: var(--color-muted-text);
            font-size: 13px;
            line-height: 1.45;
        }
        .agenda-time {
            width: 120px;
            white-space: nowrap;
            color: var(--color-secondary);
            font-weight: 800;
        }
        .agenda-week-nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 18px;
        }
        .empty-agenda {
            padding: 32px;
            text-align: center;
        }
        .empty-agenda strong {
            display: block;
            color: var(--color-secondary);
            margin-bottom: 8px;
        }
        @media (max-width: 980px) {
            .schedule-student-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .schedule-student-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Agenda por aluno</span>
            <h1>Marcar aulas para {{ $student->nome }}</h1>
            <p>Escolha categoria, veiculo e professor, selecione varios horarios livres na grade semanal e salve tudo de uma vez.</p>
        </div>
        <div class="header-actions">
            <a class="btn-secondary" href="{{ route('students.index') }}">Voltar para alunos</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div class="notice notice-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="schedule-student-summary">
        <div class="schedule-summary-item">
            <span>Matricula</span>
            <strong>{{ $student->matricula ?: '-' }}</strong>
        </div>
        <div class="schedule-summary-item">
            <span>Categoria</span>
            <strong>{{ $student->categoria_pretendida ?: '-' }}</strong>
        </div>
        <div class="schedule-summary-item">
            <span>Aulas A restantes</span>
            <strong>{{ $student->quantidade_aulas_a_restantes ?? 0 }}</strong>
        </div>
        <div class="schedule-summary-item">
            <span>Aulas B restantes</span>
            <strong>{{ $student->quantidade_aulas_b_restantes ?? 0 }}</strong>
        </div>
    </div>

    <div class="surface-card section-card">
        <div class="student-schedule-toolbar">
            <form method="GET" action="{{ route('students.appointments.create', $student) }}" data-student-schedule-filter>
                <div class="field-inline">
                    <label for="lesson_category">Categoria da aula</label>
                    <select id="lesson_category" name="lesson_category" required>
                        <option value="">Selecione</option>
                        @foreach ($availableCategories as $availableCategory)
                            <option value="{{ $availableCategory }}" @selected($category === $availableCategory)>
                                {{ $categoryLabels[$availableCategory] ?? $availableCategory }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="vehicle">Veiculo</label>
                    <select id="vehicle" name="vehicle" @disabled($category === '') required>
                        <option value="">Selecione</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected($selectedVehicle?->id === $vehicle->id)>
                                {{ strtoupper($vehicle->placa) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="teacher">Professor</label>
                    <select id="teacher" name="teacher" @disabled($category === '') required>
                        <option value="">Selecione</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($selectedTeacher?->id === $teacher->id)>
                                {{ $teacher->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="week_start">Semana</label>
                    <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
                </div>
                <div class="student-schedule-actions">
                    <button class="btn" type="submit">Carregar agenda</button>
                    <a class="btn-secondary" href="{{ route('students.appointments.create', $student) }}">Limpar</a>
                </div>
            </form>
        </div>

        @if (empty($availableCategories))
            <div class="empty-agenda">
                <strong>Aluno sem saldo disponivel.</strong>
                <p>Registre uma compra de aulas antes de marcar novos horarios para este aluno.</p>
            </div>
        @elseif (! $selectedVehicle || ! $selectedTeacher || $category === '')
            <div class="empty-agenda">
                <strong>Selecione categoria, veiculo e professor.</strong>
                <p>A grade semanal aparece depois que os tres campos estiverem preenchidos.</p>
            </div>
        @else
            <div class="agenda-week-nav">
                <a class="btn-secondary" href="{{ route('students.appointments.create', [
                    'student' => $student,
                    'lesson_category' => $category,
                    'vehicle' => $selectedVehicle->id,
                    'teacher' => $selectedTeacher->id,
                    'week_start' => $weekStart->copy()->subWeek()->toDateString(),
                ]) }}">Semana anterior</a>
                <span><strong>{{ strtoupper($selectedVehicle->placa) }}</strong> com {{ $selectedTeacher->nome }} - {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                <a class="btn-secondary" href="{{ route('students.appointments.create', [
                    'student' => $student,
                    'lesson_category' => $category,
                    'vehicle' => $selectedVehicle->id,
                    'teacher' => $selectedTeacher->id,
                    'week_start' => $weekStart->copy()->addWeek()->toDateString(),
                ]) }}">Proxima semana</a>
            </div>

            <form method="POST" action="{{ route('students.appointments.store', $student) }}">
                @csrf
                <input type="hidden" name="lesson_category" value="{{ $category }}">
                <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
                <input type="hidden" name="teacher_id" value="{{ $selectedTeacher->id }}">
                <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                <div class="student-schedule-grid-wrap">
                    <table class="student-schedule-grid">
                        <thead>
                            <tr>
                                <th class="agenda-time">Horario</th>
                                @foreach ($weekDays as $day)
                                    <th>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('d/m') }}<br><span class="muted">{{ $day->format('d/m') }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timeSlots as $slot)
                                <tr>
                                    <td class="agenda-time">{{ $slot }}</td>
                                    @foreach ($weekDays as $day)
                                        @php
                                            $slotKey = $day->format('Y-m-d').' '.$slot;
                                            $slotAppointments = collect($slotAppointmentsBySlot->get($slotKey, []));
                                            $studentAppointment = $slotAppointments->first(fn ($appointment) => (int) $appointment->student_id === (int) $student->id);
                                            $teacherAppointment = $slotAppointments->first(fn ($appointment) => (int) $appointment->teacher_id === (int) $selectedTeacher->id);
                                            $vehicleAppointment = $slotAppointments->first(fn ($appointment) => (int) $appointment->vehicle_id === (int) $selectedVehicle->id);
                                            $lockedReason = null;

                                            if (! $selectedTeacher->supportsTimeSlot($slot)) {
                                                $lockedReason = 'Fora do turno do professor.';
                                            } elseif ($studentAppointment) {
                                                $lockedReason = 'Aluno ja possui aula neste horario.';
                                            } elseif ($teacherAppointment) {
                                                $lockedReason = 'Professor ocupado com '.($teacherAppointment->student?->nome ?: 'outro compromisso').'.';
                                            } elseif ($vehicleAppointment) {
                                                $lockedReason = 'Veiculo ocupado com '.($vehicleAppointment->student?->nome ?: 'outro compromisso').'.';
                                            }
                                        @endphp
                                        <td>
                                            <div class="schedule-slot {{ $lockedReason ? ($studentAppointment ? 'existing' : 'locked') : 'available' }} {{ $student->treinamento_para_habilitados && ($studentAppointment || ! $lockedReason) ? 'training-student' : '' }}">
                                                @if ($lockedReason)
                                                    <strong>Indisponivel</strong>
                                                    <span class="slot-reason">{{ $lockedReason }}</span>
                                                    @if ($studentAppointment)
                                                        <span class="slot-reason">{{ $studentAppointment->teacher?->nome }} - {{ strtoupper($studentAppointment->vehicle?->placa ?? '') }}</span>
                                                    @endif
                                                @else
                                                    <label class="slot-pick">
                                                        <input type="checkbox" name="slots[]" value="{{ $day->toDateString() }}|{{ $slot }}">
                                                        Selecionar
                                                    </label>
                                                    <span class="slot-reason">Livre para {{ $student->nome }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Salvar aulas selecionadas</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filterForm = document.querySelector('[data-student-schedule-filter]');

            if (! filterForm) {
                return;
            }

            filterForm.querySelectorAll('select[name="lesson_category"], select[name="vehicle"], select[name="teacher"], input[name="week_start"]').forEach(function (field) {
                field.addEventListener('change', function () {
                    if (field.name === 'lesson_category') {
                        var vehicleField = filterForm.querySelector('select[name="vehicle"]');
                        var teacherField = filterForm.querySelector('select[name="teacher"]');

                        if (vehicleField) {
                            vehicleField.value = '';
                        }

                        if (teacherField) {
                            teacherField.value = '';
                        }
                    }

                    filterForm.submit();
                });
            });
        });
    </script>
@endsection
