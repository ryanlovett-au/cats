<?php

namespace App\Providers;

use App\Cats\ServiceManager;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\MenuBar;
use Native\Desktop\Facades\Settings;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    // Fallback popover size when nothing has been persisted yet.
    private const DEFAULT_WIDTH = 600;

    private const DEFAULT_HEIGHT = 600;

    public function boot(): void
    {
        [$width, $height] = $this->persistedSize();

        MenuBar::create()
            ->route('menu')
            ->icon(public_path('menuBarIconTemplate.png'))
            ->width($width)
            ->height($height)
            ->minWidth(360)
            ->minHeight(300)
            ->blendBackgroundBehindWindow()
            ->alwaysOnTop()
            ->resizable();

        app(ServiceManager::class)->startAutoStartServices();
    }

    /**
     * Read the last saved popover size, falling back to defaults when nothing
     * is stored or the Settings API is unavailable (e.g. in-browser dev).
     *
     * @return array{int, int}
     */
    private function persistedSize(): array
    {
        try {
            $width = (int) Settings::get('menubar_width', self::DEFAULT_WIDTH);
            $height = (int) Settings::get('menubar_height', self::DEFAULT_HEIGHT);
        } catch (\Throwable) {
            $width = self::DEFAULT_WIDTH;
            $height = self::DEFAULT_HEIGHT;
        }

        return [
            $width > 0 ? $width : self::DEFAULT_WIDTH,
            $height > 0 ? $height : self::DEFAULT_HEIGHT,
        ];
    }

    public function phpIni(): array
    {
        return [
            'error_reporting' => E_ALL & ~E_DEPRECATED,
        ];
    }
}
