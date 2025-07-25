@extends('layouts.admin')

@section('page-title', 'User Management')

@section('content')
<!-- Page Header -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">User Management</h1>
            <p class="text-gray-600">Manage all system users and their access levels</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Add New User
            </button>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
            <div class="relative">
                <input type="text" placeholder="Search by name, email, or ID..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Role</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Roles</option>
                <option value="student">Students</option>
                <option value="faculty">Faculty</option>
                <option value="dean">Dean</option>
                <option value="admin">Administrators</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
            <div class="flex gap-2">
                <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <button class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-redo mr-2"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Activity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="bg-{{ $user->role === 'admin' ? 'red' : ($user->role === 'dean' ? 'purple' : ($user->role === 'faculty' ? 'green' : 'blue')) }}-100 p-2 rounded-full mr-3">
                                <i class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : ($user->role === 'dean' ? 'user-tie' : ($user->role === 'faculty' ? 'chalkboard-teacher' : 'user-graduate')) }} text-{{ $user->role === 'admin' ? 'red' : ($user->role === 'dean' ? 'purple' : ($user->role === 'faculty' ? 'green' : 'blue')) }}-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->student_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $user->role === 'admin' ? 'red' : ($user->role === 'dean' ? 'purple' : ($user->role === 'faculty' ? 'green' : 'blue')) }}-100 text-{{ $user->role === 'admin' ? 'red' : ($user->role === 'dean' ? 'purple' : ($user->role === 'faculty' ? 'green' : 'blue')) }}-800">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        <div class="text-sm text-gray-500">{{ $user->phone ?? 'No phone' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-2">
                            <button class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($user->role !== 'admin')
                            <button class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4"></i>
                        <p class="text-lg font-medium">No users found</p>
                        <p>Try adjusting your search criteria</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($users->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- User Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="bg-blue-100 p-3 rounded-full inline-block mb-4">
            <i class="fas fa-user-graduate text-blue-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Students</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $users->where('role', 'student')->count() }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="bg-green-100 p-3 rounded-full inline-block mb-4">
            <i class="fas fa-chalkboard-teacher text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Faculty</h3>
        <p class="text-3xl font-bold text-green-600">{{ $users->where('role', 'faculty')->count() }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="bg-purple-100 p-3 rounded-full inline-block mb-4">
            <i class="fas fa-user-tie text-purple-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Dean</h3>
        <p class="text-3xl font-bold text-purple-600">{{ $users->where('role', 'dean')->count() }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="bg-red-100 p-3 rounded-full inline-block mb-4">
            <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Admins</h3>
        <p class="text-3xl font-bold text-red-600">{{ $users->where('role', 'admin')->count() }}</p>
    </div>
</div>
@endsection
