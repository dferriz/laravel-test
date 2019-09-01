<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Job;

/**
 * Interface JobInterface
 * @package App\Console\Services\DataImporter\Base\Job
 */
interface JobInterface
{
    public function doJob();
}
