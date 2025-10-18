<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreatePlaceholderImage extends Command
{
    protected $signature = 'make:placeholder {--width=1200} {--height=800}';

    protected $description = 'Create a placeholder image for pageant covers';

    public function handle()
    {
        $width = $this->option('width');
        $height = $this->option('height');
        $directory = public_path('images/placeholders');
        $filePath = $directory.'/pageant-cover.jpg';

        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Create a new image
        $image = imagecreatetruecolor($width, $height);

        // Allocate colors
        $darkBlue = imagecolorallocate($image, 31, 41, 55); // #1F2937
        $teal = imagecolorallocate($image, 56, 189, 178); // #38BDB2
        $tealTransparent = imagecolorallocatealpha($image, 56, 189, 178, 80);
        $white = imagecolorallocate($image, 255, 255, 255);
        $lightGray = imagecolorallocate($image, 226, 232, 240); // #E2E8F0

        // Fill the background
        imagefill($image, 0, 0, $darkBlue);

        // Create a gradient effect
        for ($i = 0; $i < $height; $i += 2) {
            $alpha = intval(80 * ($i / $height));
            $gradientColor = imagecolorallocatealpha($image, 56, 189, 178, $alpha);
            imageline($image, 0, $i, $width, $i, $gradientColor);
        }

        // Add text
        $text1 = 'Pageant Cover Image - Coming Soon';
        $text2 = 'The organizer will add event photos soon';

        // Add text with built-in fonts
        // Calculate center position based on text length and font size
        $font = 5; // Largest built-in font
        $text1Width = strlen($text1) * imagefontwidth($font);
        $text2Width = strlen($text2) * imagefontwidth($font - 1);

        imagestring($image, $font, ($width / 2) - ($text1Width / 2), ($height / 2) - 20, $text1, $white);
        imagestring($image, $font - 1, ($width / 2) - ($text2Width / 2), ($height / 2) + 20, $text2, $lightGray);

        // Save the image
        imagejpeg($image, $filePath, 90);
        imagedestroy($image);

        $this->info("Placeholder image created at: $filePath");

        return Command::SUCCESS;
    }
}
