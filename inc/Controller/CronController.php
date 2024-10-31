<?php
namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;
use NewsParserPlugin\Utils\ResponseFormatter;
use NewsParserPlugin\Interfaces\ModelInterface;
use NewsParserPlugin\Entities\CronData;
/**
 * Class CronController
 *
 * This class is responsible for managing cron jobs and their options.
 *
 * @package Controller
 * @author  Evgeny S.Zalevskiy <2600@ukr.net>
 * @license MIT <https://opensource.org/licenses/MIT>
 */
class CronController 
{
    /**
     * Instance of CronModel class.
     *
     * @var CronModel
     */
    protected $cronDataModel;
    
    /**
     * CronController constructor.
     *
     * @param ModelInterface $cron_model The model interface implementation for cron jobs.
     */
    public function __construct(ModelInterface $cron_data_model)
    {
        $this->cronDataModel = $cron_data_model;
    }
    
    /**
     * Creates cron options data and saves it.
     * 
     * @param array $cron_data_array An array containing the cron data.
     * @return array The attributes of the created cron data object.
     */
    public function create(array $cron_data_array): array
    {
        $cron_timestemp = time();
        
        if ($cron_data_array['status'] == 'active') {
            $cron_data_array['timestamp'] = $cron_timestemp;
        }
        
        $cron_data = $this->cronDataModel->create($cron_data_array['url'], $cron_data_array);
        
        if ($this->isCronExists($cron_data->getInterval()) === false) {
            $this->setCron($cron_data->getInterval(), $cron_timestemp);
        }
        
        return $cron_data->getAttributes();
    }
    
    /**
     * Get cron job options data.
     * 
     * @param string|null $url The URL of the cron job. If provided, only the cron job with the specified URL will be returned. Otherwise, all cron jobs will be returned.
     * @return array The cron job options data.
     */
    public function get(?string $url = null): array
    {
        $crons_data = $this->cronDataModel->getAll();
        
        if ($url === null) {
            return $crons_data;
        }
        
        if ($cron_data = $this->cronDataModel->findByID($url)) {
            return $cron_data->getAttributes();
        }
        
        $default_cron_data = $this->cronDataModel->getDefaultCronData();
        $default_cron_data['url'] = $url;   
        
        return $default_cron_data;
    }
    
    /**
     * Delete cron options.
     * 
     * @param string $url The URL of the cron job to delete.
     * @return array The default cron job data after deletion.
     */
    public function delete(string $url): array
    {
        $cron_data = $this->cronDataModel->findByID($url);
        
        if (!$cron_data) {
            return $this->cronDataModel->getDefaultCronData();
        }
        
        $deleted_cron_interval = $cron_data->getInterval();
        $this->cronDataModel->delete($url);
        
        if (!$this->isIntervalActive($this->cronDataModel->findByInterval($deleted_cron_interval))) {
            $this->unsetCron($deleted_cron_interval);
        }
        
        $default_cron_data = $this->cronDataModel->getDefaultCronData();
        $default_cron_data['url'] = $url;
        
        return $default_cron_data;
    }
    
    /**
    * Check if there are any active cron jobs with the specified interval.
    *
    * @param array $crons_data An array of cron job data.
    * @return bool True if there are active cron jobs with the specified interval, false otherwise.
    */
    protected function isIntervalActive(array $crons_data): bool
    {
        $active_crons = array_filter($crons_data, function($cron_data_array) {
            return $cron_data_array->getInterval() == 'active';
        });
        
        return count($active_crons) > 0;
    }
    
    /**
     * Check if a cron job with the specified interval exists.
     *
     * @param string $interval The interval of the cron job to check.
     * @return false|int The timestamp if the cron job exists, or false if the cron job does not exist.
     */
    protected function isCronExists(string $interval)
    {
        return wp_next_scheduled(NEWS_PARSER_CRON_ACTION_PREFIX . $interval, array($interval));
    }
    
    /**
    * Schedule a new cron job with the specified interval.
    *
    * @param string $interval The interval of the new cron job, in seconds.
    * @param int $cron_timestemp The timestamp of thenext scheduled run of the cron job, in Unix timestamp format.
     * @return void
     */
    protected function setCron(string $interval, int $cron_timestemp): void
    {
        wp_schedule_event($cron_timestemp, $interval, NEWS_PARSER_CRON_ACTION_PREFIX . $interval, array($interval));
    }
    
    /**
     * Unschedule a cron job with the specified interval.
     *
     * @param string $interval The interval of the cron job to unschedule.
     * @return void
     */
    protected function unsetCron(string $interval): void
    {
        $timestamp = $this->isCronExists($interval);
        
        if ($timestamp !== false) {
            wp_unschedule_event($timestamp, NEWS_PARSER_CRON_ACTION_PREFIX . $interval, array($interval));
        }
    }
}
