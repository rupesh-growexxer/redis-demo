<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    protected $post;
    public $cacheKey = null;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * The index function retrieves paginated blog posts data and returns it as a JSON response,
     * handling any exceptions that may occur.
     *
     * @param Request request The `index` function is a controller method that handles a request to
     * fetch paginated blog posts. Here's a breakdown of the code:
     *
     * @return The `index` function returns a JSON response containing paginated posts data fetched
     * using the `fetchPaginatedBlogs` method of the `Post` model. If the operation is successful, it
     * returns a message indicating paginated posts data along with the data and a status code of 200.
     * If an exception occurs during the process, it returns an error message from the exception along
     * with a status code
     */
    public function index(Request $request)
    {
        try {            
            $postsData = $this->getAllPostsData();
            
            return response()->json(['message'=> 'paginated posts data.', 'data'=> $postsData, 'code'=>200]);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    public function getAllPostsData()
    {
        try {
            $cacheKey = 'all_post';
            $postsData = $this->getCachedData($cacheKey);
            if(empty($postsData)) {
                $postsData = Cache::remember($cacheKey, 600, function () {
                    return DB::table('posts')->get()->toArray();
                });
            }
            return $postsData;
        } catch (Exception $e) {
            return $this->logError($e->getMessage());
        }
    }

    public function clearCache($cacheKey)
    {
        try {
            if(empty($cacheKey)){
                return $this->logError('Cache1 key missing.');
            }        
            $forgetCache = Cache::forget($cacheKey);

            return $forgetCache;
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }            
    }

    public function getCachedData($cacheKey)
    {
        try {
            if(empty($cacheKey)){
                return $this->logError('Cache key missing.');
            }

            $cacheData =    Cache::get($cacheKey);
            return $cacheData;
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }
    }


    public function logError($message = null)
    {
        return response()->json(['message'=> $message, 'code'=>404]);
    }

    public function logSucess($data=[], $message = null)
    {
        return response()->json(['message'=> $message,'data' => json_encode($data), 'code'=>200]);
    }

    /**
     * The function stores a new post with title and content provided in the request, handling
     * validation and error responses.
     *
     * @param Request request The `store` function in the code snippet is used to store a new post
     * based on the data provided in the request. Here's a breakdown of the parameters used in the
     * function:
     *
     * @return If the validation passes and the data is successfully inserted into the database, a JSON
     * response will be returned with a success message, the inserted data, and a status code of 200.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $requestData = $request->all();
            $createdPost = Post::create($requestData);

            return response()->json(['message'=> 'Data inserted sucessfully.', 'data'=> $createdPost, 'statusCode'=>200]);
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }
    }

    /**
     * The function "show" retrieves a post by its ID and returns it as JSON, handling errors
     * appropriately.
     *
     * @param id The `show` function is used to retrieve and display a specific post based on the
     * provided `id`. The function attempts to find the post with the given `id` using the
     * `Post::find()` method. If the post is found, it is returned as a JSON response. If
     *
     * @return If the post with the given ID is found, the function will return a JSON response with
     * the post data. If the post is not found, it will return a JSON response with an error message
     * "Post not found" and a status code of 404. If an exception occurs during the process, it will
     * return a JSON response with the exception message and a status code of 404.
     */
    public function show($id)
    {
        try {
            $cacheKey = 'post::'.$id;
            $postsData = $this->getCachedData($cacheKey);
            if(empty($postsData)) {
                $postData = (array) DB::table('posts')->where('id', $id)->first();
                $postsData = Cache::set($cacheKey, 600, json_encode($postData));
            }
            
            if (! $postData) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            return response()->json($postData);
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }
    }

    /**
     * This PHP function updates a post based on the provided request data and returns a JSON response
     * with success or error messages.
     *
     * @param Request request The `Request ` parameter in the `update` function represents the
     * incoming HTTP request containing data that needs to be used or updated. This data can include
     * form inputs, file uploads, headers, and other request information.
     * @param id The `id` parameter in the `update` function represents the unique identifier of the
     * post that you want to update. It is used to find the specific post in the database that you want
     * to update.
     *
     * @return The `update` function is returning a JSON response. If the post is not found, it returns
     * a JSON response with a message indicating that the post was not found, along with an empty data
     * array and a status code of 404. If the validation fails, Laravel will automatically return a
     * JSON response with the validation errors. If the update is successful, it returns a JSON
     * response with a success message
     */
    public function update(Request $request, $id)
    {
        try {
            $postDetails = Post::find($id);
            if (! $postDetails) {
                return response()->json(['meesage' => 'Post not found.', 'data' => [], 'statusCode'=> 404]);
            }

            $this->validate($request, [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
            ]);

            $postDetails->update($request->all());

            return response()->json(['message'=> 'Data updated sucessfully.', 'data'=> $postDetails, 'statusCode'=>200]);
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }
    }

    /**
     * The function `destroy` deletes a post by its ID and returns a JSON response indicating success
     * or failure.
     *
     * @param id The `destroy` function you provided is used to delete a post based on the given `id`.
     * The `id` parameter represents the unique identifier of the post that needs to be deleted from
     * the database.
     *
     * @return The `destroy` function is returning a JSON response. If the post with the given `` is
     * found and successfully deleted, it returns a JSON response with a success message, empty data
     * array, and a status code of 200. If the post is not found, it returns a JSON response with a
     * message indicating that the post was not found, an empty data array, and a status code
     */
    public function destroy($id)
    {
        try {
            $postObj = Post::find($id);
            if (! $postObj) {
                return response()->json(['meesage' => 'Post not found', 'data' => [], 'statusCode'=> 404]);
            }
            $postObj->delete();

            return response()->json(['meesage' => 'Data deleted sucessfully.', 'data' => [], 'statusCode'=> 200]);
        } catch (\Exception $e) {
            return $this->logError($e->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
        $query = $request->get('query');

        $blogs = Post::where('title', 'LIKE', '%'.$query.'%')->get();

        return response()->json($blogs);
    } catch (\Exception $e) {
        return $this->logError($e->getMessage());
    }
    }
}
