<?php

namespace Tests\Concerns;

trait BuildsLevelFbxFileMaps
{
    /**
     * @return array{type: string, size: string, url: string}
     */
    protected function fbxFileEntry(string $url, ?string $type = null, string|int $size = '0'): array
    {
        if ($type === null) {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $type = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        }

        return [
            'type' => $type,
            'size' => (string) $size,
            'url' => $url,
        ];
    }

    /**
     * @param  array<string, string>  $urlByKey
     * @return array<string, array{type: string, size: string, url: string}>
     */
    protected function fbxFileMap(array $urlByKey, string|int $size = '0'): array
    {
        $map = [];

        foreach ($urlByKey as $key => $url) {
            $baseType = strtolower((string) preg_replace('/_\d+$/', '', (string) $key));
            $map[$key] = $this->fbxFileEntry($url, $baseType !== '' ? $baseType : null, $size);
        }

        return $map;
    }
}
