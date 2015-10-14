<?php
/**
 * Created by PhpStorm.
 * User: Ralf
 * Date: 2015-10-13
 * Time: 9:14 PM
 */

//We use already made Twitter OAuth library
//https://github.com/mynetx/codebird-php
require_once (dirname(__FILE__).'/../../vendor/codebird.php');

try {
    //Twitter OAuth Settings
    $CONSUMER_KEY = 'xJdNWRstytz2o2LG8y6ZWtAEw';
    $CONSUMER_SECRET = '3sx5wtf5PuBj64kqNW6Vsi5Ns32tNqBLUjslBbkPb9WJtxPpcS';
    $ACCESS_TOKEN = '2805976755-eG7vCu4WoEOKN2rXyoUaD1cwImTYLowr0KL8MFe';
    $ACCESS_TOKEN_SECRET = 'R6aavo17oBCq1RPu2K8cfrVKxmq1yiKC7kPz871qZ3cLD';

    //Get authenticated
    Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);

    $cb = Codebird\Codebird::getInstance();
    $cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);

    //retrieve posts
    $q = 'DJRayKay';//$_POST['q'];
    $api = 'statuses_userTimeline';

    //https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
    $params = array(
        'screen_name' => $q,
        'q' => $q,
        'count' => 500
    );

    //Make the REST call
    $data = (array)$cb->$api($params);

    var_dump($data[0]->created_at);die;

    //Output result in JSON, getting it ready for jQuery to process
    echo json_encode($data);
}catch(Exception $e){
    var_dump($e->getMessage());
}
?>