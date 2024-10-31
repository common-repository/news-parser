<?php
namespace NewsParserPlugin\Models;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Interfaces\ModelInterface;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Entities\Factory\CronDataFactory;

/**
 * Class CronDataModel
 *
 * This class operates with Cron options and implements the ModelInterface.
 *
 * @package  Models
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */
class CronDataModel implements ModelInterface
{
    /**
     * The table name for storing cron options.
     */
    protected const CRONE_OPTIONS_TABLE = NEWS_PURSER_PLUGIN_CRON_OPTIONS_NAME;
    
    /**
     * The CronDataFactory instance.
     *
     * @var CronDataFactory
     */
    protected $cronDataFactory;
    
    /**
     * CronDataModel constructor.
     *
     * @param CronDataFactory $cron_data_factory The CronDataFactory instance.
     */
    public function __construct(CronDataFactory $cron_data_factory)
    {
        $this->cronDataFactory = $cron_data_factory;
    }
    
    /**
     * Save options using the WordPress function update_option.
     *
     * @param string $url The URL associated with the cron options.
     * @param array $cron_data_array An array containing the cron data options.
     * @return bool True if the options were saved successfully, false otherwise.
     * @throws MyException if the options have the wrong format.
     */
    public function create($url, $cron_data_array)
    {
        return $this->update($url, $cron_data_array);
    }
    
    /**
     * Update options using the WordPress function update_option.
     *
     * @param string $url The URL associated with the cron options.
     * @param array $cron_data_array An array containing the cron data options.
     * @return bool True if the options were updated successfully, false otherwise.
     * @throws MyException if the options have the wrong format.
     */
    public function update($url, $cron_data_array)
    {
        if (!$this->cronDataFactory->isCronDataValid($cron_data_array)) {
            throw new MyException(Errors::text('OPTIONS_WRONG_FORMAT'), Errors::code('BAD_REQUEST'));
        }
        
        $crons = $this->getAll();
        $crons[$url] = $cron_data_array;
        $result = update_option(self::CRONE_OPTIONS_TABLE, $crons, '', 'no');
        
        return $this->cronDataFactory->create($cron_data_array);
    }
    
    /**
     * Delete options associated with a specific URL using the WordPress function update_option.
     *
     * @param string $url The URL associated with the cron options to delete.
     * @return bool True if the options were deleted successfully, false otherwise.
     */
    public function delete($url)
    {
        $crons = $this->getAll();
        if (array_key_exists($url, $crons)) {
            unset($crons[$url]);
        }
        return update_option(self::CRONE_OPTIONS_TABLE, $crons);
    }
    
    /**
     * Delete all cron options using the WordPress function delete_option.
     *
     * @return bool True if all cron options were deleted successfully, false otherwise.
     */
    public function deleteAll()
    {
        return delete_option(self::CRONE_OPTIONS_TABLE);
    }
    
    /**
     * Get all saved cron options using the WordPress function get_option.
     *
     * @return false|array An array containing all saved cron options, or false if no options are found.
     */
    public function getAll()
    {
        $result=get_option(self::CRONE_OPTIONS_TABLE);
        return is_array($result)?$result:[];
    }
    
    /**
     * Get cron options associated with a specific URL.
     *
     * @param string $url The URL associated with the cron options to find.
     * @return false|CronData The CronData instance associated with the URL, or false if not found.
     */
    public function findByID($url)
    {
        $crons = $this->getAll();
        if (array_key_exists($url, $crons)) {
            return $this->cronDataFactory->create($crons[$url]);
        }
        return false;
    }
    
    /**
     * Get cron options that match a specific interval.
     *
     * @param string $interval The interval to filter by.
     * @return array An array of CronData instances that match the interval.
     */
    public function findByInterval($interval)
    {
        $crons = $this->getAll();
        $cron_data_factory = $this->cronDataFactory;
        if (count($crons) == 0) {
            return $crons;
        }
        
        $filtered_crons_data = array_filter($crons, function ($cron_data_array) use ($interval) {
            return $cron_data_array['interval'] == $interval;
        });
        
        return array_map(function ($cron_data_array) use ($cron_data_factory) {
            return $cron_data_factory->create($cron_data_array);
        }, $filtered_crons_data);
    }
    
    /**
     * Get the default cron datausing the CronDataFactory.
     *
     * @return CronData The default CronData instance.
     */
    public function getDefaultCronData()
    {
        return $this->cronDataFactory->getDefaultCronData();
    }
    
    /**
     * Check if the given options array is valid.
     *
     * @param array $options The options array to validate.
     * @return bool True if the options array is valid, false otherwise.
     */
    protected function isOptionsValid($options)
    {
        if (empty($options)||!is_array($options)) {
            return false;
        }
        // Check if all required keys exist
        $required_keys = ['interval', 'maxPostsParsed', 'maxCronCalls', 'url', 'timestamp', 'cronCalls', 'parsedPosts', 'status' ];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $options)) {
                return false;
            }
        }
        return true;
    }
}
