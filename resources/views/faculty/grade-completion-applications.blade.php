@extends('layouts.faculty')

@section('page-title', 'Grade Completion Applications')

@section('content')
<div class="space-y-6">
    
    @if($applications->count() > 0)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-user mr-2"></i>Student
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-book mr-2"></i>Subject
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-grade mr-2"></i>Current Grade
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-file mr-2"></i>Document
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-calendar mr-2"></i>Dean Approved
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-hourglass-half mr-2"></i>Deadline
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-check-circle mr-2"></i>Dean Status
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-cog mr-2"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($applications as $index => $application)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($application->student->first_name, 0, 1)) }}{{ strtoupper(substr($application->student->last_name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $application->student->first_name }} {{ $application->student->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $application->student->student_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $application->subject->code }}</div>
                                    <div class="text-sm text-gray-500">{{ $application->subject->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $application->current_grade }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($application->supporting_document)
                                        <div class="flex flex-col items-center space-y-1">
                                            <a href="/faculty/grade-completion-applications/{{ $application->id }}/document" 
                                               target="_blank" 
                                               class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors duration-200 text-xs">
                                                <i class="fas fa-file mr-1"></i>
                                                View
                                            </a>
                                            <span class="text-xs text-gray-500">
                                                {{ pathinfo($application->supporting_document, PATHINFO_EXTENSION) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No document</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium">{{ $application->dean_reviewed_at->format('M j, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $application->dean_reviewed_at->format('g:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    @if($application->completion_deadline)
                                        @php
                                            $deadline = $application->completion_deadline;
                                            $now = now();
                                            $daysUntil = (int) $now->diffInDays($deadline, false);
                                            $isOverdue = $now->isAfter($deadline);
                                            $deadlineTimestamp = $deadline->timestamp * 1000; // Convert to milliseconds for JavaScript
                                        @endphp
                                        
                                        <div class="flex flex-col items-center">
                                            <span class="font-medium {{ $isOverdue ? 'text-red-600' : ($daysUntil <= 7 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ $application->completion_deadline->format('M j, Y') }}
                                            </span>
                                            
                                            @if(!$isOverdue)
                                                <!-- Real-time countdown timer -->
                                                <div class="text-xs mt-1 font-mono" 
                                                     data-deadline="{{ $deadlineTimestamp }}" 
                                                     data-application-id="{{ $application->id }}"
                                                     id="countdown-faculty-{{ $application->id }}">
                                                    <span class="countdown-timer">Loading...</span>
                                                </div>
                                            @else
                                                <span class="text-xs text-red-500 font-medium">
                                                    {{ abs($daysUntil) }} day{{ abs($daysUntil) != 1 ? 's' : '' }} overdue
                                                </span>
                                            @endif
                                            
                                            <span class="text-xs text-gray-500 mt-1">
                                                {{ $application->completion_deadline->format('g:i A') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No deadline set</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center space-y-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $application->dean_reviewed_at->format('M j, Y') }}
                                        </span>
                                    </div>
                                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex flex-col items-center space-y-2">
                        <button onclick="viewApplication({{ $application->id }})" 
                                class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-lg hover:bg-indigo-200 transition-colors duration-200 text-xs font-medium">
                            <i class="fas fa-eye mr-1"></i>
                            View Details
                        </button>
                    </div>
                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg p-12">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-alt text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No Applications Available</h3>
                <p class="text-gray-600 mb-6">There are no grade completion applications approved by the Dean at this time.</p>
                <a href="/faculty/dashboard" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Application Details Modal -->
<div id="applicationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeApplicationModal()"></div>
        
        <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-file-alt mr-3"></i>
                        Application Details
                    </h3>
                    <button onclick="closeApplicationModal()" class="text-white hover:text-gray-200 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6">
                <div id="applicationDetails" class="space-y-6">
                    <!-- Application details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentApplicationId = null;

function viewApplication(applicationId) {
    currentApplicationId = applicationId;
    
    // Show loading state
    document.getElementById('applicationDetails').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="text-gray-600 mt-2">Loading application details...</p></div>';
    document.getElementById('applicationModal').classList.remove('hidden');
    
    // Fetch application details
    fetch(`/faculty/grade-completion-applications/${applicationId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplicationDetails(data.application);
            } else {
                showAlert('Error loading application details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading application details', 'error');
        });
}

function displayApplicationDetails(application) {
    const detailsHtml = `
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-green-800">Approved by Dean</h4>
                        <p class="text-sm text-green-700">This application has been approved and digitally signed by the Dean</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-thumbs-up mr-1"></i>
                    Dean Approved
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-2"></i>Student Information
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Name:</span>
                        <span class="text-sm font-medium text-gray-800">${application.student_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Student ID:</span>
                        <span class="text-sm font-medium text-gray-800">${application.student_id}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-book text-purple-600 mr-2"></i>Subject Information
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subject Code:</span>
                        <span class="text-sm font-medium text-gray-800">${application.subject_code}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subject Name:</span>
                        <span class="text-sm font-medium text-gray-800">${application.subject_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Current Grade:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            ${application.current_grade}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-edit text-yellow-600 mr-2"></i>Application Details
            </h4>
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-700">Submitted:</span>
                    <span class="text-sm text-gray-600 ml-2">${application.created_at}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Dean Approved:</span>
                    <span class="text-sm text-gray-600 ml-2">${application.dean_reviewed_at}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Reason for Application:</span>
                    <div class="mt-2 p-3 bg-white border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">${application.reason}</p>
                    </div>
                </div>
                ${application.dean_remarks ? `
                <div>
                    <span class="text-sm font-medium text-gray-700">Dean's Remarks:</span>
                    <div class="mt-2 p-3 bg-white border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">${application.dean_remarks}</p>
                    </div>
                </div>
                ` : ''}
                ${application.supporting_document ? `
                <div>
                    <span class="text-sm font-medium text-gray-700">Supporting Document:</span>
                    <div class="mt-2 flex items-center space-x-3">
                        <a href="/faculty/grade-completion-applications/${application.id}/document" target="_blank" class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors duration-200 text-sm">
                            <i class="fas fa-file mr-2"></i>
                            View Document
                        </a>
                        <a href="/faculty/grade-completion-applications/${application.id}/document" download class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors duration-200 text-sm">
                            <i class="fas fa-download mr-2"></i>
                            Download
                        </a>
                    </div>
                </div>
                ` : '<div class="text-sm text-gray-500 flex items-center"><i class="fas fa-info-circle mr-2"></i>No supporting document submitted</div>'}
            </div>
        </div>
        
        <!-- Dean's Digital Signature Section -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>Dean's Approval Status
            </h4>
            <div class="bg-white border-2 border-green-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Status:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        APPROVED
                    </span>
                </div>
                <div class="border-t border-gray-200 pt-3">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Approved by:</span>
                            <span class="font-medium text-gray-800">Dean's Office</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Approved on:</span>
                            <span class="font-medium text-gray-800">${application.dean_reviewed_at || 'N/A'}</span>
                        </div>
                        ${application.dean_remarks ? `
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Dean's Remarks:</span>
                                <p class="text-sm text-gray-800 mt-1 bg-gray-50 p-2 rounded">${application.dean_remarks}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
            <button onclick="viewSignedDocument(${application.id})" 
                    class="px-6 py-3 bg-purple-100 text-purple-800 rounded-lg hover:bg-purple-200 transition-colors duration-200 font-medium">
                <i class="fas fa-file-signature mr-2"></i>
                View Signed Document
            </button>
        </div>
    `;
    
    document.getElementById('applicationDetails').innerHTML = detailsHtml;
}

function viewSignedDocument(applicationId) {
    window.open(`/faculty/grade-completion-applications/${applicationId}/signed-document`, '_blank');
}

function closeApplicationModal() {
    document.getElementById('applicationModal').classList.add('hidden');
    currentApplicationId = null;
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    alert.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Countdown Timer Functions
function updateCountdownTimers() {
    const timers = document.querySelectorAll('[data-deadline]');
    const now = new Date().getTime();
    
    timers.forEach(timer => {
        const deadline = parseInt(timer.getAttribute('data-deadline'));
        const timeLeft = deadline - now;
        
        if (timeLeft > 0) {
            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            let timeString = '';
            let colorClass = '';
            
            if (days > 0) {
                timeString = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                if (days <= 1) {
                    colorClass = 'text-orange-600 font-bold';
                } else if (days <= 7) {
                    colorClass = 'text-yellow-600 font-semibold';
                } else if (days <= 30) {
                    colorClass = 'text-blue-600';
                } else {
                    colorClass = 'text-green-600';
                }
            } else if (hours > 0) {
                timeString = `${hours}h ${minutes}m ${seconds}s`;
                colorClass = 'text-orange-600 font-bold animate-pulse';
            } else if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
                colorClass = 'text-red-600 font-bold animate-pulse';
            } else {
                timeString = `${seconds}s`;
                colorClass = 'text-red-600 font-bold animate-pulse';
            }
            
            const timerElement = timer.querySelector('.countdown-timer');
            if (timerElement) {
                timerElement.textContent = timeString;
                timerElement.className = `countdown-timer ${colorClass}`;
            }
        } else {
            // Time's up!
            const timerElement = timer.querySelector('.countdown-timer');
            if (timerElement) {
                timerElement.textContent = 'EXPIRED';
                timerElement.className = 'countdown-timer text-red-600 font-bold animate-pulse';
            }
        }
    });
}

// Start the countdown timers
document.addEventListener('DOMContentLoaded', function() {
    updateCountdownTimers();
    // Update every second
    setInterval(updateCountdownTimers, 1000);
});
</script>

<style>
/* Countdown Timer Styling */
.countdown-timer {
    display: inline-block;
    padding: 2px 6px;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    font-family: 'Courier New', 'Monaco', monospace;
    font-size: 11px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.countdown-timer.animate-pulse {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Urgency-based styling */
.countdown-timer.text-red-600 {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.countdown-timer.text-orange-600 {
    background-color: rgba(234, 88, 12, 0.1);
    border: 1px solid rgba(234, 88, 12, 0.3);
}

.countdown-timer.text-yellow-600 {
    background-color: rgba(202, 138, 4, 0.1);
    border: 1px solid rgba(202, 138, 4, 0.3);
}
</style>

@endsection
