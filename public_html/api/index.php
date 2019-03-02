<?php
require_once dirname(__DIR__, 2) . "/php/classes/JsonObjectStorage.php";

//prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {


	$method = $_SERVER["HTTP_X_HTTP_METHOD"] ?? $_SERVER["REQUEST_METHOD"];

	$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$postUserId = filter_input(INPUT_GET, "postUserId", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

	if($method === "GET") {
		//set XSRF cookie
		setXsrfCookie();

		$userJson = @file_get_contents("users.json");

		if($userJson === false) {
			throw(new RuntimeException("Unable to read diceware data", 500));
		}
		$users = json_decode($userJson);

		if(empty($id) === false) {
			foreach($users as $user) {
				if($user->userId === $id) {
					$reply->data =$user;
					break;
				}
			}
		} elseif (empty($userPostId) ===false) {

			foreach($users as $user) {
				if($user->userId === $postUserId) {

					$postJson = @file_get_contents("post.json");

					if($postJson === false) {
						throw(new RuntimeException("Unable to read diceware data", 500));
					}

					$posts = json_decode($postJson);
					$postArray = [];

					foreach($posts as $post) {
						if($post->postUserId === $postUserId) {
							$postArray[] = $post;
						}
					}
					$reply->data = [
						"user" => $user,
						"posts" => $postArray
					];
				} else{
					$reply->data = [];
				}
			}
		}
	} else {
		throw (new InvalidArgumentException("Invalid HTTP method request", 418));
	}
} catch(\Exception | \TypeError $exception) {
	$reply->status = $exception->getCode();
	$reply->message = $exception->getMessage();
}
// encode and return reply to front end caller
header("Content-type: application/json");
echo json_encode($reply);