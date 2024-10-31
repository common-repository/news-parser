<?php
namespace NewsParserPlugin\Parser\Modifiers\PostModifiers;

use NewsParserPlugin\Models\PostModel;
use NewsParserPlugin\Interfaces\MiddlewareInterface;

/**
 * Modifier function that adds a post thumbnail to the post.
 *
 * This function takes a PostModel object and adds a post thumbnail to it using the `addPostThumbnail()` method of the PostModel class.
 *
 * @param PostModel $post The PostModel object to which the post thumbnail will be added.
 * @return void
 */
class AddPostThumbnailModifier implements MiddlewareInterface
{
function __invoke ($post,$options)
{
    if($options['extraOptions']['addFeaturedMedia']) $post->addPostThumbnail();
    return $post;
}
}