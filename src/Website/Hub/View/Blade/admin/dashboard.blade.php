<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
    {{-- Welcome Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-100">Welcome to {{ config('app.name', 'Core PHP') }}</h1>
        <p class="text-zinc-400 mt-1">Your application is ready to use.</p>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-zinc-800/50 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-violet-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-400">Users</p>
                    <p class="text-2xl font-semibold text-zinc-100">{{ \Core\Tenant\Models\User::count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-zinc-800/50 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-400">Status</p>
                    <p class="text-2xl font-semibold text-green-400">Active</p>
                </div>
            </div>
        </div>

        <div class="bg-zinc-800/50 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-400">Laravel</p>
                    <p class="text-2xl font-semibold text-zinc-100">{{ app()->version() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-zinc-800/50 rounded-xl p-6 mb-8">
        <h2 class="text-lg font-semibold text-zinc-100 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ \Website\Hub\Boot::hubRoute('hub.account') }}" class="flex items-center gap-4 p-4 bg-zinc-900/50 rounded-lg hover:bg-zinc-700/50 transition">
                <div class="w-10 h-10 bg-violet-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-zinc-100">Your Profile</p>
                    <p class="text-sm text-zinc-400">Manage your account</p>
                </div>
            </a>

            <a href="/" class="flex items-center gap-4 p-4 bg-zinc-900/50 rounded-lg hover:bg-zinc-700/50 transition">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-zinc-100">View Site</p>
                    <p class="text-sm text-zinc-400">Go to homepage</p>
                </div>
            </a>
        </div>
    </div>

    {{-- User Info --}}
    <div class="bg-zinc-800/50 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-zinc-100 mb-4">Logged in as</h2>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-violet-600 rounded-full flex items-center justify-center text-white font-semibold">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <p class="font-medium text-zinc-100">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-sm text-zinc-400">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
    </div>
</div>
