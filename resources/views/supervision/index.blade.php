<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50 text-slate-800 antialiased">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Panel de Supervisión · VendeComoPro</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/vcp-logo.png') }}">

  <!-- Tailwind Play CDN for high-fidelity styling -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#fff7ed',
              100: '#ffedd5',
              500: '#f97316',
              600: '#ea580c',
              700: '#c2410c',
            }
          }
        }
      }
    }
  </script>
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

  <style>
    /* Custom scrollbars */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  </style>
</head>
<body class="h-full flex flex-col selection:bg-orange-500 selection:text-white bg-slate-50">

  <!-- Header / Topbar (Clean Light) -->
  <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-30 px-4 lg:px-8 py-3.5 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <img src="{{ asset('assets/vcp-logo.png') }}" alt="VCP" class="h-8 w-auto">
      </div>

      <div class="flex items-center gap-3">
        <a href="{{ route('supervision.export', ['token' => $currentToken]) }}" 
           class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-lg bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 border border-slate-300 shadow-sm transition">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Exportar CSV
        </a>

        <a href="{{ route('ruta') }}" target="_blank" 
           class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-lg bg-orange-600 hover:bg-orange-500 text-white transition shadow-sm shadow-orange-500/20">
          <span>Abrir Ruta</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="flex-1 max-w-7xl w-full mx-auto px-4 lg:px-8 py-6 space-y-6">

    <!-- KPI Cards: Row 1 (4 Main Overview Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
      
      <!-- Total Students -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-sm hover:border-slate-300 transition">
        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Estudiantes</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kpis['total']) }}</span>
          <span class="text-xs text-slate-400 font-medium">100% total</span>
        </div>
        <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between font-medium">
          <span>USA: {{ $kpis['usa'] }}</span>
          <span>Fuera: {{ $kpis['outside'] }}</span>
        </div>
      </div>

      <!-- Avg Progress -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-sm hover:border-slate-300 transition">
        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Avance Promedio</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl font-black text-amber-600 tracking-tight">{{ $kpis['avg_progress'] }}%</span>
          <span class="text-xs text-amber-600 font-medium">global</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
          <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $kpis['avg_progress'] }}%"></div>
        </div>
      </div>

      <!-- Completed -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-sm hover:border-slate-300 transition">
        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Graduados</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl font-black text-emerald-600 tracking-tight">{{ number_format($kpis['completed']) }}</span>
          <span class="text-xs font-semibold text-emerald-600">
            {{ $kpis['total'] > 0 ? round(($kpis['completed'] / $kpis['total']) * 100, 1) : 0 }}%
          </span>
        </div>
        <div class="mt-2 text-[11px] text-emerald-700 font-medium">
          Completaron 12/12
        </div>
      </div>

      <!-- Active -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-sm hover:border-slate-300 transition">
        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Activos</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl font-black text-sky-600 tracking-tight">{{ number_format($kpis['active_recent']) }}</span>
          <span class="text-xs font-semibold text-sky-600">
            {{ $kpis['total'] > 0 ? round(($kpis['active_recent'] / $kpis['total']) * 100, 1) : 0 }}%
          </span>
        </div>
        <div class="mt-2 text-[11px] text-sky-700 font-medium">
          Conexión reciente (&le;48h)
        </div>
      </div>

    </div>

    <!-- KPI Cards: Row 2 (2 Retention / Attention Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:gap-4">
      
      <!-- Warning 3-7d -->
      <div class="bg-amber-50/40 border border-amber-200/80 rounded-xl p-4 flex items-center justify-between shadow-sm hover:border-amber-300 transition">
        <div>
          <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Pausados
          </span>
          <p class="text-xs text-amber-700 mt-1 font-medium">Sin conexión entre 3 y 7 días</p>
        </div>
        <div class="text-right">
          <span class="text-2xl font-black text-amber-600 tracking-tight">{{ number_format($kpis['warning']) }}</span>
          <span class="block text-[11px] text-amber-700 font-semibold">atención</span>
        </div>
      </div>

      <!-- Critical Inactive > 7d -->
      <div class="bg-rose-50/60 border border-rose-200 rounded-xl p-4 flex items-center justify-between shadow-sm hover:border-rose-300 transition">
        <div>
          <span class="text-[11px] font-semibold text-rose-800 uppercase tracking-wider flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            Críticos
          </span>
          <p class="text-xs text-rose-700 mt-1 font-medium">Sin actividad hace más de 7 días</p>
        </div>
        <div class="text-right">
          <span class="text-2xl font-black text-rose-600 tracking-tight">{{ number_format($kpis['critical_inactive']) }}</span>
          <span class="block text-[11px] text-rose-700 font-semibold">riesgo</span>
        </div>
      </div>

    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <!-- Chart 1: Estudiantes por Estación (Funnel / Bar) -->
      <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-sm font-bold text-slate-900">Concentración de Estudiantes por Estación</h2>
            <p class="text-xs text-slate-500">Identifica en qué estación se encuentran o se detienen los alumnos.</p>
          </div>
          <span class="text-xs font-semibold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md border border-slate-200">12 Estaciones</span>
        </div>
        <div class="h-64">
          <canvas id="stationsChart"></canvas>
        </div>
      </div>

      <!-- Chart 2: Semáforo de Actividad (Donut) -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-900">Semáforo de Actividad</h2>
            <p class="text-xs text-slate-500">Estado de conexión de los estudiantes</p>
          </div>
        </div>
        <div class="flex-1 flex items-center justify-center relative min-h-[200px]">
          <canvas id="activityChart"></canvas>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs">
          <div>
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1 align-middle"></span>
            <span class="text-slate-500 font-medium">Activos</span>
            <p class="font-bold text-slate-900 mt-0.5">{{ $kpis['active_recent'] }}</p>
          </div>
          <div>
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 mr-1 align-middle"></span>
            <span class="text-slate-500 font-medium">Pausados</span>
            <p class="font-bold text-slate-900 mt-0.5">{{ $kpis['warning'] }}</p>
          </div>
          <div>
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500 mr-1 align-middle"></span>
            <span class="text-slate-500 font-medium">Críticos</span>
            <p class="font-bold text-slate-900 mt-0.5">{{ $kpis['critical_inactive'] }}</p>
          </div>
        </div>
      </div>

    </div>

    <!-- Student Table Section -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      
      <!-- Filter Bar -->
      <div class="p-4 border-b border-slate-200 space-y-3 bg-slate-50/50">
        <form id="filter-form" method="GET" action="{{ route('supervision.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
          @if($currentToken)
            <input type="hidden" name="token" value="{{ $currentToken }}">
          @endif

          <!-- Status Tabs -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <button type="submit" name="status_tab" value="all" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $filters['status_tab'] === 'all' ? 'bg-orange-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
              Todos ({{ $kpis['total'] }})
            </button>
            <button type="submit" name="status_tab" value="active_recent" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $filters['status_tab'] === 'active_recent' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
              🟢 Activos ({{ $kpis['active_recent'] }})
            </button>
            <button type="submit" name="status_tab" value="warning" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $filters['status_tab'] === 'warning' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
              🟡 Pausados ({{ $kpis['warning'] }})
            </button>
            <button type="submit" name="status_tab" value="critical" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $filters['status_tab'] === 'critical' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
              🔴 Críticos ({{ $kpis['critical_inactive'] }})
            </button>
            <button type="submit" name="status_tab" value="completed" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $filters['status_tab'] === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
              🏆 Graduados ({{ $kpis['completed'] }})
            </button>
          </div>

          <!-- Selects & Search -->
          <div class="flex items-center gap-2 flex-wrap md:flex-nowrap">
            <!-- Search -->
            <div class="relative flex-1 md:w-56">
              <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Buscar nombre o correo..."
                class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-sm">
              <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Station Filter -->
            <select name="station" onchange="document.getElementById('filter-form').submit()"
              class="bg-white border border-slate-300 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-sm">
              <option value="all">Todas las estaciones</option>
              @foreach($stationTitles as $idx => $title)
                <option value="{{ $idx }}" {{ (string)$filters['station'] === (string)$idx ? 'selected' : '' }}>
                  Estación {{ $idx }}: {{ Str::limit($title, 20) }}
                </option>
              @endforeach
            </select>

            <!-- System Filter -->
            <select name="system" onchange="document.getElementById('filter-form').submit()"
              class="bg-white border border-slate-300 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-sm">
              <option value="all">Sistema</option>
              <option value="medium" {{ $filters['system'] === 'medium' ? 'selected' : '' }}>Medium</option>
              <option value="elite" {{ $filters['system'] === 'elite' ? 'selected' : '' }}>Élite</option>
            </select>

            <!-- Residence Filter -->
            <select name="residence" onchange="document.getElementById('filter-form').submit()"
              class="bg-white border border-slate-300 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-sm">
              <option value="all">Residencia</option>
              <option value="usa" {{ $filters['residence'] === 'usa' ? 'selected' : '' }}>USA</option>
              <option value="outside" {{ $filters['residence'] === 'outside' ? 'selected' : '' }}>Fuera de USA</option>
            </select>

            @if($filters['search'] || $filters['station'] || $filters['system'] || $filters['residence'] || $filters['status_tab'] !== 'all')
              <a href="{{ route('supervision.index', ['token' => $currentToken]) }}" title="Limpiar filtros"
                class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-900 bg-white rounded-lg border border-slate-300 shadow-sm">
                ✕
              </a>
            @endif
          </div>
        </form>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
          <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200">
            <tr>
              <th class="py-3.5 px-4">Estudiante</th>
              <th class="py-3.5 px-4">Estación Actual</th>
              <th class="py-3.5 px-4">Progreso</th>
              <th class="py-3.5 px-4">Última Conexión</th>
              <th class="py-3.5 px-4">Sistema / Residencia</th>
              <th class="py-3.5 px-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($students as $st)
              @php
                $lastActive = $st->last_active_at;
                $daysInactive = $lastActive ? $lastActive->diffInDays(now()) : 999;
                
                $statusColor = 'text-rose-700 bg-rose-50 border-rose-200';
                $statusText = 'Crítico';
                if ($daysInactive <= 2) {
                    $statusColor = 'text-emerald-700 bg-emerald-50 border-emerald-200';
                    $statusText = 'Activo';
                } elseif ($daysInactive <= 7) {
                    $statusColor = 'text-amber-700 bg-amber-50 border-amber-200';
                    $statusText = 'Pausado';
                }
              @endphp
              <tr class="hover:bg-slate-50/80 transition group">
                <!-- Estudiante -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shadow-inner">
                      {{ strtoupper(substr($st->full_name ?? $st->email, 0, 2)) }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 flex items-center gap-2">
                        <span>{{ $st->full_name ?? 'Sin nombre registrado' }}</span>
                        @if($st->progress_percentage >= 100)
                          <span title="Graduado 100%">🏆</span>
                        @endif
                      </div>
                      <div class="text-[11px] text-slate-500 font-mono">{{ $st->email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Estación Actual -->
                <td class="py-3.5 px-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-orange-50 border border-orange-200 text-orange-900">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    Estación {{ $st->current_station ?? 1 }}: {{ Str::limit($stationTitles[$st->current_station ?? 1] ?? 'Inicio', 18) }}
                  </span>
                  <div class="text-[10px] text-slate-500 mt-1 pl-1 font-medium">
                    {{ $st->completed_stations ?? 0 }}/12 estaciones completas
                  </div>
                </td>

                <!-- Progreso -->
                <td class="py-3.5 px-4 w-44">
                  <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-bold {{ $st->progress_percentage >= 100 ? 'text-emerald-700' : 'text-slate-800' }}">
                      {{ $st->progress_percentage ?? 0 }}%
                    </span>
                    <span class="text-[10px] text-slate-500 font-medium">{{ $st->completed_tasks ?? 0 }} tareas</span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all {{ $st->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-orange-500 to-amber-500' }}" 
                         style="width: {{ min(100, max(0, $st->progress_percentage ?? 0)) }}%"></div>
                  </div>
                </td>

                <!-- Última Actividad -->
                <td class="py-3.5 px-4">
                  <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $statusColor }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    <span>{{ $lastActive ? $lastActive->diffForHumans() : 'Sin actividad' }}</span>
                  </div>
                  <div class="text-[10px] text-slate-500 mt-0.5 pl-1 font-medium">
                    {{ $lastActive ? $lastActive->format('d/m/Y H:i') : 'Nunca ingresó' }}
                  </div>
                </td>

                <!-- Sistema / Residencia -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-700 border border-slate-200">
                      {{ strtoupper($st->system ?? 'MEDIUM') }}
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $st->residence === 'usa' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                      {{ $st->residence === 'usa' ? 'USA' : 'Fuera USA' }}
                    </span>
                  </div>
                </td>

                <!-- Acciones -->
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Botón Inspeccionar -->
                    <button type="button" onclick="inspectStudent({{ $st->id }})" title="Ver detalle de misiones"
                      class="p-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200 shadow-sm transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>

                    <!-- Botón Generar Magic Link -->
                    <button type="button" onclick="generateMagicLink({{ $st->id }}, '{{ addslashes($st->email) }}')" title="Copiar enlace de acceso directo (Magic Link)"
                      class="p-1.5 rounded-lg bg-white hover:bg-orange-50 text-slate-600 hover:text-orange-600 border border-slate-200 hover:border-orange-300 shadow-sm transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                  <div class="text-3xl mb-2">🔍</div>
                  <p class="font-medium text-slate-600">No se encontraron estudiantes con los filtros seleccionados.</p>
                  <a href="{{ route('supervision.index', ['token' => $currentToken]) }}" class="text-orange-600 hover:underline text-xs mt-1 inline-block font-semibold">Limpiar filtros</a>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($students->hasPages())
        <div class="p-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600 bg-slate-50/50">
          <div>
            Mostrando del {{ $students->firstItem() }} al {{ $students->lastItem() }} de {{ $students->total() }} estudiantes
          </div>
          <div>
            {{ $students->links() }}
          </div>
        </div>
      @endif

    </div>

  </main>

  <!-- Modal Detalle Estudiante (Light) -->
  <div id="student-modal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
      <!-- Modal Header -->
      <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
          <h3 id="modal-name" class="text-base font-bold text-slate-900">Detalle de Estudiante</h3>
          <p id="modal-email" class="text-xs text-slate-500 font-mono"></p>
        </div>
        <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700 text-lg p-1">✕</button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-700">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
          <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase">Estación Actual</span>
            <p id="modal-station" class="text-sm font-bold text-slate-900 mt-0.5">-</p>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase">Progreso Total</span>
            <p id="modal-progress" class="text-sm font-bold text-orange-600 mt-0.5">-</p>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase">Última Actividad</span>
            <p id="modal-active" class="text-sm font-bold text-sky-600 mt-0.5">-</p>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase">Sistema / Residencia</span>
            <p id="modal-system" class="text-sm font-bold text-slate-800 mt-0.5">-</p>
          </div>
        </div>

        <!-- Task Checklist Completed -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Tareas y Misiones Marcadas</h4>
          <div id="modal-tasks-container" class="max-h-56 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1.5">
            <!-- Dynamic task rows -->
          </div>
        </div>

        <!-- Magic Link Section -->
        <div class="bg-orange-50/70 border border-orange-200 rounded-xl p-4 space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-bold text-orange-950 text-xs">Acceso Rápido para Soporte</span>
            <span class="text-[10px] font-semibold text-orange-700 bg-orange-100 px-2 py-0.5 rounded">Válido por 72 horas</span>
          </div>
          <p class="text-[11px] text-slate-600">Genera un enlace directo para que el alumno entre a su ruta sin requerir contraseña.</p>
          <div class="flex items-center gap-2 mt-2">
            <input type="text" id="modal-magic-url" readonly class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 font-mono select-all shadow-sm">
            <button type="button" id="modal-copy-btn" onclick="copyModalLink()" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
              Copiar Enlace
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold border border-slate-200 transition">
          Cerrar
        </button>
      </div>
    </div>
  </div>

  <!-- Toast Notification (Light) -->
  <div id="toast" class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-xl border border-slate-800 shadow-2xl text-xs font-medium transform translate-y-20 opacity-0 transition duration-300 flex items-center gap-2">
    <span class="text-emerald-400 font-bold">✓</span>
    <span id="toast-message">Mensaje copiado</span>
  </div>

  <!-- Charts & Interactive Scripts -->
  <script>
    // Initialize Charts
    const stationData = @json($charts['stations']);
    const activityData = @json($charts['activity']);

    // Stations Chart
    const ctxStation = document.getElementById('stationsChart').getContext('2d');
    new Chart(ctxStation, {
      type: 'bar',
      data: {
        labels: stationData.labels.map(l => l.split(':')[0]), // E1, E2...
        datasets: [{
          label: 'Estudiantes en esta estación',
          data: stationData.data,
          backgroundColor: '#ea580c',
          borderRadius: 6,
          hoverBackgroundColor: '#c2410c',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              title: (items) => stationData.labels[items[0].dataIndex] || '',
              label: (context) => ` ${context.parsed.y} estudiantes`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#64748b', stepSize: 1 },
            grid: { color: '#f1f5f9' }
          },
          x: {
            ticks: { color: '#475569', font: { weight: '600' } },
            grid: { display: false }
          }
        }
      }
    });

    // Activity Donut Chart
    const ctxActivity = document.getElementById('activityChart').getContext('2d');
    new Chart(ctxActivity, {
      type: 'doughnut',
      data: {
        labels: activityData.labels,
        datasets: [{
          data: activityData.data,
          backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => ` ${context.label}: ${context.raw} alumnos`
            }
          }
        }
      }
    });

    // Toast helper
    function showToast(msg) {
      const toast = document.getElementById('toast');
      document.getElementById('toast-message').innerText = msg;
      toast.classList.remove('translate-y-20', 'opacity-0');
      setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
      }, 3500);
    }

    // Inspect Student Modal
    async function inspectStudent(studentId) {
      const modal = document.getElementById('student-modal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      document.getElementById('modal-name').innerText = 'Cargando...';
      document.getElementById('modal-tasks-container').innerHTML = '<div class="text-slate-400 py-3 text-center">Consultando datos de progreso...</div>';
      document.getElementById('modal-magic-url').value = '';

      try {
        const res = await fetch(`/supervision/student/${studentId}`);
        const data = await res.json();

        if (data.success) {
          const st = data.student;
          document.getElementById('modal-name').innerText = st.full_name;
          document.getElementById('modal-email').innerText = st.email;
          document.getElementById('modal-station').innerText = `E${st.current_station}: ${st.current_station_title}`;
          document.getElementById('modal-progress').innerText = `${st.progress_percentage}% (${st.completed_stations}/12 estaciones)`;
          document.getElementById('modal-active').innerText = st.last_active_at;
          document.getElementById('modal-system').innerText = `${st.system} · ${st.residence}`;

          // Tasks list
          const container = document.getElementById('modal-tasks-container');
          if (data.completed_tasks.length === 0) {
            container.innerHTML = '<div class="text-slate-400 py-2">No ha completado tareas registradas aún.</div>';
          } else {
            container.innerHTML = data.completed_tasks.map(k => `
              <div class="flex items-center gap-2 py-1 px-2.5 rounded bg-white border border-slate-200">
                <span class="text-emerald-600 font-bold">✓</span>
                <span class="font-mono text-slate-700 text-[11px]">${k}</span>
              </div>
            `).join('');
          }

          // Generate magic link automatically for preview in modal
          fetch(`/supervision/magic-link/${studentId}`)
            .then(r => r.json())
            .then(ml => {
              if (ml.success) {
                document.getElementById('modal-magic-url').value = ml.magic_url;
              }
            });
        }
      } catch (e) {
        document.getElementById('modal-tasks-container').innerHTML = '<div class="text-rose-500 py-2">Error al cargar datos.</div>';
      }
    }

    function closeModal() {
      const modal = document.getElementById('student-modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function copyModalLink() {
      const input = document.getElementById('modal-magic-url');
      if (!input.value) return;
      navigator.clipboard.writeText(input.value);
      showToast('¡Enlace mágico copiado al portapapeles!');
    }

    async function generateMagicLink(studentId, email) {
      try {
        const res = await fetch(`/supervision/magic-link/${studentId}`);
        const data = await res.json();
        if (data.success && data.magic_url) {
          await navigator.clipboard.writeText(data.magic_url);
          showToast(`¡Enlace copiado para ${email}!`);
        } else {
          alert('No se pudo generar el enlace');
        }
      } catch (err) {
        alert('Error al generar enlace: ' + err.message);
      }
    }
  </script>
</body>
</html>
