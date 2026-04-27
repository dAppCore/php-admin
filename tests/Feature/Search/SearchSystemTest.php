<?php

declare(strict_types=1);

use Core\Admin\Search\Providers\UserSearchProvider;
use Core\Admin\Search\Providers\WorkspaceSearchProvider;
use Core\Admin\Search\SearchDispatcher;
use Core\Admin\Search\SearchProvider;
use Core\Admin\Search\SearchResult;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

function searchSystemOriginalResolver(): mixed
{
    static $resolver = null;
    static $captured = false;

    if (! $captured) {
        $resolver = Model::getConnectionResolver();
        $captured = true;
    }

    return $resolver;
}

final class SearchSystemUserModel extends Model
{
    protected $table = 'search_system_users';

    public $timestamps = false;

    protected $guarded = [];
}

final class SearchSystemWorkspaceModel extends Model
{
    protected $table = 'search_system_workspaces';

    public $timestamps = false;

    protected $guarded = [];
}

function searchSystemDatabase(): Capsule
{
    static $capsule = null;

    searchSystemOriginalResolver();

    if ($capsule instanceof Capsule) {
        return $capsule;
    }

    $capsule = new Capsule;
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    Model::unguard();

    return $capsule;
}

function resetSearchSystemTables(): void
{
    $schema = searchSystemDatabase()->schema();

    $schema->dropIfExists('search_system_users');
    $schema->create('search_system_users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
    });

    $schema->dropIfExists('search_system_workspaces');
    $schema->create('search_system_workspaces', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('slug')->nullable();
    });
}

beforeEach(function (): void {
    resetSearchSystemTables();
});

afterAll(function (): void {
    $resolver = searchSystemOriginalResolver();

    if ($resolver !== null) {
        Model::setConnectionResolver($resolver);

        return;
    }

    Model::unsetConnectionResolver();
});

describe('SearchProvider contract', function (): void {
    it('Good: exposes search results, label, and priority', function (): void {
        $provider = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(
                        title: 'Dashboard',
                        subtitle: 'Overview',
                        url: '/hub',
                        icon: 'fa-gauge',
                        category: 'Pages',
                        score: 90,
                    ),
                ];
            }

            public function getLabel(): string
            {
                return 'Pages';
            }

            public function getPriority(): int
            {
                return 50;
            }
        };

        expect($provider->getLabel())->toBe('Pages')
            ->and($provider->getPriority())->toBe(50)
            ->and($provider->search('dash'))->toHaveCount(1)
            ->and($provider->search('dash')[0])->toBeInstanceOf(SearchResult::class);
    });

    it('Bad: allows a provider to return no matches', function (): void {
        $provider = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [];
            }

            public function getLabel(): string
            {
                return 'Empty';
            }

            public function getPriority(): int
            {
                return 0;
            }
        };

        expect($provider->search('missing'))->toBe([]);
    });

    it('Ugly: dispatcher ignores non-result payloads from a loose provider', function (): void {
        $provider = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    ['title' => 'Array payload'],
                    new SearchResult(
                        title: 'Real payload',
                        subtitle: 'Valid DTO',
                        url: '/real',
                        icon: 'fa-check',
                        category: 'Valid',
                        score: 10,
                    ),
                ];
            }

            public function getLabel(): string
            {
                return 'Mixed';
            }

            public function getPriority(): int
            {
                return 1;
            }
        };

        $results = (new SearchDispatcher([$provider]))->search('payload');

        expect($results)->toHaveCount(1)
            ->and($results[0]->title)->toBe('Real payload');
    });
});

