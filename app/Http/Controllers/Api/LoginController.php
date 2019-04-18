<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use Facebook;
use GuzzleHttp;
use Illuminate\Http\Request;
use function GuzzleHttp\json_decode;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class LoginController extends Controller
{

    const FIREBAE_DB_URL = "https://namle-fb.firebaseio.com/";

    const DF_ACCESS_TOKEN = "EAAGNO4a7r2wBADxRvS3t4l1ciDzLLZB0iNkR0spiZBx1DgagLIFP6ofDXr6z5cm2tsZC8YIhvtCizcClBnrHoh44X3tIy9ed5jZCNgLtmVYQ55lsD8bulez6kvzNOMDkVOQRUUB2VMGzxlMJZBWIKhE5hfrsYpyWjw3gTnRgatgZDZD";

    function login(Request $request)
    {
        $uid = $request->uid;
        $accessToken = $request->accessToken != null ? $request->accessToken : self::DF_ACCESS_TOKEN;
        $client = new GuzzleHttp\Client();

        $url = "https://graph.facebook.com/v3.2/"
        . $uid
        . "?access_token="
        . $accessToken
        . "&debug=all&fields=friends.limit(5000)&format=json&method=get&pretty=0&suppress_http_code=1&transport=cors";

        $res = $client->get($url);

        $statusCode = $res->getStatusCode(); // 200
        $body = $res->getBody();

        $data = json_decode($body)->friends->data;

        $database = $this->getDatabase();

        $database
            ->getReference('users' . $uid)
            ->set($data);
        $children = $database->getReference()->getChildKeys();

        return response()->json($children);
    }

    function getFriends(Request $request){
        $uid = $request->uid;

        $database = $this->getDatabase();

        $data = $database->getReference("users" . $uid) ->getSnapshot()->getValue();
        return response()->json(["friends" => $data]);
    }

    private function getDatabase(){
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/firebaseConfig.json');
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->withDatabaseUri(self::FIREBAE_DB_URL)
            ->create();
        $database = $firebase->getDatabase();
        return $database;
    }
}
