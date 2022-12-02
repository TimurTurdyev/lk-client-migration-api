<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\MigrateFile;
use App\Models\Tree;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * @return Menu[]
     */
    public function registerMainMenu(): array
    {
        return [
            Menu::make('Экспорт')
                ->icon('orc.monitor')
                ->route('platform.export')
                ->title('Navigation')
                ->badge(function () {
                    return Tree::count();
                }),

            Menu::make('Импорт')
                ->icon('orc.monitor')
                ->route('platform.import')
                ->badge(function () {
                    return MigrateFile::count();
                }),

            /*
            Menu::make('Example screen')
                ->icon('orc.monitor')
                ->route('platform.example')
                ->title('Navigation')
                ->badge(function () {
                    return 6;
                }),

            Menu::make('Dropdown menu')
                ->icon('orc.code')
                ->list([
                    Menu::make('Sub element item 1')->icon('orc.bag'),
                    Menu::make('Sub element item 2')->icon('orc.heart'),
                ]),

            Menu::make('Basic Elements')
                ->title('Form controls')
                ->icon('orc.note')
                ->route('platform.example.fields'),

            Menu::make('Advanced Elements')
                ->icon('orc.briefcase')
                ->route('platform.example.advanced'),

            Menu::make('Text Editors')
                ->icon('orc.list')
                ->route('platform.example.editors'),

            Menu::make('Overview layouts')
                ->title('Layouts')
                ->icon('orc.layers')
                ->route('platform.example.layouts'),

            Menu::make('Chart tools')
                ->icon('orc.bar-chart')
                ->route('platform.example.charts'),

            Menu::make('Cards')
                ->icon('orc.grid')
                ->route('platform.example.cards')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('orc.docs')
                ->url('https://orchid.software/en/docs'),

            Menu::make('Changelog')
                ->icon('orc.shuffle')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(function () {
                    return Dashboard::version();
                }, Color::DARK()),*/

            Menu::make(__('Users'))
                ->icon('orc.user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access rights')),

            Menu::make(__('Roles'))
                ->icon('orc.lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    /**
     * @return Menu[]
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make('Profile')
                ->route('platform.profile')
                ->icon('orc.user'),
        ];
    }

    /**
     * @return ItemPermission[]
     */
    public function registerPermissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
