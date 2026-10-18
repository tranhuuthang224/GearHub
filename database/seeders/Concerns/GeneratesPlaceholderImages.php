<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

trait GeneratesPlaceholderImages
{
    /**
     * Sinh ảnh SVG minh họa, lưu vào disk public.
     * Trả về đường dẫn tương đối (đúng định dạng lưu trong CSDL).
     */
    protected function placeholder(string $path, string $label, string $caption = ''): string
    {
        $palette = ['#2563EB', '#1E293B', '#F97316', '#0F766E', '#7C3AED', '#BE123C'];
        $background = $palette[crc32($path) % count($palette)];

        $lines = explode("\n", wordwrap($label, 20, "\n", false));
        $y = 380 - (count($lines) - 1) * 28;

        $text = '';
        foreach ($lines as $line) {
            $text .= sprintf(
                '<text x="400" y="%d" font-family="Arial, Helvetica, sans-serif" font-size="44" font-weight="700" fill="#FFFFFF" text-anchor="middle">%s</text>',
                $y,
                htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            );
            $y += 56;
        }

        if ($caption !== '') {
            $text .= sprintf(
                '<text x="400" y="%d" font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#FFFFFF" fill-opacity="0.75" text-anchor="middle">%s</text>',
                $y + 12,
                htmlspecialchars($caption, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            );
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800">'
             . '<rect width="800" height="800" fill="' . $background . '"/>'
             . $text
             . '</svg>';

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}