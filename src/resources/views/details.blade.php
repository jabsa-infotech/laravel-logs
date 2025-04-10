@extends(config('laravel-logs.layout'))

@section('content')
    {{-- Breadcrumb Navigation --}}
    <nav class="flex mb-3" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
            <li class="inline-flex items-center">
                <a href="/"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-500">System Logs</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header with Controls --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-semibold text-gray-900">System Logs</h1>
                <div class="px-3 py-1 rounded-full text-sm bg-blue-50 text-blue-700">
                    {{ now()->format('Y-m-d') }}
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" 
                               id="logSearch" 
                               placeholder="Search logs..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <select id="logLevel" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Levels</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>

                @can(config('laravel-logs.clear_permission'))
                    <form action="{{ route(config('laravel-logs.route_name_prefix').'.destroy') }}" 
                          method="post" 
                          onsubmit="return confirm('Are you sure you want to clear all logs?')">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Logs
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Log Viewer --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <div id="logViewer" class="divide-y divide-gray-100" style="max-height: 800px; overflow-y: auto;">
                    @foreach(explode("\n", $logContent) as $index => $line)
                        @php
                            $logLevel = 'info';
                            if (str_contains(strtolower($line), 'error')) {
                                $logLevel = 'error';
                            } elseif (str_contains(strtolower($line), 'warning')) {
                                $logLevel = 'warning';
                            } elseif (str_contains(strtolower($line), 'debug')) {
                                $logLevel = 'debug';
                            }

                            // Check if line contains JSON
                            $hasJson = preg_match('/{.*}/s', $line);
                        @endphp

                        <div class="log-line" 
                             data-content="{{ htmlspecialchars($line) }}"
                             data-level="{{ $logLevel }}">
                            <div class="p-3 font-mono text-sm hover:bg-gray-50">
                                <div class="flex items-start gap-4">
                                    {{-- Line Number --}}
                                    <div class="text-gray-400 select-none w-12 text-right">
                                        {{ $index + 1 }}
                                    </div>

                                    {{-- Log Content --}}
                                    <div class="flex-1">
                                        @if($hasJson)
                                            @php
                                                // Extract timestamp and message
                                                preg_match('/\[(.*?)\].*?:(.+)({.*})/s', $line, $matches);
                                                $timestamp = $matches[1] ?? '';
                                                $message = trim($matches[2] ?? '');
                                                $json = $matches[3] ?? '';
                                            @endphp
                                            
                                            {{-- Timestamp --}}
                                            <span class="text-gray-500">
                                                [{{ $timestamp }}]
                                            </span>
                                            
                                            {{-- Message --}}
                                            <span class="ml-2">{{ $message }}</span>
                                            
                                            {{-- JSON --}}
                                            <div class="mt-2 p-2 bg-gray-50 rounded-lg overflow-x-auto">
                                                <pre class="text-xs whitespace-pre-wrap break-words">{{ $json }}</pre>
                                            </div>
                                        @else
                                            {{ $line }}
                                        @endif
                                    </div>

                                    {{-- Copy Button --}}
                                    <button onclick="copyToClipboard(this)" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 rounded">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logViewer = document.getElementById('logViewer');
            const searchInput = document.getElementById('logSearch');
            const levelSelect = document.getElementById('logLevel');
            const logLines = document.querySelectorAll('.log-line');
            let searchDebounceTimer;
    
            // Filter logs based on search and level
            function filterLogs() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedLevel = levelSelect.value.toLowerCase();
    
                logLines.forEach(line => {
                    const content = line.getAttribute('data-content').toLowerCase();
                    const level = line.getAttribute('data-level');
                    
                    const matchesSearch = searchTerm === '' || content.includes(searchTerm);
                    const matchesLevel = selectedLevel === 'all' || level === selectedLevel;
                    
                    if (matchesSearch && matchesLevel) {
                        line.classList.remove('hidden');
                        
                        // Reset background classes
                        line.classList.remove('bg-red-50', 'bg-yellow-50', 'bg-blue-50', 'bg-gray-50');
                        
                        // Apply appropriate background
                        switch(level) {
                            case 'error':
                                line.classList.add('bg-red-50');
                                break;
                            case 'warning':
                                line.classList.add('bg-yellow-50');
                                break;
                            case 'info':
                                line.classList.add('bg-blue-50');
                                break;
                            case 'debug':
                                line.classList.add('bg-gray-50');
                                break;
                        }
                    } else {
                        line.classList.add('hidden');
                    }
                });
            }
    
            // Debounce function for search
            function debounce(func, wait) {
                return function executedFunction() {
                    const later = () => {
                        clearTimeout(searchDebounceTimer);
                        func();
                    };
                    clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = setTimeout(later, wait);
                };
            }
    
            // Event listeners with debounced search
            searchInput.addEventListener('input', debounce(() => filterLogs(), 300));
            levelSelect.addEventListener('change', filterLogs);
    
            // Click to copy functionality
            logLines.forEach(line => {
                line.addEventListener('click', () => {
                    const content = line.getAttribute('data-content');
                    navigator.clipboard.writeText(content)
                        .then(() => showToast('Copied to clipboard'))
                        .catch(() => showToast('Failed to copy', true));
                });
            });
    
            // Toast notification helper
            function showToast(message, isError = false) {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg transition-opacity duration-200 opacity-0 
                                 ${isError ? 'bg-red-600' : 'bg-gray-900'} text-white`;
                toast.textContent = message;
                document.body.appendChild(toast);
    
                // Fade in
                requestAnimationFrame(() => toast.classList.add('opacity-100'));
    
                // Fade out and remove
                setTimeout(() => {
                    toast.classList.remove('opacity-100');
                    setTimeout(() => toast.remove(), 200);
                }, 2000);
            }
    
            // Initial filter application
            filterLogs();
    
            // Scroll to bottom on load
            logViewer.scrollTop = logViewer.scrollHeight;
        });

        function copyToClipboard(button) {
            const logLine = button.closest('.log-line');
            const content = logLine.dataset.content;
            
            navigator.clipboard.writeText(content).then(() => {
                // Show feedback
                const originalSvg = button.innerHTML;
                button.innerHTML = `
                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
                
                setTimeout(() => {
                    button.innerHTML = originalSvg;
                }, 2000);
            });
        }
    </script>

@endsection