describe('SearchResult DTO', function (): void {
    it('Good: stores the required readonly result fields', function (): void {
        $result = new SearchResult(
            title: 'Alice Admin',
            subtitle: 'alice@example.test',
            url: '/hub/platform/user/1',
            icon: 'fa-user',
            category: 'Users',
            score: 100,
        );

        expect($result->title)->toBe('Alice Admin')
            ->and($result->subtitle)->toBe('alice@example.test')
            ->and($result->url)->toBe('/hub/platform/user/1')
            ->and($result->icon)->toBe('fa-user')
            ->and($result->category)->toBe('Users')
            ->and($result->score)->toBe(100);
    });

    it('Bad: applies sensible defaults for partial result data', function (): void {
        $result = new SearchResult(title: 'Untyped');

        expect($result->title)->toBe('Untyped')
            ->and($result->subtitle)->toBeNull()
            ->and($result->url)->toBe('#')
            ->and($result->icon)->toBe('document')
            ->and($result->category)->toBe('unknown')
            ->and($result->score)->toBe(0);
    });

    it('Ugly: supports both new and legacy positional construction', function (): void {
        $newShape = new SearchResult('Workspace', 'primary-site', '/hub/workspaces/primary-site', 'fa-folder', 'Workspaces', 85);
        $legacyShape = new SearchResult('user-1', 'Alice Admin', '/hub/platform/user/1', 'users', 'fa-user', 'alice@example.test');
        $numericSubtitleLegacyShape = new SearchResult('user-2', 'Numeric Subtitle', '/hub/platform/user/2', 'users', 'fa-user', '12345');

        expect($newShape->title)->toBe('Workspace')
            ->and($newShape->category)->toBe('Workspaces')
            ->and($newShape->score)->toBe(85)
            ->and($legacyShape->id)->toBe('user-1')
            ->and($legacyShape->title)->toBe('Alice Admin')
            ->and($legacyShape->type)->toBe('users')
            ->and($numericSubtitleLegacyShape->id)->toBe('user-2')
            ->and($numericSubtitleLegacyShape->subtitle)->toBe('12345')
            ->and($numericSubtitleLegacyShape->score)->toBe(0);
    });
});

describe('UserSearchProvider', function (): void {
    it('Good: finds users by name and email using Eloquent LIKE queries', function (): void {
        SearchSystemUserModel::query()->create(['name' => 'Alice Admin', 'email' => 'alice@example.test']);
        SearchSystemUserModel::query()->create(['name' => 'Bob Builder', 'email' => 'bob@example.test']);

        $provider = new UserSearchProvider(SearchSystemUserModel::class);

        $nameResults = $provider->search('Alice');
        $emailResults = $provider->search('bob@example');

        expect($nameResults)->toHaveCount(1)
            ->and($nameResults[0]->title)->toBe('Alice Admin')
            ->and($nameResults[0]->subtitle)->toBe('alice@example.test')
            ->and($nameResults[0]->category)->toBe('Users')
            ->and($emailResults)->toHaveCount(1)
            ->and($emailResults[0]->title)->toBe('Bob Builder');
    });

    it('Bad: returns no results for blank user queries', function (): void {
        SearchSystemUserModel::query()->create(['name' => 'Alice Admin', 'email' => 'alice@example.test']);

        $provider = new UserSearchProvider(SearchSystemUserModel::class);

        expect($provider->search('   '))->toBe([]);
    });

    it('Ugly: escapes LIKE wildcards instead of broad matching every user', function (): void {
        SearchSystemUserModel::query()->create(['name' => 'Alice Admin', 'email' => 'alice@example.test']);
        SearchSystemUserModel::query()->create(['name' => 'Bob Builder', 'email' => 'bob@example.test']);

        $provider = new UserSearchProvider(SearchSystemUserModel::class);

        expect($provider->search('%'))->toBe([]);
    });

    it('Ugly: ranks all user candidates before applying the provider limit', function (): void {
        SearchSystemUserModel::query()->create(['name' => 'The Target Person', 'email' => 'first@example.test']);
        SearchSystemUserModel::query()->create(['name' => 'target', 'email' => 'second@example.test']);

        $provider = new UserSearchProvider(SearchSystemUserModel::class, 1);
        $results = $provider->search('target');

        expect($results)->toHaveCount(1)
            ->and($results[0]->title)->toBe('target')
            ->and($results[0]->score)->toBe(100);
    });
});

