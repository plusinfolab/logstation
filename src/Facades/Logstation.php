<?php

namespace PlusinfoLab\Logstation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void recordLog(array $entry)
 * @method static void recordBatch(array $entries)
 * @method static \PlusinfoLab\Logstation\Models\LogEntry|null find(string $id)
 * @method static \Illuminate\Support\Collection search(array $filters = [], int $perPage = 50)
 * @method static bool delete(string $id)
 * @method static int prune(\DateTimeInterface $before)
 * @method static int clear()
 * @method static array getStatistics()
 * @method static bool isEnabled()
 *
 * @see \PlusinfoLab\Logstation\Logstation
 */
class Logstation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PlusinfoLab\Logstation\Logstation::class;
    }
}
