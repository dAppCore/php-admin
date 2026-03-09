# Core Admin Package

Admin panel components, Livewire modals, and service management interface for the Core PHP Framework.

## Installation

```bash
composer require lthn/php-admin
```

## Features

### Admin Menu System
Declarative menu registration with automatic permission checking:

```php
use Core\Front\Admin\Contracts\AdminMenuProvider;

class MyModuleMenu implements AdminMenuProvider
{
    public function registerMenu(AdminMenuRegistry $registry): void
    {
        $registry->addItem('products', [
            'label' => 'Products',
            'icon' => 'cube',
            'route' => 'admin.products.index',
            'permission' => 'products.view',
        ]);
    }
}
```

### Livewire Modals
Full-page Livewire components for admin interfaces:

```php
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Product Manager')]
class ProductManager extends Component
{
    public function render(): View
    {
        return view('admin.products.manager')
            ->layout('hub::admin.layouts.app');
    }
}
```

### Form Components
Reusable form components with authorization:

- `<x-forms.input>` - Text inputs with validation
- `<x-forms.select>` - Dropdowns
- `<x-forms.checkbox>` - Checkboxes
- `<x-forms.toggle>` - Toggle switches
- `<x-forms.textarea>` - Text areas
- `<x-forms.button>` - Buttons with loading states

```blade
<x-forms.input
    name="name"
    label="Product Name"
    wire:model="name"
    required
/>
```

### Global Search
Extensible search provider system:

```php
use Core\Admin\Search\Contracts\SearchProvider;

class ProductSearchProvider implements SearchProvider
{
    public function search(string $query): array
    {
        return Product::where('name', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(fn($p) => new SearchResult(
                title: $p->name,
                url: route('admin.products.edit', $p),
                icon: 'cube'
            ))
            ->toArray();
    }
}
```

### Service Management Interface
Unified dashboard for viewing workspace services and statistics.

## Configuration

The package auto-discovers admin menu providers and search providers from your modules.

## Requirements

- PHP 8.2+
- Laravel 11+ or 12+
- Livewire 3.0+
- Flux UI 2.0+

## Changelog

See [changelog/2026/jan/features.md](changelog/2026/jan/features.md) for recent changes.

## License

EUPL-1.2 - See [LICENSE](../../LICENSE) for details.
