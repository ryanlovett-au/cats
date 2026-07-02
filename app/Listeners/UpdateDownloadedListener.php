<?php

namespace App\Listeners;

use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Facades\Settings;

class UpdateDownloadedListener
{
    public function handle(UpdateDownloaded $event): void
    {
        // Record the ready-to-install version so the menu bar can offer a
        // "Quit & Install" action. NativePHP's implicit install-on-quit does
        // not fire on macOS, so the install must be triggered explicitly via
        // AutoUpdater::quitAndInstall().
        Settings::set('pending_update_version', $event->version);
    }
}
