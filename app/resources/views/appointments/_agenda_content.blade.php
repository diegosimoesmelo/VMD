@if (session('success'))
    <p class="notice notice-success">{{ session('success') }}</p>
@endif

@if ($errors->any())
    <p class="notice notice-error">{{ $errors->first() }}</p>
@endif

@php
    $teacherContextId = $selectedTeacher?->id;
    $agendaRouteParams = [
        'schedule_mode' => $scheduleMode,
        'teacher' => $teacherContextId,
        'vehicle' => $selectedVehicle?->id,
        'vehicle_category' => $vehicleCategoryFilter,
    ];
@endphp

<div class="surface-card section-card">
    <div class="agenda-toolbar">
        <form method="GET" action="{{ route('appointments.index') }}" data-agenda-filter-form>
            <div class="field-inline">
                <span class="filter-label">Tipo de agenda</span>
                <div class="agenda-mode-radio">
                    <label>
                        <input type="radio" name="schedule_mode" value="vehicle" @checked($scheduleMode === 'vehicle')>
                        <span>Por veículo</span>
                    </label>
                    <label>
                        <input type="radio" name="schedule_mode" value="teacher" @checked($scheduleMode === 'teacher')>
                        <span>Por instrutor</span>
                    </label>
                </div>
            </div>

            @if ($scheduleMode === 'teacher')
                <div class="field-inline">
                    <label for="teacher">Instrutor</label>
                    <select id="teacher" name="teacher">
                        <option value="">Selecione o instrutor</option>
                        @foreach ($allTeachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($selectedTeacher?->id === $teacher->id)>
                                {{ $teacher->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="field-inline">
                <label for="vehicle_category">Categoria do veículo</label>
                <select id="vehicle_category" name="vehicle_category">
                    <option value="">Todas</option>
                    @foreach ($vehicleCategoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected($vehicleCategoryFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-inline">
                <label for="vehicle">Veículo</label>
                <select id="vehicle" name="vehicle">
                    <option value="">Selecione o veículo</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected($selectedVehicle?->id === $vehicle->id)>
                            {{ strtoupper($vehicle->placa) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field-inline">
                <label for="week_start">Semana de referência</label>
                <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
            </div>

            <div class="agenda-toolbar-actions">
                <button class="btn" type="submit">Carregar agenda</button>
                <a class="btn-secondary" data-agenda-nav href="{{ route('appointments.index') }}">Limpar filtros</a>
            </div>
        </form>
    </div>

    @if ($hasAgendaSelection && $selectedVehicle)
        <div class="vehicle-hero">
            <div class="vehicle-hero-card">
                <span class="eyebrow">{{ $scheduleMode === 'teacher' ? 'Instrutor em foco' : 'Veículo em foco' }}</span>
                <h2>
                    @if ($scheduleMode === 'teacher' && $selectedTeacher)
                        Agenda de {{ $selectedTeacher->nome }} com o veículo {{ strtoupper($selectedVehicle->placa) }}
                    @else
                        Você está montando a agenda deste veículo
                    @endif
                </h2>
                <div class="vehicle-plate">{{ strtoupper($selectedVehicle->placa) }}</div>
                <p>
                    @if ($scheduleMode === 'teacher' && $selectedTeacher)
                        Nesta grade, o instrutor e o veículo ficam reservados no horário selecionado. Horários já usados por este instrutor ou por este carro aparecem bloqueados.
                    @else
                        Todos os horários desta grade pertencem exclusivamente a este veículo. Qualquer aula marcada aqui reserva esta placa para o horário selecionado.
                    @endif
                </p>
                <div class="vehicle-hero-meta">
                    @if ($scheduleMode === 'teacher' && $selectedTeacher)
                        <span class="vehicle-hero-chip">{{ $selectedTeacher->nome }}</span>
                    @endif
                    <span class="vehicle-hero-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
                    <span class="vehicle-hero-chip">Semana {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="vehicle-hero-side">
                <div class="vehicle-focus-card">
                    <span class="eyebrow">Leitura rápida</span>
                    <strong>{{ strtoupper($selectedVehicle->placa) }}</strong>
                    <p>Use esta referência antes de salvar qualquer aula para evitar marcar no carro errado.</p>
                </div>
                <div class="vehicle-focus-card">
                    <span class="eyebrow">{{ $scheduleMode === 'teacher' ? 'Instrutor' : 'Categoria' }}</span>
                    <strong>{{ $scheduleMode === 'teacher' && $selectedTeacher ? $selectedTeacher->nome : (\App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria) }}</strong>
                    <p>{{ $scheduleMode === 'teacher' ? 'Somente horários livres para este instrutor e este veículo podem ser salvos.' : 'Somente professores e alunos compatíveis com esta categoria entram no fluxo principal.' }}</p>
                </div>
            </div>
        </div>

        @if ($scheduleMode === 'teacher' && $selectedTeacher)
            <div class="teacher-focus-banner">
                <span>Instrutor selecionado</span>
                <strong>{{ $selectedTeacher->nome }}</strong>
                <small>Veículo {{ strtoupper($selectedVehicle->placa) }} - categoria {{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</small>
            </div>
        @endif

        <div class="vehicle-summary">
            @if ($scheduleMode === 'teacher' && $selectedTeacher)
                <span class="vehicle-chip">Instrutor {{ $selectedTeacher->nome }}</span>
            @endif
            <span class="vehicle-chip">Placa {{ strtoupper($selectedVehicle->placa) }}</span>
            <span class="vehicle-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
        </div>

        <div class="agenda-week-nav">
            <a class="btn-secondary" data-agenda-nav href="{{ route('appointments.index', array_merge($agendaRouteParams, ['week_start' => $weekStart->copy()->subWeek()->toDateString()])) }}">Semana anterior</a>
            <span><strong>{{ strtoupper($selectedVehicle->placa) }}</strong> - {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
            <a class="btn-secondary" data-agenda-nav href="{{ route('appointments.index', array_merge($agendaRouteParams, ['week_start' => $weekStart->copy()->addWeek()->toDateString()])) }}">Próxima semana</a>
        </div>

        <div class="agenda-grid-wrap">
            <table class="agenda-grid">
                <thead>
                    <tr>
                        <th class="agenda-time">Horário</th>
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
                                    $appointment = $appointmentsBySlot->get($slotKey);
                                    $slotAppointments = $slotAppointmentsBySlot->get($slotKey, collect());
                                @endphp
                                <td class="agenda-slot-cell" data-slot-key="{{ $slotKey }}">
                                    @include('appointments._slot_cell', [
                                        'appointment' => $appointment,
                                        'slotAppointments' => $slotAppointments,
                                        'day' => $day,
                                        'slot' => $slot,
                                        'selectedVehicle' => $selectedVehicle,
                                        'teachers' => $teachers,
                                        'students' => $students,
                                        'busyTeacherIdsBySlot' => $busyTeacherIdsBySlot,
                                        'busyStudentIdsBySlot' => $busyStudentIdsBySlot,
                                        'vehicleCategoryFilter' => $vehicleCategoryFilter,
                                        'studentCategoryLabels' => $studentCategoryLabels,
                                        'scheduleMode' => $scheduleMode,
                                        'selectedTeacher' => $selectedTeacher,
                                    ])
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-agenda">
            @if ($scheduleMode === '')
                <strong>Escolha como deseja montar a agenda.</strong>
                <p>Selecione se a visualização será por veículo ou por instrutor para liberar os próximos filtros.</p>
            @elseif ($scheduleMode === 'teacher' && ! $selectedTeacher)
                <strong>Selecione um instrutor.</strong>
                <p>Depois escolha o veículo para abrir a grade semanal deste instrutor.</p>
            @elseif (! $selectedVehicle)
                <strong>Selecione um veículo.</strong>
                <p>{{ $scheduleMode === 'teacher' ? 'O veículo será combinado com o instrutor escolhido antes de exibir a grade.' : 'A grade de horários só aparece depois que um veículo for selecionado.' }}</p>
            @else
                <strong>Nenhum veículo encontrado.</strong>
                <p>Ajuste o filtro de categoria ou cadastre veículos para montar a agenda semanal.</p>
            @endif
        </div>
    @endif
</div>
