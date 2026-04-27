<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ \Website\Hub\Boot::hubRoute('hub.platform') }}" wire:navigate class="hover:text-gray-700 dark:hover:text-gray-200">
                <core:icon name="arrow-left" class="mr-1" />
                Platform Users
            </a>
            <span>/</span>
            <span>{{ $user->name }}</span>
        </div>

        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                @php
                    $tierColor = match($user->tier?->value ?? 'free') {
                        'hades' => 'violet',
                        'apollo' => 'blue',
                        default => 'gray',
                    };
                @endphp
                <div class="w-16 h-16 rounded-xl bg-{{ $tierColor }}-500/20 flex items-center justify-center">
                    <core:icon name="user" class="text-2xl text-{{ $tierColor }}-600 dark:text-{{ $tierColor }}-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $user->name }}</h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $tierColor }}-500/20 text-{{ $tierColor }}-600 dark:text-{{ $tierColor }}-400">
                            {{ ucfirst($user->tier?->value ?? 'free') }}
                        </span>
                        @if($user->email_verified_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/20 text-green-600 dark:text-green-400">
                            <core:icon name="check-circle" class="mr-1" />
                            Verified
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-600 dark:text-amber-400">
                            <core:icon name="clock" class="mr-1" />
                            Unverified
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-violet-500/20 text-violet-600 dark:text-violet-400">
                <core:icon name="crown" class="mr-1.5" />
                Hades Only
            </span>
        </div>
    </div>

    {{-- Action message --}}
    @if($actionMessage)
    <div class="mb-6 p-4 rounded-lg {{ $actionType === 'success' ? 'bg-green-500/20 text-green-700 dark:text-green-400' : ($actionType === 'warning' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-400' : 'bg-red-500/20 text-red-700 dark:text-red-400') }}">
        <div class="flex items-center">
            <core:icon name="{{ $actionType === 'success' ? 'check-circle' : ($actionType === 'warning' ? 'triangle-exclamation' : 'circle-xmark') }}" class="mr-2" />
            {{ $actionMessage }}
        </div>
    </div>
    @endif

    {{-- Pending deletion warning --}}
    @if($pendingDeletion)
    <div class="mb-6 p-4 rounded-lg bg-red-500/20 border border-red-500/30">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <core:icon name="triangle-exclamation" class="text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <div class="font-medium text-red-800 dark:text-red-200">Account deletion scheduled</div>
                    <div class="text-sm text-red-700 dark:text-red-300 mt-1">
                        This account is scheduled for deletion on {{ $pendingDeletion->expires_at->format('j F Y') }}.
                        @if($pendingDeletion->reason)
                            Reason: {{ $pendingDeletion->reason }}
                        @endif
                    </div>
                </div>
            </div>
            <flux:button wire:click="cancelPendingDeletion" size="sm" variant="danger">
                Cancel deletion
            </flux:button>
        </div>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-6" aria-label="Tabs">
            @foreach([
                'overview' => ['label' => 'Overview', 'icon' => 'gauge'],
                'workspaces' => ['label' => 'Workspaces', 'icon' => 'folder'],
                'entitlements' => ['label' => 'Entitlements', 'icon' => 'key'],
                'data' => ['label' => 'Data & Privacy', 'icon' => 'shield-halved'],
                'danger' => ['label' => 'Danger Zone', 'icon' => 'triangle-exclamation'],
            ] as $tab => $info)
            <button
                wire:click="setTab('{{ $tab }}')"
                class="flex items-center gap-2 py-3 px-1 border-b-2 text-sm font-medium transition {{ $activeTab === $tab ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                <core:icon name="{{ $info['icon'] }}" />
                {{ $info['label'] }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="min-h-[400px]">
        {{-- Overview Tab --}}
        @if($activeTab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Account Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Account Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">User ID</div>
                            <div class="font-mono text-gray-800 dark:text-gray-100">{{ $user->id }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Created</div>
                            <div class="text-gray-800 dark:text-gray-100">{{ $user->created_at?->format('d M Y, H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Last Updated</div>
                            <div class="text-gray-800 dark:text-gray-100">{{ $user->updated_at?->format('d M Y, H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Email Verified</div>
                            <div class="text-gray-800 dark:text-gray-100">
                                {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Not verified' }}
                            </div>
                        </div>
                        @if($user->tier_expires_at)
                        <div class="col-span-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tier Expires</div>
                            <div class="text-gray-800 dark:text-gray-100">{{ $user->tier_expires_at->format('d M Y') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Tier Management --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Tier Management</h3>
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <flux:select wire:model="editingTier" label="Account Tier">
                                @foreach($tiers as $tier)
                                <flux:select.option value="{{ $tier->value }}">{{ ucfirst($tier->value) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <flux:button wire:click="saveTier" variant="primary">Save Tier</flux:button>
                    </div>
                </div>

                {{-- Email Verification --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Email Verification</h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:checkbox wire:model="editingVerified" label="Email verified" />
                            <flux:button wire:click="saveVerification" size="sm">Save</flux:button>
                        </div>
                        <flux:button wire:click="resendVerification" variant="ghost" size="sm">
                            <core:icon name="envelope" class="mr-1" />
                            Resend verification
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Quick Stats --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Quick Stats</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Workspaces</span>
                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $dataCounts['workspaces'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Deletion Requests</span>
                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $dataCounts['deletion_requests'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Account Details --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Details</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Tier</div>
                            <div class="text-gray-800 dark:text-gray-100 capitalize">{{ $user->tier?->value ?? 'free' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Status</div>
                            <div class="flex items-center gap-2">
                                @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/20 text-green-600">Active</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-600">Pending Verification</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Workspaces Tab --}}
        @if($activeTab === 'workspaces')
        <div class="space-y-6">
            {{-- Workspace List --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-gray-100">Workspaces ({{ $this->workspaces->count() }})</h3>
                </div>

                @if($this->workspaces->isEmpty())
                <div class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="w-12 h-12 rounded-xl bg-gray-500/20 flex items-center justify-center mx-auto mb-3">
                        <core:icon name="folder" class="text-xl text-gray-400" />
                    </div>
                    <p>No workspaces</p>
                    <p class="text-sm mt-1">This user hasn't created any workspaces yet.</p>
                </div>
                @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($this->workspaces as $workspace)
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-{{ $workspace->color ?? 'blue' }}-500/20 flex items-center justify-center">
                                    <core:icon name="{{ $workspace->icon ?? 'folder' }}" class="text-{{ $workspace->color ?? 'blue' }}-600 dark:text-{{ $workspace->color ?? 'blue' }}-400" />
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $workspace->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $workspace->slug }}</div>
                                </div>
                            </div>
                            <flux:button wire:click="openPackageModal({{ $workspace->id }})" size="sm">
                                <core:icon name="plus" class="mr-1" />
                                Add Package
                            </flux:button>
                        </div>

                        @if($workspace->workspacePackages->isEmpty())
                        <div class="text-sm text-gray-500 dark:text-gray-400 italic ml-13">No packages provisioned</div>
                        @else
                        <div class="ml-13 space-y-2">
                            @foreach($workspace->workspacePackages as $wp)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-{{ $wp->package->color ?? 'gray' }}-500/20 flex items-center justify-center">
                                        <core:icon name="{{ $wp->package->icon ?? 'box' }}" class="text-sm text-{{ $wp->package->color ?? 'gray' }}-600 dark:text-{{ $wp->package->color ?? 'gray' }}-400" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-gray-100 text-sm">{{ $wp->package->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $wp->package->code }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($wp->package->is_base_package)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/20 text-blue-600 dark:text-blue-400">Base</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/20 text-green-600 dark:text-green-400">
                                        {{ ucfirst($wp->status ?? 'active') }}
                                    </span>
                                    <button
                                        wire:click="revokePackage({{ $workspace->id }}, '{{ $wp->package->code }}')"
                                        wire:confirm="Revoke '{{ $wp->package->name }}' from this workspace?"
                                        class="p-1.5 text-red-600 hover:bg-red-500/20 rounded-lg transition"
                                        title="Revoke package"
                                    >
                                        <core:icon name="trash" class="text-sm" />
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Entitlements Tab --}}
        @if($activeTab === 'entitlements')
        <div class="space-y-6">
            @if($this->workspaces->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-500/20 flex items-center justify-center mx-auto mb-3">
                    <core:icon name="key" class="text-xl text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400">No workspaces</p>
                <p class="text-sm text-gray-400 mt-1">This user has no workspaces to manage entitlements for.</p>
            </div>
            @else
                @foreach($this->workspaceEntitlements as $wsId => $data)
                @php $workspace = $data['workspace']; $stats = $data['stats']; $boosts = $data['boosts']; $summary = $data['summary']; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                    {{-- Workspace Header --}}
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-{{ $workspace->color ?? 'blue' }}-500/20 flex items-center justify-center">
                                <core:icon name="{{ $workspace->icon ?? 'folder' }}" class="text-{{ $workspace->color ?? 'blue' }}-600 dark:text-{{ $workspace->color ?? 'blue' }}-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 dark:text-gray-100">{{ $workspace->name }}</h3>
                                <div class="text-xs text-gray-500 font-mono">{{ $workspace->slug }}</div>
                            </div>
                        </div>
                        <flux:button wire:click="openEntitlementModal({{ $workspace->id }})" size="sm">
                            <core:icon name="plus" class="mr-1" />
                            Add Entitlement
                        </flux:button>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20">
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $stats['total'] }}</div>
                                <div class="text-xs text-gray-500">Total</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $stats['allowed'] }}</div>
                                <div class="text-xs text-gray-500">Allowed</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ $stats['denied'] }}</div>
                                <div class="text-xs text-gray-500">Denied</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $stats['boosts'] }}</div>
                                <div class="text-xs text-gray-500">Boosts</div>
                            </div>
                        </div>
                    </div>

                    {{-- Active Boosts --}}
                    @if($boosts->count() > 0)
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
                            <core:icon name="bolt" class="mr-1 text-purple-500" />
                            Active Boosts
                        </h4>
                        <div class="space-y-2">
                            @foreach($boosts as $boost)
                            <div class="flex items-center justify-between p-3 bg-purple-500/10 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                        <core:icon name="bolt" class="text-sm text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-gray-100 font-mono text-sm">{{ $boost->feature_code }}</div>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span class="capitalize">{{ str_replace('_', ' ', $boost->boost_type) }}</span>
                                            @if($boost->limit_value)
                                            <span>· +{{ number_format($boost->limit_value) }}</span>
                                            @endif
                                            @if($boost->expires_at)
                                            <span>· Expires {{ $boost->expires_at->format('d M Y') }}</span>
                                            @else
                                            <span class="text-green-500">· Permanent</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button
                                    wire:click="removeBoost({{ $boost->id }})"
                                    wire:confirm="Remove this boost?"
                                    class="p-1.5 text-red-600 hover:bg-red-500/20 rounded-lg transition"
                                    title="Remove boost"
                                >
                                    <core:icon name="trash" class="text-sm" />
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Allowed Entitlements Summary --}}
                    <div class="px-5 py-4">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Allowed Features</h4>
                        @php
                            $allowedFeatures = $summary->flatten(1)->where('allowed', true);
                        @endphp
                        @if($allowedFeatures->isEmpty())
                        <p class="text-sm text-gray-400 italic">No features enabled</p>
                        @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($allowedFeatures as $entitlement)
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                                {{ $entitlement['unlimited'] ? 'bg-purple-500/20 text-purple-700 dark:text-purple-300' : 'bg-green-500/20 text-green-700 dark:text-green-300' }}">
                                <core:icon name="{{ $entitlement['unlimited'] ? 'infinity' : 'check' }}" class="text-xs" />
                                {{ $entitlement['name'] }}
                                @if(!$entitlement['unlimited'] && $entitlement['limit'])
                                <span class="text-gray-500">({{ number_format($entitlement['used'] ?? 0) }}/{{ number_format($entitlement['limit']) }})</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        @endif

        {{-- Data & Privacy Tab --}}
        @if($activeTab === 'data')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Stored Data Preview --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-800 dark:text-gray-100">Stored Data</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">GDPR Article 15 - Right of access</p>
                        </div>
                        <flux:button wire:click="exportUserData" size="sm">
                            <core:icon name="arrow-down-tray" class="mr-1" />
                            Export JSON
                        </flux:button>
                    </div>
                    <div class="bg-gray-900 dark:bg-gray-950 p-4 overflow-x-auto max-h-[500px] overflow-y-auto">
                        <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- GDPR Info --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">GDPR Compliance</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <core:icon name="file-export" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">Article 20</div>
                                <div class="text-xs text-gray-500">Data portability</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center flex-shrink-0">
                                <core:icon name="eye" class="text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">Article 15</div>
                                <div class="text-xs text-gray-500">Right of access</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                <core:icon name="trash" class="text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">Article 17</div>
                                <div class="text-xs text-gray-500">Right to erasure</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Danger Zone Tab --}}
        @if($activeTab === 'danger')
        <div class="max-w-2xl space-y-6">
            {{-- Scheduled Deletion --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-200 dark:border-amber-800 bg-amber-500/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                            <core:icon name="clock" class="text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h3 class="font-medium text-amber-900 dark:text-amber-200">Schedule Deletion</h3>
                            <p class="text-sm text-amber-700 dark:text-amber-300">GDPR Article 17 - Right to erasure</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Schedule account deletion with a 7-day grace period. The user will be notified and can cancel during this time.
                    </p>
                    <flux:button wire:click="confirmDelete(false)" :disabled="$pendingDeletion">
                        <core:icon name="clock" class="mr-1" />
                        Schedule Deletion
                    </flux:button>
                </div>
            </div>

            {{-- Immediate Deletion --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-red-200 dark:border-red-800 bg-red-500/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                            <core:icon name="trash" class="text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <h3 class="font-medium text-red-900 dark:text-red-200">Immediate Deletion</h3>
                            <p class="text-sm text-red-700 dark:text-red-300">Permanently delete account and all data</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Permanently delete this account and all associated data immediately. This action cannot be undone.
                    </p>
                    <flux:button wire:click="confirmDelete(true)" variant="danger">
                        <core:icon name="trash" class="mr-1" />
                        Delete Immediately
                    </flux:button>
                </div>
            </div>

            {{-- Anonymisation --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-500/20 flex items-center justify-center">
                            <core:icon name="user-minus" class="text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800 dark:text-gray-200">Anonymise Account</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Replace PII with anonymous data</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Replace all personally identifiable information with anonymous data while keeping the account structure intact. This is an alternative to full deletion.
                    </p>
                    <flux:button wire:click="anonymizeUser" variant="ghost">
                        <core:icon name="user-minus" class="mr-1" />
                        Anonymise User
                    </flux:button>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="showDeleteConfirm" class="max-w-lg">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                    <core:icon name="triangle-exclamation" class="text-red-600 dark:text-red-400" />
                </div>
                <flux:heading size="lg">
                    {{ $immediateDelete ? 'Delete account immediately' : 'Schedule account deletion' }}
                </flux:heading>
            </div>

            <p class="text-gray-600 dark:text-gray-400">
                @if($immediateDelete)
                    This will permanently delete <strong class="text-gray-800 dark:text-gray-200">{{ $user->email }}</strong> and all associated data immediately. This action cannot be undone.
                @else
                    This will schedule <strong class="text-gray-800 dark:text-gray-200">{{ $user->email }}</strong> for deletion in 7 days. The user can cancel during this period.
                @endif
            </p>

            <flux:input wire:model="deleteReason" label="Reason (optional)" placeholder="GDPR request, user requested, etc." />

            <div class="flex justify-end gap-3">
                <flux:button wire:click="cancelDelete" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="scheduleDelete" :variant="$immediateDelete ? 'danger' : 'primary'">
                    {{ $immediateDelete ? 'Delete permanently' : 'Schedule deletion' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Package provisioning modal --}}
    <flux:modal wire:model="showPackageModal" class="max-w-lg">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <core:icon name="box" class="text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg">Provision Package</flux:heading>
            </div>

            @if($selectedWorkspaceId)
                @php
                    $selectedWorkspace = $this->workspaces->firstWhere('id', $selectedWorkspaceId);
                @endphp
                <div class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Workspace</div>
                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $selectedWorkspace?->name ?? 'Unknown' }}</div>
                </div>
            @endif

            <flux:select wire:model="selectedPackageCode" label="Select Package">
                <flux:select.option value="">Choose a package...</flux:select.option>
                @foreach($this->availablePackages as $package)
                <flux:select.option value="{{ $package->code }}">
                    {{ $package->name }}
                    @if($package->is_base_package) (Base) @endif
                    @if(!$package->is_public) (Internal) @endif
                </flux:select.option>
                @endforeach
            </flux:select>

            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    The package will be assigned immediately with no expiry date. You can modify or remove it later.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closePackageModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="provisionPackage" variant="primary" :disabled="!$selectedPackageCode">
                    Provision Package
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Entitlement provisioning modal --}}
    <flux:modal wire:model="showEntitlementModal" class="max-w-lg">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <core:icon name="bolt" class="text-purple-600 dark:text-purple-400" />
                </div>
                <flux:heading size="lg">Add Entitlement</flux:heading>
            </div>

            @if($entitlementWorkspaceId)
                @php
                    $entitlementWorkspace = $this->workspaces->firstWhere('id', $entitlementWorkspaceId);
                @endphp
                <div class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Workspace</div>
                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $entitlementWorkspace?->name ?? 'Unknown' }}</div>
                </div>
            @endif

            <flux:select wire:model="entitlementFeatureCode" variant="listbox" searchable label="Feature" placeholder="Search features...">
                @foreach($this->allFeatures->groupBy('category') as $category => $features)
                <flux:select.option disabled>── {{ ucfirst($category ?: 'General') }} ──</flux:select.option>
                @foreach($features as $feature)
                <flux:select.option value="{{ $feature->code }}">
                    {{ $feature->name }} ({{ $feature->code }})
                </flux:select.option>
                @endforeach
                @endforeach
            </flux:select>

            <flux:select wire:model.live="entitlementType" label="Type">
                <flux:select.option value="enable">Enable (Toggle on)</flux:select.option>
                <flux:select.option value="add_limit">Add Limit (Extra quota)</flux:select.option>
                <flux:select.option value="unlimited">Unlimited</flux:select.option>
            </flux:select>

            @if($entitlementType === 'add_limit')
            <flux:input wire:model="entitlementLimit" type="number" label="Limit Value" placeholder="e.g. 100" min="1" />
            @endif

            <flux:select wire:model.live="entitlementDuration" label="Duration">
                <flux:select.option value="permanent">Permanent</flux:select.option>
                <flux:select.option value="duration">Expires on date</flux:select.option>
            </flux:select>

            @if($entitlementDuration === 'duration')
            <flux:input wire:model="entitlementExpiresAt" type="date" label="Expires At" />
            @endif

            <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-3">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                    This will create a boost that grants the selected feature directly to this workspace, independent of packages.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeEntitlementModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="provisionEntitlement" variant="primary" :disabled="!$entitlementFeatureCode">
                    Add Entitlement
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
