<?php

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Enums\UserTier;
use Core\Tenant\Models\AccountDeletionRequest;
use Core\Tenant\Models\Boost;
use Core\Tenant\Models\Feature;
use Core\Tenant\Models\Package;
use Core\Tenant\Models\User;
use Core\Tenant\Models\Workspace;
use Core\Tenant\Services\EntitlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Website\Hub\Boot;
use Website\Hub\Concerns\HasRateLimiting;

class PlatformUser extends Component
{
    use HasRateLimiting;

    public User $user;

    // Editable fields
    public string $editingTier = '';

    public bool $editingVerified = false;

    // Action state
    public string $actionMessage = '';

    public string $actionType = '';

    public bool $showDeleteConfirm = false;

    public bool $immediateDelete = false;

    public string $deleteReason = '';

    // Package provisioning
    public bool $showPackageModal = false;

    public ?int $selectedWorkspaceId = null;

    public string $selectedPackageCode = '';

    public string $activeTab = 'overview';

    // Entitlement provisioning
    public bool $showEntitlementModal = false;

    public ?int $entitlementWorkspaceId = null;

    public string $entitlementFeatureCode = '';

    public string $entitlementType = 'enable';

    public ?int $entitlementLimit = null;

    public string $entitlementDuration = 'permanent';

    public ?string $entitlementExpiresAt = null;

    public function mount(int $id): void
    {
        // Ensure only Hades users can access
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for platform administration.');
        }

