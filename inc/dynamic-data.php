<?php

use Breakdance\DynamicData\StringField;
use Breakdance\DynamicData\StringData;

class UniversalPostUrlField extends StringField
{
    /**
     * Returns the label of the field.
     *
     * @return string
     */
    public function label()
    {
        return 'Universal Post URL';
    }

    /**
     * Returns the category under which the field will be listed.
     *
     * @return string
     */
    public function category()
    {
        return 'Custom Fields';
    }

    /**
     * Returns a unique slug for the field.
     *
     * @return string
     */
    public function slug()
    {
        return 'universal_post_url';
    }

    /**
     * Returns the types of data this field can return.
     *
     * @inheritDoc
     */
    public function returnTypes()
    {
        return ['url'];
    }

    /**
     * Defines the controls for the field.
     *
     * @inheritDoc
     */
    public function controls()
    {
        return [
            \Breakdance\Elements\control('post_id', 'Post ID', [
                'type' => 'number',
                'layout' => 'vertical',
                'description' => 'Enter the ID of the post, page, or custom post type.'
            ]),
        ];
    }

    /**
     * Handler function to retrieve the URL of a post.
     *
     * @param array $attributes
     * @return StringData
     */
    public function handler($attributes): StringData
    {
        $postId = $attributes['post_id'] ?? null;

        // Validate post ID
        if (!is_numeric($postId) || intval($postId) <= 0) {
            return StringData::emptyString();
        }

        $post = get_post(intval($postId));
        if (!$post) {
            return StringData::emptyString();
        }

        $postUrl = get_permalink($post);
        return StringData::fromString($postUrl ?: '');
    }
}

add_action('init', function() {
    if (!function_exists('\Breakdance\DynamicData\registerField') || !class_exists('\Breakdance\DynamicData\Field')) {
        error_log('Breakdance classes or functions not found.');
        return;
    }

    \Breakdance\DynamicData\registerField(new UniversalPostUrlField());
});
