<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SystemMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'metric_value',
        'metric_type',
        'category',
        'timestamp',
        'metadata'
    ];

    protected $casts = [
        'metric_value' => 'float',
        'timestamp' => 'datetime',
        'metadata' => 'array'
    ];

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('timestamp', [$start, $end]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('metric_name', $name);
    }

    public static function recordMetric($name, $value, $type = 'gauge', $category = 'system', $metadata = [])
    {
        return self::create([
            'metric_name' => $name,
            'metric_value' => $value,
            'metric_type' => $type,
            'category' => $category,
            'timestamp' => now(),
            'metadata' => $metadata
        ]);
    }

    public static function getLatestValue($name)
    {
        return self::where('metric_name', $name)
            ->latest('timestamp')
            ->value('metric_value');
    }

    public static function getAverageForPeriod($name, $hours = 24)
    {
        return self::where('metric_name', $name)
            ->where('timestamp', '>=', Carbon::now()->subHours($hours))
            ->avg('metric_value');
    }
}