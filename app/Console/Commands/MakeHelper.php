<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeHelper extends Command
{
    protected $signature = 'make:helper';
    protected $description = 'Create a CustomHelper.php in app/Helpers';

    public function handle()
    {
        $helperPath = app_path('Helpers/CustomHelper.php');

        if (!File::exists(app_path('Helpers'))) {
            File::makeDirectory(app_path('Helpers'), 0755, true);
        }

        if (!File::exists($helperPath)) {
            File::put($helperPath, <<<EOT
<?php

namespace App\Helpers;

class CustomHelper
{
    // Add your static helper functions here
}
EOT
            );

            $this->info('CustomHelper.php created in app/Helpers.');
        } else {
            $this->error('CustomHelper.php already exists.');
        }

        // Add to composer.json if not already present
        $composer = base_path('composer.json');
        $content = File::get($composer);

        if (!str_contains($content, 'app/Helpers/CustomHelper.php')) {
            $json = json_decode($content, true);
            $json['autoload']['files'][] = 'app/Helpers/CustomHelper.php';
            File::put($composer, json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->info('Added CustomHelper.php to composer.json autoload files.');
            $this->info('Run `composer dump-autoload` to apply changes.');
        } else {
            $this->info('CustomHelper.php already registered in composer.json.');
        }

        return 0;
    }
}
