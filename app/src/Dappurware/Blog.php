<?php

namespace Dappur\Dappurware;

use Interop\Container\ContainerInterface;

class Blog extends Dappurware
{
    protected $categoryId;
    protected $slug;
    protected $parsedTags;
    protected $videoProvider;
    protected $videoId;
    protected $publishAt;

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryId = null;
        $this->slug = null;
        $this->parsedTags = null;
        $this->videoProvider = null;
        $this->videoId = null;
        $this->publishAt = \Carbon\Carbon::now();
        $this->utils = new \Dappur\Dappurware\Utils;
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function addPost()
    {
        $requestParams = $this->container->request->getParams();

        // Validate Title Desc
        $this->validateTitleDesc();

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug();

        // Process Tags
        $this->processTags();

        // Process Video
        $this->processVideo();

        // Process Publish At Date
        $this->publishAt = \Carbon\Carbon::parse($requestParams['publish_at']);

        if ($this->validator->isValid()) {
            $newPost = new \Dappur\Model\BlogPosts;
            $newPost->title = $requestParams['title'];
            $newPost->description = $requestParams['description'];
            $newPost->slug = $this->slug;
            $newPost->content = $requestParams['post_content'];
            $newPost->featured_image = $requestParams['featured_image'];
            $newPost->video_provider = $this->videoProvider;
            $newPost->video_id = $this->videoId;
            $newPost->category_id = $this->categoryId;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            $newPost->status = 0;
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();
            
            foreach ($this->parsedTags as $tag) {
                $addTag = new \Dappur\Model\BlogPostsTags;
                $addTag->post_id = $newPost->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Your blog has been saved successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error saving your blog.');
        return false;
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = new \Dappur\Model\BlogPosts;
        $post = $post->find($postId);

        if (!$post) {
            return false;
        }
        // Validate Title Desc
        $this->validateTitleDesc($post->id);

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug($post->id);

        // Process Tags
        $this->processTags();

        // Process Video
        $this->processVideo();

        // Process Publish At Date
        $this->publishAt = \Carbon\Carbon::parse($requestParams['publish_at']);

        if ($this->validator->isValid()) {
            $post->title = $requestParams['title'];
            $post->description = $requestParams['description'];
            $post->slug = $this->slug;
            $post->content = $requestParams['post_content'];
            $post->featured_image = $requestParams['featured_image'];
            $post->video_provider = $this->videoProvider;
            $post->video_id = $this->videoId;
            $post->category_id = $this->categoryId;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            //Delete Existing Post Tags
            \Dappur\Model\BlogPostsTags::where('post_id', $post->id)->delete();
            
            foreach ($this->parsedTags as $tag) {
                $addTag = new \Dappur\Model\BlogPostsTags;
                $addTag->post_id = $post->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Your blog has been updated successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error updating your blog.');
        return false;
    }

    public function delete()
    {
        $post = new \Dappur\Model\BlogPosts;
        $post = $post->find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = new \Dappur\Model\BlogPosts;
        $post = $post->find($this->container->request->getParam('post_id'));

        if ($post) {
            $post->status = 1;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    public function unpublish()
    {
        $post = new \Dappur\Model\BlogPosts;
        $post = $post->find($this->container->request->getParam('post_id'));

        if ($post) {
            $post->status = 0;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    private function validateTitleDesc($postId = null)
    {
        //Validate Data
        $validateData = array(
            'title' => array(
                'rules' => \Respect\Validation\Validator::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            ),
            'description' => array(
                'rules' => \Respect\Validation\Validator::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = new \Dappur\Model\BlogPosts;
        $checkTitle = $checkTitle->where('title', $this->container->request->getParam('title'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        if ($checkTitle->first()) {
            $this->validator->addError('title', 'Duplicate title found.  This is bad for SEO.');
        }
    }

    private function processCategory()
    {
        $categoryId = $this->container->request->getParam('category');
        
        // Check if category exists by id
        $categoryCheck = new \Dappur\Model\BlogCategories;
        $categoryCheck = $categoryCheck->find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = \Dappur\Model\BlogCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new \Dappur\Model\BlogCategories;
            $addCategory->name = $categoryId;
            $addCategory->slug = $this->utils->slugify($categoryId);
            $addCategory->status = 1;
            $addCategory->save();
            $categoryId = $addCategory->id;
        }

        $this->categoryId = $categoryId;
    }

    private function processSlug($postId = null)
    {
        $slug = $this->utils->slugify($this->container->request->getParam('title'));
        $checkSlug = new \Dappur\Model\BlogPosts;
        $checkSlug = $checkSlug->where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        if ($checkSlug->first()) {
            $this->validator->addError('title', 'Possible duplicate title due to duplicate slug.');
            return false;
        }

        $this->slug = $slug;
        return true;
    }

    private function processTags()
    {
        foreach ($this->container->request->getParam('tags') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = new \Dappur\Model\BlogTags;
                $check = $check->find($value);
                if ($check) {
                    $this->parsedTags[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = $this->utils->slugify($value);
            $slugCheck = \Dappur\Model\BlogTags::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedTags[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newTag = new \Dappur\Model\BlogTags;
            $newTag->name = $value;
            $newTag->slug = $slug;
            if ($newTag->save()) {
                if ($newTag->id) {
                    $this->parsedTags[] = $newTag->id;
                }
            }
        }
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function processVideo()
    {
        $requestParams = $this->container->request->getParams();

        // Handle Featured Video
        if (!empty($requestParams['video_id']) && !empty($requestParams['video_provider'])) {
            $this->videoProvider = $requestParams['video_provider'];
            $this->videoId = $requestParams['video_id'];
        }
        if (!empty($requestParams['video_url'])) {
            $this->videoProvider = Dappur\Dappurware\VideoParser::getVideoId($requestParams['video_url']);
            $this->videoId = Dappur\Dappurware\VideoParser::getVideoId($requestParams['video_url']);
        }

        // Check Featured Image
        if ($this->videoProvider && $this->videoId && empty($requestParams['featured_image'])) {
            $this->validator->addError('featured_image', 'Featured image is required with a video.');
        }
    }
}
