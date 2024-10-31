<?php
namespace NewsParserPlugin\Entities;

/**
 * This class represents the CronData entity, which contains information related to cron job settings and data.
 *
 *
 * @package  Entities
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */
class CronData
{
   
     /**
     * Url of resource that will be source of the posts feed.
     *
     * @var string
     */
    protected $url;
    protected $maxCronCalls;
    protected $maxPostsParsed;
    protected $interval;
    /**
    * Timestamp of last parsed post
    *
    * @var int
    */
    protected $timestamp;
    /**
    * Crone calls counter
    *
    * @var int
    */
    protected $cronCalls;
    /**
    * Parsed posts counter
    *
    * @var int
    */
    protected $parsedPosts;
    /**
    * Timestamp of last parsed post
    *
    * @var string 'active' | 'inactive'
    */
    protected $status;
    /**
     * init function
     *
     * @param string $url Url of resource that will be source of the posts feed.
     */
    public function __construct($cron_data)
    {
    
        $this->assignOptions($cron_data);
    }
    /**
     * Getter function for maximum cron calls.
     *
     * @return false|int
     */
    public function getMaxCronCalls()
    {
        return isset($this->maxCronCalls)?$this->maxCronCalls:false;
    }
     /**
     * Getter function for maximum posts parsed.
     *
     * @return false|int
     */
    public function getMaxPostsParsed()
    {
        return isset($this->maxPostsParsed)?$this->maxPostsParsed:false;
    }
     /**
     * Getter function for parsind interval.
     *
     * @return false|string
     */
    public function getInterval()
    {
        return isset($this->interval)?$this->interval:false;
    }
    /**
     * Return timestamp of last parsed post.
     * 
     * @return false|int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * Return number of cron job calls.
     * 
     * @return false|int
     */
    public function getCronCalls()
    {
        return isset($this->cronCalls)?$this->cronCalls:false;
    }
    /**
     * Increase value of cron calls counter
     * 
     * @return int new value of cron calls counter
     */
    public function increaseCronCalls()
    {
        return $this->cronCalls++;
    }
   /**
     * Return number of parsed posts.
     * 
     * @return false|int
     */
    public function getParsedPosts()
    {
        return isset($this->parsedPosts)?$this->parsedPosts:false;
    }
     /**
     * Increase parsed posts counter
     * 
     * @return int new value of parsed posts counter
     */
    public function increaseParsedPosts()
    {
        return $this->parsedPosts++;
    }
    /**
     * Return status of the cron job.
     * 
     * @return false|string
     */
    public function getStatus()
    {
        return isset($this->status)?$this->status:false;    
    }
    /**
    * Return url of the resource. 
    *
    * @return string
    */
    public function getUrl()
    {
        return $this->url;
    }
    /**
     * Sets a new timestamp for the Cron options model.
     *
     * This method sets a new timestamp for the Cron options model. This timestamp is used to determine
     * whether a post needs to be parsed if the post's pubDate is greater than the value of this property.
     *
     * @param int $timestamp The new timestamp for the Cron options model.
     * @return int new timestamp
     */

     public function setTimestamp($timestamp)
     {
         return $this->timestamp=$timestamp;
     }
    
     /**
     * Assign options to object properties.
     *
     * @param array $options
     * @return void
     */
    protected function assignOptions($options)
    {
        $this->url=$options['url'];
        $this->maxCronCalls=$options['maxCronCalls'];
        $this->maxPostsParsed=$options['maxPostsParsed'];
        $this->interval=$options['interval'];
        $this->timestamp=$options['timestamp'];
        $this->cronCalls=$options['cronCalls'];
        $this->parsedPosts=$options['parsedPosts'];
        $this->status=$options['status']; 
    }
    
     /**
     * Get all options in needed format.
     *
     * 
     * @param string $format accept array|object|json.
     * @return array|object|string
     */
    public function getAttributes($format='array')
    {
       return $this->formatAttributes($format);
    }
    /**
     * Return options data in proper format.
     * 
     * @param string $format accept array|object|json.
     * @return array|object|string
     */
    protected function formatAttributes($format)
    {
        $data=array(
            'url'=>$this->url,
            'maxCronCalls'=>$this->maxCronCalls,
            'maxPostsParsed'=>$this->maxPostsParsed,
            'interval'=>$this->interval,
            'timestamp'=>$this->timestamp,
            'cronCalls'=>$this->cronCalls,
            'parsedPosts'=>$this->parsedPosts,
            'status'=>$this->status
        );
        switch ($format) {
            case 'array':
                return $data;
            case 'object':
                return $this;
            case 'json':
                return json_encode($data);
        }
    }
}