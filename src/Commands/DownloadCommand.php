<?php

namespace Blinq\Icons\Commands;

use Blinq\Icons\IconPack;
use Illuminate\Console\Command;

/**
 * This file contains the main command for the Synth application.
 * It handles the execution of the command and manages the Synth, MainMenu, and Modules instances.
 */
class DownloadCommand extends Command
{
    public $signature = 'blinq-icons:download {--max=}';

    public $description = 'Download all the icons in the all the available sets';

    public function handle(): int
    {
        $max = $this->option('max', 0);

        $packs = collect(IconPack::all())->shuffle();

        $counter = 0;
        /**
         * @var IconPack $pack
         */
        foreach($packs as $pack) {
            $list = $pack->list()->shuffle();
            
            foreach($list as $icon) {
                $counter++;
                $this->info("Downloading {$icon->name} from {$pack->getName()}");

                $pack->getIconPath($icon->name, $icon->variant);
                
                if($max > 0 && $counter >= $max) {
                    break;
                }
            }

        }

        return 0;
    }
}
