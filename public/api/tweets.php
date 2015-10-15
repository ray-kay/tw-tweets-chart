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
    $tweets = (array)$cb->$api($params);
    //counts how many tweets checked
    $tweetsChecked = 0;

    if($tweets){
        //init empty list foreach hour
        $hours = array();
        for($i = 0; $i < 24; $i++){
            $hours[sprintf('%02d',$i)] = 0;
        }

        /**
         * get user utc offset
         * As Twitter saves all tweets in GMT and doesn't provide the users time zone foreach tweet
         * we assume that all tweets are made to the user location/time zone
         */
        $userUtcOffset = $tweets[0]->user->utc_offset;
        $userTimeZoneOffset = null;
        $subTimeZoneOffset = false;

        if($userUtcOffset){
            /**
             * create a DateInterval to add/sub this later foreach tweet,
             * negative values are not allowed here, thats why we have to save this in $subTimeZoneOffset
             */
            $userTimeZoneOffset = new DateInterval('PT'.abs($userUtcOffset).'S');
            $subTimeZoneOffset = $userUtcOffset < 0;
        }

        //get hour values foreach tweet
        foreach($tweets as $tweet) {
            if(!empty($tweetData->created_at)) {
                $hour = getHourOfTweet($tweet, $userTimeZoneOffset, $subTimeZoneOffset);
                $hours[$hour]++;
                $tweetsChecked++;
            }
        }
        //var_dump($hours);die;
    }


    //Output result in JSON, getting it ready for jQuery to process
    echo json_encode(array(
        'data' => $hours,
        'count' => $tweetsChecked
    ));

}catch(Exception $e){
    var_dump($e->getMessage());
}


function getHourOfTweet($tweetData, DateInterval $timeZoneOffset = null, $subTimeZoneOffset = false){
    $dateTime = new DateTime($tweetData->created_at);

    if($timeZoneOffset) {
        $subTimeZoneOffset ? $dateTime->sub($timeZoneOffset) : $dateTime->add($timeZoneOffset);
    }
    //echo $tweetData->created_at.' - '.$dateTime->format('H').'<br>';
    return $dateTime->format('H'); //all times are gmt!
}
?>