describe('WorkspaceSearchProvider', function (): void {
    it('Good: finds workspaces by name and slug using Eloquent LIKE queries', function (): void {
        SearchSystemWorkspaceModel::query()->create(['name' => 'Primary Site', 'slug' => 'primary-site']);
        SearchSystemWorkspaceModel::query()->create(['name' => 'Docs Centre', 'slug' => 'docs-centre']);

        $provider = new WorkspaceSearchProvider(SearchSystemWorkspaceModel::class);

        $nameResults = $provider->search('Primary');
        $slugResults = $provider->search('docs-centre');

        expect($nameResults)->toHaveCount(1)
            ->and($nameResults[0]->title)->toBe('Primary Site')
            ->and($nameResults[0]->url)->toBe('/hub/workspaces/primary-site')
            ->and($nameResults[0]->category)->toBe('Workspaces')
            ->and($slugResults)->toHaveCount(1)
            ->and($slugResults[0]->title)->toBe('Docs Centre');
    });

    it('Bad: returns no results for blank workspace queries', function (): void {
        SearchSystemWorkspaceModel::query()->create(['name' => 'Primary Site', 'slug' => 'primary-site']);

        $provider = new WorkspaceSearchProvider(SearchSystemWorkspaceModel::class);

        expect($provider->search("\n\t"))->toBe([]);
    });

    it('Ugly: escapes LIKE wildcards instead of broad matching every workspace', function (): void {
        SearchSystemWorkspaceModel::query()->create(['name' => 'Primary Site', 'slug' => 'primary-site']);
        SearchSystemWorkspaceModel::query()->create(['name' => 'Docs Centre', 'slug' => 'docs-centre']);

        $provider = new WorkspaceSearchProvider(SearchSystemWorkspaceModel::class);

        expect($provider->search('_'))->toBe([]);
    });

    it('Ugly: ranks all workspace candidates before applying the provider limit', function (): void {
        SearchSystemWorkspaceModel::query()->create(['name' => 'The Target Workspace', 'slug' => 'target-workspace']);
        SearchSystemWorkspaceModel::query()->create(['name' => 'target', 'slug' => 'exact-target']);

        $provider = new WorkspaceSearchProvider(SearchSystemWorkspaceModel::class, 1);
        $results = $provider->search('target');

        expect($results)->toHaveCount(1)
            ->and($results[0]->title)->toBe('target')
            ->and($results[0]->score)->toBe(100);
    });
});

describe('SearchDispatcher', function (): void {
    it('Good: gathers provider results and ranks by score descending', function (): void {
        $users = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(title: 'Lower', subtitle: null, url: '/low', icon: 'fa-user', category: 'Users', score: 60),
                ];
            }

            public function getLabel(): string
            {
                return 'Users';
            }

            public function getPriority(): int
            {
                return 100;
            }
        };

        $workspaces = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(title: 'Higher', subtitle: null, url: '/high', icon: 'fa-folder', category: 'Workspaces', score: 95),
                ];
            }

            public function getLabel(): string
            {
                return 'Workspaces';
            }

            public function getPriority(): int
            {
                return 90;
            }
        };

        $results = (new SearchDispatcher([$users, $workspaces]))->search('query');

        expect($results)->toHaveCount(2)
            ->and($results[0]->title)->toBe('Higher')
            ->and($results[1]->title)->toBe('Lower');
    });

    it('Bad: skips provider calls for empty dispatcher queries', function (): void {
        $provider = new class implements SearchProvider
        {
            public bool $called = false;

            public function search(string $query): array
            {
                $this->called = true;

                return [];
            }

            public function getLabel(): string
            {
                return 'Never Called';
            }

            public function getPriority(): int
            {
                return 1;
            }
        };

        $results = (new SearchDispatcher([$provider]))->search('   ');

        expect($results)->toBe([])
            ->and($provider->called)->toBeFalse();
    });

    it('Ugly: uses provider priority as a deterministic score tie-breaker', function (): void {
        $lowPriority = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(title: 'Low priority', subtitle: null, url: '/low', icon: 'fa-user', category: 'Users', score: 80),
                ];
            }

            public function getLabel(): string
            {
                return 'Low';
            }

            public function getPriority(): int
            {
                return 1;
            }
        };

        $highPriority = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(title: 'High priority', subtitle: null, url: '/high', icon: 'fa-folder', category: 'Workspaces', score: 80),
                ];
            }

            public function getLabel(): string
            {
                return 'High';
            }

            public function getPriority(): int
            {
                return 99;
            }
        };

        $results = (new SearchDispatcher([$lowPriority, $highPriority]))->search('query');

        expect($results[0]->title)->toBe('High priority')
            ->and($results[1]->title)->toBe('Low priority');
    });

    it('Ugly: reports a failing provider and continues aggregating healthy results', function (): void {
        $failing = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                throw new RuntimeException('Provider failed.');
            }

            public function getLabel(): string
            {
                return 'Failing';
            }

            public function getPriority(): int
            {
                return 100;
            }
        };

        $healthy = new class implements SearchProvider
        {
            public function search(string $query): array
            {
                return [
                    new SearchResult(title: 'Healthy result', subtitle: null, url: '/healthy', icon: 'fa-check', category: 'Healthy', score: 80),
                ];
            }

            public function getLabel(): string
            {
                return 'Healthy';
            }

            public function getPriority(): int
            {
                return 1;
            }
        };

        $results = (new SearchDispatcher([$failing, $healthy]))->search('query');

        expect($results)->toHaveCount(1)
            ->and($results[0]->title)->toBe('Healthy result');
    });
});
