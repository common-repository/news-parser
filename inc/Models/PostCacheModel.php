<?php
namespace NewsParserPlugin\Models;

class PostCacheModel
{
    protected $isLocked = false;
    protected const LOCK_FILE_NAME = 'PostCacheModelLock.txt';
    protected const POST_CACHE_KEY = 'news-parser-post_cache';
    protected const IN_PROGRESS_CACHE_KEY = 'news-parser-post_cache_in_progress';
    protected const IN_PROGRESS_CACHE_LIFETIME = 60 * 10;
    protected const POST_CACHE_LIFETIME = 60 * 60 * 24 * 14;

    /**
     * Get array of cached posts. There are two types of cached posts:
     *  - in progress posts
     *  - created posts
     * All posts are grouped by prefix.Prefix is RSS feed url.
     * Structure of success post array:
     *  [_id] - post id in frontend @int
     *  [link] - post link @string
     *  [title] - post title @string
     *  [post_id] - post id in backend @int
     *  [links]=>[previewLink,editLink,deleteLink] - array of links in post @array
     *  [status]=>draft|false
     * Structure of in error post array:
     *  [_id] - post id in frontend @int
     *  [link] - post link @string
     *  [error] =>[
     *      'code' => error code @int
     *      'message' => error message @string
     *      'data' => error data @array
     *  ]
     *
     * @param string $type Type of cached posts
     * @param string $prefix Prefix of cached posts
     * @return array|mixed
     */
    public function get($type, $prefix)
    {
        if ($this->isLocked()) {
            sleep(0.5);
            return $this->get($type, $prefix);
        }
        switch ($type) {
            case 'in_progress':
                $response = get_transient(self::IN_PROGRESS_CACHE_KEY . '-' . $prefix);
                break;
            case 'created':
                $response = get_transient(self::POST_CACHE_KEY . '-' . $prefix);
                break;
        }
        if (!$response) {
            return [];
        }
        return $response;
    }
    /**
     * Set array of cached posts. There are two types of cached posts:
     * - in progress posts
     * - created posts
     * All posts are grouped by prefix.Prefix is RSS feed url.
     * Structure of success post array:
     * [_id] - post id in frontend @int
     * [link] - post link @string
     * [title] - post title @string
     * [post_id] - post id in backend @int
     * [links]=>[previewLink,editLink,deleteLink] - array of links in post @array
     * [status]=>draft|false
     * Structure of in error post array:
     * [_id] - post id in frontend @int
     * [link] - post link @string
     * [error] =>[
     * 'code' => error code @int
     * 'message' => error message @string
     * 'data' => error data @array
     * ]
     * @param string $type Type of cached posts
     * @param array $posts Array of posts
     * @param string $prefix Prefix of cached posts
     * @return void
     */
    public function set($type, $posts, $prefix)
    {
        if ($this->isLocked()) {
            sleep(0.5);
            return $this->get($type, $prefix);
        }
        $this->setLock();
        switch ($type) {
            case 'in_progress':
                set_transient(self::IN_PROGRESS_CACHE_KEY . '-' . $prefix, $this->removeOldPosts($posts, time() - self::IN_PROGRESS_CACHE_LIFETIME), self::IN_PROGRESS_CACHE_LIFETIME);
                break;
            case 'created':
                set_transient(self::POST_CACHE_KEY . '-' . $prefix, $this->removeOldPosts($posts, time() - self::POST_CACHE_LIFETIME), self::POST_CACHE_LIFETIME);
                break;
        }
        $this->releaseLock();
    }
    public function update($post, $prefix)
    {
        $posts = $this->get('created', $prefix);
        if (!is_array($posts)) {
            $posts = [];
        }
        $posts[$post['link']] = $post;
        $this->set('created', $posts, $prefix);
    }
    public function create($post, $prefix)
    {
        $posts_in_progress = $this->get('in_progress', $prefix);
        if (is_array($posts_in_progress) && array_key_exists($post['link'], $posts_in_progress)) {
            unset($posts_in_progress[$post['link']]);
        }
        $this->set('in_progress', $posts_in_progress, $prefix);
        $this->update(array_merge($post, ['status' => 'created', 'timestamp' => time()]), $prefix);
    }
    public function findByID($id, $prefix, $status = 'created')
    {
        $posts = $this->get($status, $prefix);
        if (!is_array($posts)) {
            return false;
        }
        if (array_key_exists($id, $posts)) {
            return $posts[$id];
        }
        return false;
    }
    public function findByStatus($status, $prefix)
    {
        $posts = $this->get($status, $prefix);
        if (!is_array($posts)) {
            return false;
        }
        return $posts;
    }
    public function findByPrefix($prefix, $status = 'created')
    {
        return $this->findByStatus($status, $prefix);
    }
    public function delete($prefix, $id = null)
    {
        if ($id == null) {
            delete_transient(self::POST_CACHE_KEY . '-' . $prefix);
            return;
        }
        $posts = $this->get('created', $prefix);

        if (array_key_exists($id, $posts)) {
            unset($posts[$id]);
        }
        $this->set($posts, 'created', $prefix);
    }
    public function startProcessing($post, $prefix)
    {
        $posts = $this->get('in_progress', $prefix);
        if (!is_array($posts)) {
            $posts = [];
        }
        $posts[$post['link']] = array_merge($post, ['status' => 'processing', 'timestamp' => time()]);
        $this->set('in_progress', $posts, $prefix);
        $this->delete($post['link'], $prefix);
    }
    public function removeOldPosts($posts, $expiration_timestamp)
    {
        return array_filter($posts, function ($post) use ($expiration_timestamp) {
            return $post['timestamp'] > $expiration_timestamp;
        });
    }
    public function removeErrorPosts($posts)
    {
        return array_filter($posts, function ($post) {
            return !array_key_exists('error', $post);
        });
    }
    private function setLock()
    {
        /*
        $lock_file_name = NEWS_PARSER_PLUGIN_DIR . self::LOCK_FILE_NAME;
        if(!$is_lock_file_exists=file_exists($lock_file_name)&&!$is_writable=is_writable($lock_file_name)) error_log("No file to acquire lock");
        $fp = fopen($lock_file_name, 'r+');
        if (flock($fp, LOCK_EX)) { // Acquire exclusive lock, potentially blocking
            $this->isLocked = true;
        } else {
            error_log("Failed to acquire lock");
            return false; // Lock not acquired
        }
        */
    }

    private function releaseLock()
    {
        /*
        $this->isLocked = false;
        flock($lock_file, LOCK_UN); // Release lock
        fclose($lock_file);
        */
    }

    private function isLocked()
    {
       if ($this->isLocked) return true;
       return false;
    }

}
