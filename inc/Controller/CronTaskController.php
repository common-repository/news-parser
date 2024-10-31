<?php
namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Controller\EventController;
use NewsParserPlugin\Models\CronDataModel;
use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Interfaces\ModelInterface;
/**
 * Class CronTaskController
 *
 * Controller for handling cron tasks and parsing posts.
 * 
 * @package Controller
 * @author  Evgeny S.Zalevskiy <2600@ukr.net>
 * @license MIT <https://opensource.org/licenses/MIT>
 */
class CronTaskController
{

    protected $event;
    protected $cronDataModel;

    /**
     * CronTaskController constructor.
     *
     * @param EventController $event            The event controller instance.
     * @param ModelInterface $cron_data_model   The cron data model instance.
     */
    public function __construct(EventController $event, ModelInterface $cron_data_model)
    {
        $this->event = $event;
        $this->cronDataModel = $cron_data_model;
    }

    /**
     * Callback function for the cron task.
     *
     * @param int $interval The interval for the cron task.
     *
     * @return int The number of parsed posts.
     */
    public function cronTaskCallback($interval)
    {
        $parsed_posts_counter = 0;
        $crons = $this->cronDataModel->findByInterval($interval);

        foreach ($crons as $cron_data) { 
            if ($cron_data->getCronCalls() < $cron_data->getMaxCronCalls()) {
                $cron_options_post_counter = $cron_data->getParsedPosts();
                $rss_list = $this->event->trigger('list:get', array($cron_data->getUrl()));
                $last_parsed_post_timestamp = $cron_data->getTimestamp();

                $this->parsePosts(array_filter($rss_list, function ($post_data) use ($last_parsed_post_timestamp) {
                    return strtotime($post_data['pubDate']) > $last_parsed_post_timestamp;
                }), $cron_data);

                $cron_data->increaseCronCalls();
                $cron_data = $this->cronDataModel->update($cron_data->getUrl(), $cron_data->getAttributes());
                $parsed_posts_counter += $cron_data->getParsedPosts() - $cron_options_post_counter;
            }
        }

        return $parsed_posts_counter;
    }

    /**
     * Parse the posts from the RSS data.
     *
     * @param array $posts_rss_data The RSS data containing the posts.
     * @param mixed $cron_data      The cron data.
     *
     * @return mixed The updated cron data.
     */
    protected function parsePosts($posts_rss_data, $cron_data)
    {
        // To avoid sorting data by pubDate, use $latest_timestamp
        $latest_timestamp = $cron_data->getTimestamp();

        foreach (array_reverse($posts_rss_data) as $post_data) {
            if ($cron_data->getParsedPosts() < $cron_data->getMaxPostsParsed()) {
                try {
                    $this->event->trigger('posts:autopilot-parse', array($post_data['link'], $cron_data->getUrl()));
                } catch (MyException $e) {
                    // ToDo: should add some logging
                    continue;
                }

                $cron_data->increaseParsedPosts();
                $post_timestamp = strtotime($post_data['pubDate']);

                if ($latest_timestamp < $post_timestamp) {
                    $latest_timestamp = $post_timestamp;
                }
            } else {
                break;
            }
        }

        return $cron_data->setTimestamp($latest_timestamp);
    }
}
