<?php

namespace App\Controllers;

use App\Models\PostRepository;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Helpers\CSRF;
use App\Helpers\Session;
use App\Helpers\Env;

/**
 * Post controller for handling blog post operations
 */
class PostController
{
    private $postRepo;

    public function __construct()
    {
        $this->postRepo = new PostRepository();
    }

    /**
     * Display all posts with pagination
     *
     * @param int $page Current page number
     * @param string|null $search Search query
     * @return array Posts and pagination data
     */
    public function index($page = 1, $search = null)
    {
        try {
            $perPage = Env::getInt('POSTS_PER_PAGE', 10);
            $offset = ($page - 1) * $perPage;

            if ($search) {
                $posts = $this->postRepo->search($search, $perPage, $offset);
                $totalPosts = $this->postRepo->countSearch($search);
            } else {
                $posts = $this->postRepo->findAllOrdered($perPage, $offset);
                $totalPosts = $this->postRepo->count();
            }

            $totalPages = ceil($totalPosts / $perPage);

            return [
                'posts' => $posts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalPosts' => $totalPosts,
                'search' => $search
            ];
        } catch (\Exception $e) {
            Logger::error('Error loading posts: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display single post
     *
     * @param int $id Post ID
     * @return array|null
     */
    public function show($id)
    {
        try {
            return $this->postRepo->findById($id);
        } catch (\Exception $e) {
            Logger::error('Error loading post: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new post
     *
     * @param array $data Post data
     * @return int Post ID
     * @throws \Exception
     */
    public function create(array $data)
    {
        try {
            // Validate CSRF token
            CSRF::verify();

            // Validate input
            $validator = new Validator($data);
            $validator->rule('title', 'required|min:3|max:200', 'Title')
                      ->rule('content', 'required|min:10', 'Content');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $errorMessages[] = implode(', ', $messages);
                }
                throw new \Exception(implode('; ', $errorMessages));
            }

            // Sanitize input
            $postData = [
                'title' => Validator::sanitize($data['title']),
                'content' => Validator::sanitize($data['content'])
            ];

            $postId = $this->postRepo->create($postData);
            Logger::info('Post created', ['id' => $postId]);

            Session::flash('success', 'Post created successfully!');
            return $postId;
        } catch (\Exception $e) {
            Logger::error('Error creating post: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update post
     *
     * @param int $id Post ID
     * @param array $data Post data
     * @return bool
     * @throws \Exception
     */
    public function update($id, array $data)
    {
        try {
            // Validate CSRF token
            CSRF::verify();

            // Validate input
            $validator = new Validator($data);
            $validator->rule('title', 'required|min:3|max:200', 'Title')
                      ->rule('content', 'required|min:10', 'Content');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $errorMessages[] = implode(', ', $messages);
                }
                throw new \Exception(implode('; ', $errorMessages));
            }

            // Sanitize input
            $postData = [
                'title' => Validator::sanitize($data['title']),
                'content' => Validator::sanitize($data['content'])
            ];

            $result = $this->postRepo->update($id, $postData);
            Logger::info('Post updated', ['id' => $id]);

            Session::flash('success', 'Post updated successfully!');
            return $result;
        } catch (\Exception $e) {
            Logger::error('Error updating post: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete post
     *
     * @param int $id Post ID
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            // Validate CSRF token
            CSRF::verify();

            $result = $this->postRepo->delete($id);
            Logger::info('Post deleted', ['id' => $id]);

            Session::flash('success', 'Post deleted successfully!');
            return $result;
        } catch (\Exception $e) {
            Logger::error('Error deleting post: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            throw $e;
        }
    }
}
