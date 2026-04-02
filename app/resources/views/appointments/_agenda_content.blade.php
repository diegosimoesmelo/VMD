@if (session('success'))
    <p class="notice notice-success">{{ session('success') }}</p>
@endif

@if ($errors->any())
    <p class="notice notice-error">{{ $errors->first() }}</p>
@endif

<div class="surface-card section-card">
    <div class="agenda-toolbar">
        <form method="GET" action="{{ route('appointments.index') }}" data-agenda-filter-form>
            <div class="field-inline">
                <label for="vehicle_category">Categoria do veiculo</label>
                <select id="vehicle_category" name="vehicle_category">
                    <option value="">Todas</option>
                    @foreach ($vehicleCategoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected($vehicleCategoryFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-inline">
                <label for="vehicle">Veiculo</label>
                <select id="vehicle" name="vehicle" required>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected($selectedVehicle?->id === $vehicle->id)>
                            {{ strtoupper($vehicle->placa) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field-inline">
                <label for="week_start">Semana de referencia</label>
                <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
            </div>
            <button class="btn" type="submit">Carregar agenda</button>
        </form>
    </div>

    @if ($selectedVehicle)
        <div class="vehicle-hero">
            <div class="vehicle-hero-card">
                <span class="eyebrow">Veiculo em foco</span>
                <h2>Voce esta montando a agenda deste veiculo</h2>
                <div class="vehicle-plate">{{ strtoupper($selectedVehicle->placa) }}</div>
                <p>Todos os horarios desta grade pertencem exclusivamente a este veiculo. Qualquer aula marcada aqui reserva esta placa para o horario selecionado.</p>
                <div class="vehicle-hero-meta">
                    <span class="vehicle-hero-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
                    <span class="vehicle-hero-chip">Semana {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="vehicle-hero-side">
                <div class="vehicle-focus-card">
                    <span class="eyebrow">Leitura Rapida</span>
                    <strong>{{ strtoupper($selectedVehicle->placa) }}</strong>
                    <p>Use essa referencia antes de salvar qualquer aula para evitar marcar no carro errado.</p>
                </div>
                <div class="vehicle-focus-card">
                    <span class="eyebrow">Categoria</span>
                    <strong>{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</strong>
                    <p>Somente professores e alunos compativeis com esta categoria entram no fluxo principal.</p>
                </div>
            </div>
        </div>

        <div class="vehicle-summary">
            <span class="vehicle-chip">Placa {{ strtoupper($selectedVehicle->placa) }}</span>
            <span class="vehicle-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
        </div>

        <div class="agenda-week-nav">
            <a class="btn-secondary" data-agenda-nav href="{{ route('appointments.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}">Semana anterior</a>
            <span><strong>{{ strtoupper($selectedVehicle->placa) }}</strong> - {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
            <a class="btn-secondary" data-agenda-nav href="{{ route('appointments.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}">Proxima semana</a>
        </div>

        <div class="agenda-grid-wrap">
            <table class="agenda-grid">
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
                                    $appointment = $appointmentsBySlot->get($slotKey);
                                @endphp
                                <td class="agenda-slot-cell" data-slot-key="{{ $slotKey }}">
                                    @include('appointments._slot_cell', [
                                        'appointment' => $appointment,
                                        'day' => $day,
                                        'slot' => $slot,
                                        'selectedVehicle' => $selectedVehicle,
                                        'teachers' => $teachers,
                                        'students' => $students,
                                        'busyTeacherIdsBySlot' => $busyTeacherIdsBySlot,
                                        'busyStudentIdsBySlot' => $busyStudentIdsBySlot,
                                        'vehicleCategoryFilter' => $vehicleCategoryFilter,
                                        'studentCategoryLabels' => $studentCategoryLabels,
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
            <strong>Nenhum veiculo encontrado.</strong>
            <p>Ajuste o filtro de categoria ou cadastre veiculos para montar a agenda semanal.</p>
        </div>
    @endif
</div>
