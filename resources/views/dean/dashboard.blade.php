@extends('layouts.dean')

@section('page-title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Welcome, Dean!</h1>
            <p class="text-gray-600">Administrative Overview</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2 sm:px-4 sm:py-2">
            <span class="text-sm font-medium text-green-800">{{ now()->format('F j, Y') }}</span>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <div class="flex items-center">
            <div class="bg-blue-100 p-3 rounded-full flex-shrink-0">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4 min-w-0">
                <h3 class="text-lg font-semibold text-gray-800 truncate">Students</h3>
                <p class="text-2xl font-bold text-blue-600">{{ $studentsCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <div class="flex items-center">
            <div class="bg-green-100 p-3 rounded-full flex-shrink-0">
                <i class="fas fa-chalkboard-teacher text-green-600 text-xl"></i>
            </div>
            <div class="ml-4 min-w-0">
                <h3 class="text-lg font-semibold text-gray-800 truncate">Faculty</h3>
                <p class="text-2xl font-bold text-green-600">{{ $facultyCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <div class="flex items-center">
            <div class="bg-green-100 p-3 rounded-full flex-shrink-0">
                <i class="fas fa-file-signature text-uc-green text-xl"></i>
            </div>
            <div class="ml-4 min-w-0">
                <h3 class="text-lg font-semibold text-gray-800 truncate">Pending Applications</h3>
                <p class="text-2xl font-bold text-uc-green">{{ $pendingApplicationsCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <div class="flex items-center">
            <div class="bg-orange-100 p-3 rounded-full flex-shrink-0">
                <i class="fas fa-bullhorn text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4 min-w-0">
                <h3 class="text-lg font-semibold text-gray-800 truncate">Announcements</h3>
                <p class="text-2xl font-bold text-orange-600">-</p>
            </div>
        </div>
    </div>
</div>

<!-- Deadline Monitoring -->
<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Grade Completion Deadlines</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Overdue Applications -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-red-100 rounded-full p-3 flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-800">Overdue</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $overdueCount }}</p>
                    <p class="text-sm text-red-600">Past deadline</p>
                </div>
            </div>
        </div>

        <!-- Approaching Deadline -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-3 flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-800">Approaching</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $approachingDeadlineCount }}</p>
                    <p class="text-sm text-yellow-600">Within 30 days</p>
                </div>
            </div>
        </div>

        <!-- Active Applications -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 flex-shrink-0">
                    <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-800">Active</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $activeCount }}</p>
                    <p class="text-sm text-green-600">Within deadline</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('dean.grade-completion-applications') }}" class="flex items-center p-3 sm:p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
            <i class="fas fa-file-alt text-uc-green text-lg sm:text-xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <span class="font-medium text-gray-800 block">Review Applications</span>
                @if($pendingApplicationsCount > 0)
                    <span class="inline-flex items-center px-2 py-1 mt-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $pendingApplicationsCount }} pending
                    </span>
                @endif
            </div>
        </a>
        
        <a href="{{ route('dean.approved-applications') }}" class="flex items-center p-3 sm:p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
            <i class="fas fa-check-circle text-green-600 text-lg sm:text-xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <span class="font-medium text-gray-800 block">Approved Applications</span>
                <span class="text-xs text-gray-500 block">View your signed applications</span>
            </div>
        </a>
        
        <a href="{{ route('dean.announcement') }}" class="flex items-center p-3 sm:p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
            <i class="fas fa-bullhorn text-blue-600 text-lg sm:text-xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <span class="font-medium text-gray-800 block">Post Announcement</span>
            </div>
        </a>
    </div>
</div>
@endsection