        $this->user = User::findOrFail($id);
        $this->editingTier = $this->user->tier?->value ?? 'free';
        $this->editingVerified = $this->user->email_verified_at !== null;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'workspaces', 'entitlements', 'data', 'danger'])) {
            $this->activeTab = $tab;
        }
    }

    public function saveTier(): void
    {
        $this->rateLimit('admin-tier-change', 10, function () {
            $this->user->tier = UserTier::from($this->editingTier);
            $this->user->save();

            $this->actionMessage = "Tier updated to {$this->editingTier}.";
            $this->actionType = 'success';
        });
    }

    public function saveVerification(): void
    {
        $this->rateLimit('admin-verification', 10, function () {
            if ($this->editingVerified && ! $this->user->email_verified_at) {
                $this->user->email_verified_at = now();
            } elseif (! $this->editingVerified) {
                $this->user->email_verified_at = null;
            }

            $this->user->save();

            $this->actionMessage = $this->editingVerified
                ? 'Email marked as verified.'
                : 'Email verification removed.';
            $this->actionType = 'success';
        });
    }

    public function resendVerification(): void
    {
        if ($this->user->email_verified_at) {
            $this->actionMessage = 'User email is already verified.';
            $this->actionType = 'warning';

            return;
        }

        $this->user->sendEmailVerificationNotification();

        $this->actionMessage = 'Verification email sent.';
        $this->actionType = 'success';
    }

    /**
     * Export all user data as JSON (GDPR Article 20 - Right to data portability).
     */
    public function exportUserData()
    {
        return $this->rateLimit('admin-export', 5, function () {
            $data = $this->collectUserData();

            $filename = "user-data-{$this->user->id}-".now()->format('Y-m-d-His').'.json';

            Log::info('GDPR data export performed by admin', [
                'admin_id' => auth()->id(),
                'target_user_id' => $this->user->id,
                'target_email' => $this->user->email,
            ]);

            return response()->streamDownload(function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, $filename, [
                'Content-Type' => 'application/json',
            ]);
        });
    }

    /**
     * Collect all user data for export or display.
     */
    public function collectUserData(): array
    {
        $this->user->load([
            'hostWorkspaces',
        ]);

        return [
            'export_info' => [
                'exported_at' => now()->toIso8601String(),
                'exported_by' => 'Platform Administrator',
                'reason' => 'GDPR Article 15 - Right of access / Article 20 - Right to data portability',
            ],
            'account' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'tier' => $this->user->tier?->value ?? 'free',
                'tier_expires_at' => $this->user->tier_expires_at?->toIso8601String(),
                'email_verified_at' => $this->user->email_verified_at?->toIso8601String(),
                'created_at' => $this->user->created_at?->toIso8601String(),
                'updated_at' => $this->user->updated_at?->toIso8601String(),
            ],
            'workspaces' => $this->user->hostWorkspaces->map(fn ($ws) => [
                'id' => $ws->id,
                'name' => $ws->name,
                'slug' => $ws->slug,
                'role' => $ws->pivot->role ?? null,
                'is_default' => $ws->pivot->is_default ?? false,
                'joined_at' => $ws->pivot->created_at?->toIso8601String(),
            ])->toArray(),
            'cached_stats' => $this->user->cached_stats,
            'deletion_requests' => AccountDeletionRequest::where('user_id', $this->user->id)
                ->get()
                ->map(fn ($req) => [
                    'id' => $req->id,
                    'reason' => $req->reason,
                    'status' => $this->getDeletionStatus($req),
                    'created_at' => $req->created_at?->toIso8601String(),
                    'expires_at' => $req->expires_at?->toIso8601String(),
                    'confirmed_at' => $req->confirmed_at?->toIso8601String(),
                    'completed_at' => $req->completed_at?->toIso8601String(),
                    'cancelled_at' => $req->cancelled_at?->toIso8601String(),
                ])->toArray(),
        ];
    }

    protected function getDeletionStatus(AccountDeletionRequest $req): string
    {
        if ($req->completed_at) {
            return 'completed';
        }
        if ($req->cancelled_at) {
            return 'cancelled';
        }
        if ($req->expires_at->isPast()) {
            return 'expired_pending';
        }

        return 'pending';
    }

    /**
     * Get pending deletion request for user.
     */
    public function getPendingDeletionProperty(): ?AccountDeletionRequest
    {
        return AccountDeletionRequest::where('user_id', $this->user->id)
            ->whereNull('completed_at')
            ->whereNull('cancelled_at')
            ->first();
    }

    /**
     * Show delete confirmation dialog.
     */
    public function confirmDelete(bool $immediate = false): void
    {
        $this->immediateDelete = $immediate;
        $this->showDeleteConfirm = true;
        $this->deleteReason = '';
    }

    /**
     * Cancel delete confirmation.
     */
    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->immediateDelete = false;
        $this->deleteReason = '';
    }

    /**
     * Schedule account deletion (GDPR Article 17 - Right to erasure).
     */
    public function scheduleDelete(): void
    {
        $this->rateLimit('admin-deletion', 3, function () {
            if ($this->user->isHades() && $this->user->id === auth()->id()) {
                $this->actionMessage = 'You cannot delete your own Hades account from here.';
                $this->actionType = 'error';
                $this->showDeleteConfirm = false;

                return;
            }

            $request = AccountDeletionRequest::createForUser($this->user, $this->deleteReason ?: 'Admin initiated - GDPR request');

            Log::warning('GDPR deletion scheduled by admin', [
                'admin_id' => auth()->id(),
                'target_user_id' => $this->user->id,
                'target_email' => $this->user->email,
                'immediate' => $this->immediateDelete,
                'reason' => $this->deleteReason,
            ]);

            if ($this->immediateDelete) {
                $this->executeImmediateDelete($request);
            } else {
                $this->actionMessage = 'Account deletion scheduled. Will be deleted in 7 days unless cancelled.';
                $this->actionType = 'warning';
            }

            $this->showDeleteConfirm = false;
        });
    }

    /**
     * Execute immediate deletion.
     */
    protected function executeImmediateDelete(AccountDeletionRequest $request): void
    {
        try {
            $email = $this->user->email;

            DB::transaction(function () use ($request) {
                $request->confirm();
                $request->complete();

                // Delete all workspaces owned by the user
                if (method_exists($this->user, 'hostWorkspaces')) {
                    $this->user->hostWorkspaces()->detach();
                }

                // Hard delete user account
                $this->user->forceDelete();
            });

            Log::warning('GDPR immediate deletion executed by admin', [
                'admin_id' => auth()->id(),
                'deleted_user_email' => $email,
            ]);

            session()->flash('platform_message', "User {$email} has been permanently deleted.");
            session()->flash('platform_message_type', 'success');

            $this->redirect(Boot::hubRoute('hub.platform'), navigate: true);
        } catch (\Exception $e) {
            Log::error('Failed to execute immediate deletion', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            $this->actionMessage = 'Failed to delete account: '.$e->getMessage();
            $this->actionType = 'error';
        }
    }

    /**
     * Cancel pending deletion request.
     */
    public function cancelPendingDeletion(): void
    {
        $pending = $this->pendingDeletion;

        if (! $pending) {
            $this->actionMessage = 'No pending deletion request found.';
            $this->actionType = 'warning';

            return;
        }

        $pending->cancel();

        Log::info('GDPR deletion cancelled by admin', [
            'admin_id' => auth()->id(),
            'target_user_id' => $this->user->id,
            'deletion_request_id' => $pending->id,
        ]);

        $this->actionMessage = 'Deletion request cancelled.';
        $this->actionType = 'success';
    }

    /**
     * Anonymize user data (alternative to deletion - GDPR compliant).
     */
    public function anonymizeUser(): void
    {
        $this->rateLimit('admin-deletion', 3, function () {
            if ($this->user->isHades() && $this->user->id === auth()->id()) {
                $this->actionMessage = 'You cannot anonymize your own account.';
                $this->actionType = 'error';

                return;
            }

            $originalEmail = $this->user->email;
            $anonymizedId = 'anon_'.$this->user->id.'_'.now()->timestamp;

            DB::transaction(function () use ($anonymizedId) {
                $this->user->update([
                    'name' => 'Anonymized User',
                    'email' => $anonymizedId.'@anonymized.local',
                    'password' => bcrypt(str()->random(64)),
                    'tier' => UserTier::FREE,
                    'email_verified_at' => null,
                    'cached_stats' => null,
                ]);

                // Remove from all workspaces
                if (method_exists($this->user, 'hostWorkspaces')) {
                    $this->user->hostWorkspaces()->detach();
                }

                // Cancel any pending deletions
                AccountDeletionRequest::where('user_id', $this->user->id)
                    ->whereNull('completed_at')
                    ->whereNull('cancelled_at')
                    ->update(['cancelled_at' => now()]);
            });

            Log::warning('User anonymized by admin (GDPR)', [
                'admin_id' => auth()->id(),
                'target_user_id' => $this->user->id,
                'original_email' => $originalEmail,
            ]);

            $this->user->refresh();
            $this->editingTier = $this->user->tier?->value ?? 'free';
            $this->editingVerified = false;

            $this->actionMessage = 'User data has been anonymized.';
            $this->actionType = 'success';
        });
    }

    /**
     * Get all related data counts for display.
     */
    public function getDataCountsProperty(): array
    {
        return [
            'workspaces' => $this->user->hostWorkspaces()->count(),
            'deletion_requests' => AccountDeletionRequest::where('user_id', $this->user->id)->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Workspace & Entitlement Management
    // ─────────────────────────────────────────────────────────────

    /**
     * Get user's workspaces with their packages.
     */
    #[Computed]
    public function workspaces()
    {
        return $this->user->hostWorkspaces()
            ->with(['workspacePackages' => function ($query) {
                $query->active()->with('package');
            }])
            ->get();
    }

    /**
     * Get all available packages for provisioning.
     */
    #[Computed]
    public function availablePackages()
    {
        return Package::active()->ordered()->get();
    }

    /**
     * Open the package provisioning modal.
     */
    public function openPackageModal(int $workspaceId): void
    {
        $this->selectedWorkspaceId = $workspaceId;
        $this->selectedPackageCode = '';
        $this->showPackageModal = true;
    }

    /**
     * Close the package provisioning modal.
     */
    public function closePackageModal(): void
    {
        $this->showPackageModal = false;
        $this->selectedWorkspaceId = null;
        $this->selectedPackageCode = '';
    }

    /**
     * Provision a package to the selected workspace.
     */
    public function provisionPackage(): void
    {
        $this->rateLimit('admin-entitlement', 10, function () {
            if (! $this->selectedWorkspaceId || ! $this->selectedPackageCode) {
                $this->actionMessage = 'Please select a workspace and package.';
                $this->actionType = 'warning';

                return;
            }

            $workspace = Workspace::findOrFail($this->selectedWorkspaceId);
            $package = Package::where('code', $this->selectedPackageCode)->firstOrFail();

            $entitlements = app(EntitlementService::class);
            $entitlements->provisionPackage($workspace, $this->selectedPackageCode, [
                'source' => 'admin',
            ]);

            Log::info('Package provisioned by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $this->user->id,
                'workspace_id' => $workspace->id,
                'package_code' => $this->selectedPackageCode,
            ]);

            $this->actionMessage = "Package '{$package->name}' provisioned to workspace '{$workspace->name}'.";
            $this->actionType = 'success';

            $this->closePackageModal();
            unset($this->workspaces); // Clear computed cache
        });
    }

    /**
     * Revoke a package from a workspace.
     */
    public function revokePackage(int $workspaceId, string $packageCode): void
    {
        $this->rateLimit('admin-entitlement', 10, function () use ($workspaceId, $packageCode) {
            $workspace = Workspace::findOrFail($workspaceId);

            // Verify this belongs to one of the user's workspaces
            if (! $this->user->hostWorkspaces->contains($workspace)) {
                $this->actionMessage = 'This workspace does not belong to this user.';
                $this->actionType = 'error';

                return;
            }

            $package = Package::where('code', $packageCode)->first();
            $packageName = $package?->name ?? $packageCode;
            $workspaceName = $workspace->name;

            $entitlements = app(EntitlementService::class);
            $entitlements->revokePackage($workspace, $packageCode, 'admin');

            Log::info('Package revoked by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $this->user->id,
                'workspace_id' => $workspace->id,
                'package_code' => $packageCode,
            ]);

            $this->actionMessage = "Package '{$packageName}' revoked from workspace '{$workspaceName}'.";
            $this->actionType = 'success';

            unset($this->workspaces); // Clear computed cache
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Entitlement Management
    // ─────────────────────────────────────────────────────────────

    /**
     * Get all available features for autocomplete.
     */
    #[Computed]
    public function allFeatures()
    {
        return Feature::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get resolved entitlements for each workspace.
     */
    #[Computed]
    public function workspaceEntitlements(): array
    {
        $entitlements = app(EntitlementService::class);
        $result = [];

        foreach ($this->workspaces as $workspace) {
            $summary = $entitlements->getUsageSummary($workspace);
            $boosts = $entitlements->getActiveBoosts($workspace);

            $result[$workspace->id] = [
                'workspace' => $workspace,
                'summary' => $summary,
                'boosts' => $boosts,
                'stats' => [
                    'total' => $summary->flatten(1)->count(),
                    'allowed' => $summary->flatten(1)->where('allowed', true)->count(),
                    'denied' => $summary->flatten(1)->where('allowed', false)->count(),
                    'boosts' => $boosts->count(),
                ],
            ];
        }

        return $result;
    }

    /**
     * Open the entitlement provisioning modal.
     */
    public function openEntitlementModal(int $workspaceId): void
    {
        $this->entitlementWorkspaceId = $workspaceId;
        $this->entitlementFeatureCode = '';
        $this->entitlementType = 'enable';
        $this->entitlementLimit = null;
        $this->entitlementDuration = 'permanent';
        $this->entitlementExpiresAt = null;
        $this->showEntitlementModal = true;
    }

    /**
     * Close the entitlement provisioning modal.
     */
    public function closeEntitlementModal(): void
    {
        $this->showEntitlementModal = false;
        $this->entitlementWorkspaceId = null;
        $this->entitlementFeatureCode = '';
    }

    /**
     * Provision an entitlement (boost) to the selected workspace.
     */
    public function provisionEntitlement(): void
    {
        $this->rateLimit('admin-entitlement', 10, function () {
            if (! $this->entitlementWorkspaceId || ! $this->entitlementFeatureCode) {
                $this->actionMessage = 'Please select a workspace and feature.';
                $this->actionType = 'warning';

                return;
            }

            $workspace = Workspace::findOrFail($this->entitlementWorkspaceId);
            $feature = Feature::where('code', $this->entitlementFeatureCode)->first();

            if (! $feature) {
                $this->actionMessage = 'Feature not found.';
                $this->actionType = 'error';

                return;
            }

            // Verify this belongs to one of the user's workspaces
            if (! $this->user->hostWorkspaces->contains($workspace)) {
                $this->actionMessage = 'This workspace does not belong to this user.';
                $this->actionType = 'error';

                return;
            }

            $options = [
                'source' => 'admin',
                'boost_type' => match ($this->entitlementType) {
                    'enable' => Boost::BOOST_TYPE_ENABLE,
                    'add_limit' => Boost::BOOST_TYPE_ADD_LIMIT,
                    'unlimited' => Boost::BOOST_TYPE_UNLIMITED,
                    default => Boost::BOOST_TYPE_ENABLE,
                },
                'duration_type' => $this->entitlementDuration === 'permanent'
                    ? Boost::DURATION_PERMANENT
                    : Boost::DURATION_DURATION,
            ];

            if ($this->entitlementType === 'add_limit' && $this->entitlementLimit) {
                $options['limit_value'] = $this->entitlementLimit;
            }

            if ($this->entitlementDuration === 'duration' && $this->entitlementExpiresAt) {
                $options['expires_at'] = $this->entitlementExpiresAt;
            }

            $entitlements = app(EntitlementService::class);
            $entitlements->provisionBoost($workspace, $this->entitlementFeatureCode, $options);

            Log::info('Entitlement provisioned by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $this->user->id,
                'workspace_id' => $workspace->id,
                'feature_code' => $this->entitlementFeatureCode,
                'type' => $this->entitlementType,
            ]);

            $this->actionMessage = "Entitlement '{$feature->name}' added to workspace '{$workspace->name}'.";
            $this->actionType = 'success';

            $this->closeEntitlementModal();
            unset($this->workspaceEntitlements);
        });
    }

    /**
     * Remove a boost from a workspace.
     */
    public function removeBoost(int $boostId): void
    {
        $this->rateLimit('admin-entitlement', 10, function () use ($boostId) {
            $boost = Boost::findOrFail($boostId);

            // Verify this belongs to one of the user's workspaces
            $workspace = $boost->workspace;
            if (! $this->user->hostWorkspaces->contains($workspace)) {
                $this->actionMessage = 'This boost does not belong to this user.';
                $this->actionType = 'error';

                return;
            }

            $featureCode = $boost->feature_code;
            $workspaceName = $workspace->name;

            $boost->update(['status' => Boost::STATUS_CANCELLED]);

            Log::info('Boost removed by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $this->user->id,
                'workspace_id' => $workspace->id,
                'boost_id' => $boostId,
                'feature_code' => $featureCode,
            ]);

            $this->actionMessage = "Boost for '{$featureCode}' removed from workspace '{$workspaceName}'.";
            $this->actionType = 'success';

            unset($this->workspaceEntitlements);
        });
    }

    public function render()
    {
        return view('hub::admin.platform-user', [
            'tiers' => UserTier::cases(),
            'userData' => $this->collectUserData(),
            'dataCounts' => $this->dataCounts,
            'pendingDeletion' => $this->pendingDeletion,
        ])->layout('hub::admin.layouts.app', ['title' => 'User: '.$this->user->name]);
    }
}
