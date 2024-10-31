<?php
namespace NewsParserPlugin\Entities\Factory;

use NewsParserPlugin\Entities\CronData;


/**
 * Class CronDataFactory
 *
 * This class is responsible for creating instances of the CronData class and validating cron data options.
 *
 * @package  Entities\Factory
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */


class CronDataFactory
{
    /**
     * Default cron data options.
     *
     * @var array
     */
    public const DEFAULT_CRON_DATA = [
        'url' => '',
        'maxCronCalls' => '',
        'maxPostsParsed' => '',
        'interval' => 'hourly',
        'timestamp' => 0,
        'cronCalls' => 0,
        'parsedPosts' => 0,
        'status' => 'inactive',
    ];
    
    /**
     * Create a new instance of the CronData class.
     *
     * @param array $cron_data_array An array containing the cron data.
     * @return CronData The created CronData instance.
     */
    public function create($cron_data_array)
    {
        return new CronData($cron_data_array);
    }
    
    /**
     * Check if the cron data options are valid.
     *
     * @param array $cron_options The cron data options to validate.
     * @return bool True if the cron data options are valid, false otherwise.
     */
    public function isCronDataValid($cron_options)
    {
        if (empty($cron_options) || !is_array($cron_options)) {
            return false;
        }
        
        // Check if all required keys exist
        $required_keys = ['interval', 'maxPostsParsed', 'maxCronCalls', 'url', 'timestamp', 'cronCalls', 'parsedPosts', 'status'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $cron_options)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get the default cron data options.
     *
     * @return array The default cron data options.
     */
    public function getDefaultCronData()
    {
        return self::DEFAULT_CRON_DATA;
    }
}
