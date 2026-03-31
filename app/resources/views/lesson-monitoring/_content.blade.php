<div class="surface-card section-card">
    <div class="agenda-toolbar">
        <form method="GET" action="{{ route('lesson-monitoring.index') }}" data-lesson-monitoring-filter-form>
            <div class="field-inline">
                <label for="monitor_vehicle_category">Categoria do veiculo</label>
                <select id="monitor_vehicle_category" name="vehicle_category">
                    <option value="">Todas</option>
                    @foreach ($vehicleCategoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected($vehicleCategoryFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-inline">
                <label for="monitor_vehicle">Veiculo</label>
                <select id="monitor_vehicle" name="vehicle" required>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected($selectedVehicle?->id === $vehicle->id)>{{ strtoupper($vehicle->placa) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-inline">
                <label for="monitor_week_start">Semana de referencia</label>
                <input id="monitor_week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
            </div>
            <button class="btn" type="submit">Carregar grade</button>
        </form>
    </div>

    @if ($selectedVehicle)
        <div class="vehicle-summary">
            <span class="vehicle-chip">Veiculo {{ strtoupper($selectedVehicle->placa) }}</span>
            <span class="vehicle-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
            <span class="vehicle-chip">Semana {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
        </div>

        <div class="agenda-week-nav">
            <a class="btn-secondary" data-lesson-monitoring-nav href="{{ route('lesson-monitoring.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}">Semana anterior</a>
            <span><strong>{{ strtoupper($selectedVehicle->placa) }}</strong> - acompanhamento operacional</span>
            <a class="btn-secondary" data-lesson-monitoring-nav href="{{ route('lesson-monitoring.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}">Proxima semana</a>
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
                                <td class="lesson-monitoring-slot" data-slot-key="{{ $slotKey }}">
                                    @if ($appointment)
                                        @include('lesson-monitoring._slot_cell', [
                                            'appointment' => $appointment,
                                            'lessonStatusOptions' => $lessonStatusOptions,
                                            'vehicleCategoryFilter' => $vehicleCategoryFilter,
                                        ])
                                    @else
                                        <div class="monitor-slot-card empty">
                                            <span class="monitor-status empty">Sem aula</span>
                                            <div class="monitor-meta">
                                                <strong>Horario sem movimentacao</strong>
                                                <span>Nenhum apontamento necessario.</span>
                                            </div>
                                        </div>
                                    @endif
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
            <p>Ajuste os filtros para visualizar a grade operacional das aulas.</p>
        </div>
    @endif
</div>
