<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class DownloadPlates extends Command
{
    protected $signature = 'plates:download {set?}';
    protected $description = 'Download plate images from live site';

    private $sets = [
        'c36g','c37g','c38g','c39g','c39w','m39g','c49t','c50t','c52m',
        'm53p','m54p','c53c','m54q','m55l','m59p','m60p','s61t','m63p',
        's63g','s66g','m68m','m68q','m68p','m70p','m75p','m78p','s78s',
        'm79p','m80p','m81p','m82p','m83p','m84p','m86p','m87p','m88p',
        'm89p','m90p'
    ];

    public function handle()
    {
        $setToDownload = $this->argument('set');
        $sets = $setToDownload ? [$setToDownload] : $this->sets;
        $baseUrl = 'https://minilicenseplates.com/plates';
        
        foreach ($sets as $set) {
            $this->info("Checking $set...");
            $setDir = public_path("plates/$set");
            
            if (!File::exists($setDir)) {
                File::makeDirectory($setDir, 0755, true);
            }
            
            // Try to get directory listing (may not work depending on server config)
            try {
                $response = Http::get("$baseUrl/$set/");
                if ($response->successful()) {
                    $this->info("  Found directory listing for $set");
                    // Parse HTML to find image links
                    preg_match_all('/href="([^"]+\.(jpg|jpeg|png|gif|webp|bmp))"/i', $response->body(), $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $imageFile) {
                            $this->downloadImage("$baseUrl/$set/$imageFile", $setDir, $imageFile);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->warn("  Could not access $set directory listing: " . $e->getMessage());
            }
        }
        
        $this->info("Done. If images weren't downloaded automatically, copy them manually from your server.");
    }
    
    private function downloadImage($url, $dir, $filename)
    {
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                File::put("$dir/$filename", $response->body());
                $this->line("  Downloaded: $filename");
                return true;
            }
        } catch (\Exception $e) {
            $this->warn("  Failed to download $filename");
        }
        return false;
    }
}
