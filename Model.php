<?php

namespace Filesystem\Models;

use Filesystem\Factories\FileFactory;
use Foundation\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Examples extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    public string $rootDirectory = '/';

    protected $guarded = [];

    public function getCloudPath(bool $withResize = true): string
    {
        $isImage = Str::startsWith($this->mimeType, 'image/');
        $invalidData = ['image/svg+xml'];
        $isValid = !in_array($this->mimeType, $invalidData);

        $cloudBucket = '';
        if (Storage::getDefaultDriver() === 'minio') {
            $cloudBucket = Storage::disk('minio')->getConfig()['bucket'];
        }
        $path = trim($this->url, '/');

        return "/static/$cloudBucket/$path";
    }

    protected function mimeType(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Storage::exists($attributes['url']) ? Storage::mimeType($attributes['url']) : 'mime',
        );
    }

    protected static function newFactory(): FileFactory
    {
        return FileFactory::new();
    }
}
