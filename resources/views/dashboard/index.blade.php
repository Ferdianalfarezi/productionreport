@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Mesin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Mesin</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalMesin) }}</p>
            </div>
        </div>

        <!-- Total Line Machine -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Line Machine</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalLineMachine) }}</p>
            </div>
        </div>

        <!-- Total Line -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Line</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalLine) }}</p>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total User</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUser) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Line Stats Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Mesin per Line</h3>
                <p class="text-sm text-gray-500 mt-0.5">Distribusi mesin berdasarkan line</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Line</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Total Mesin</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Avg GSPH</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lineStats as $stat)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $stat->line ?: '-' }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ $stat->total }}</span>
                                </td>
                                <td class="px-6 py-3 text-center text-gray-600">{{ number_format($stat->avg_gsph, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada data mesin</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Updates -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Update Terakhir</h3>
                <p class="text-sm text-gray-500 mt-0.5">5 mesin yang terakhir diupdate</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentMesin as $mesin)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $mesin->machine_no }}</p>
                            <p class="text-xs text-gray-500">{{ $mesin->line_machine }} · {{ $mesin->line }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">{{ $mesin->update_by }}</p>
                            <p class="text-xs text-gray-400">{{ $mesin->update_time ? $mesin->update_time->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
