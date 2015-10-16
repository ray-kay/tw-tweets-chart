<?php
/**
 * simple php file to retrieve user timeline (tweets, retweets) for a twitter user
 * call with post param 'q' for the twitter user/screen name
 *
 * Created by PhpStorm.
 * User: Ralf
 * Date: 2015-10-13
 * Time: 9:14 PM
 */

//We use already made Twitter OAuth library
//https://github.com/mynetx/codebird-php
require_once (dirname(__FILE__).'/../../vendor/codebird.php');

class Tweets
{
    /**
     * @var Codebird\Codebird
     */
    private $cb;

    /**
     * constructor
     */
    public function __construct()
    {
        //Twitter OAuth Settings
        $CONSUMER_KEY = '03Lk4SWxdbnkDwb06qbO3Upt6';
        $CONSUMER_SECRET = 'd6biVxQRSnRiPJ5H4GLo268eDwO9ZES7BQ4N2iTs5KqiSeFCPD';
        $ACCESS_TOKEN = '2805976755-eG7vCu4WoEOKN2rXyoUaD1cwImTYLowr0KL8MFe';
        $ACCESS_TOKEN_SECRET = 'R6aavo17oBCq1RPu2K8cfrVKxmq1yiKC7kPz871qZ3cLD';

        //Get authenticated
        Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);

        $this->cb = Codebird\Codebird::getInstance();
        $this->cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);
    }

    /**
     * @param string $username
     * @return string
     */
    public function getTweetsByHour($username)
    {
        $hours = array();

        $params = array(
            'screen_name' => $username,
            'q' => $username,
            'count' => 500
        );

        //Make the REST call
        //https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
        $tweets = (array)$this->cb->statuses_userTimeline($params);

        //counts how many tweets checked
        $tweetsChecked = 0;

        //also return the users time zone
        $userTimeZone = '';

        if($tweets){
            //init empty list foreach hour
            for($i = 0; $i < 24; $i++){
                $hours[$i] = 0;//rand(0,50);
            }

            /**
             * get user utc offset
             * As Twitter saves all tweets in GMT and doesn't provide the users time zone foreach tweet
             * we assume that all tweets are made to the user location/time zone
             */
            $userUtcOffset = $tweets[0]->user->utc_offset;
            $userTimeZoneOffset = null;
            $subTimeZoneOffset = false;
            $userTimeZone = $tweets[0]->user->time_zone;

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
                if(!empty($tweet->created_at)) {
                    $hour = $this->getHourOfTweet($tweet, $userTimeZoneOffset, $subTimeZoneOffset);
                    $hours[intval($hour)]++;
                    $tweetsChecked++;
                }
            }
        }

        //Output result in JSON, getting it ready for jQuery to process
        return json_encode(array(
            'hours' => $hours,
            'count' => $tweetsChecked,
            'time_zone' => $userTimeZone
        ));
    }

    /**
     * return the hour value of a post/tweet
     * @param Object $tweetData
     * @param DateInterval|null $timeZoneOffset
     * @param bool|false $subTimeZoneOffset
     * @return string
     */
    private function getHourOfTweet($tweetData, DateInterval $timeZoneOffset = null, $subTimeZoneOffset = false)
    {
        $dateTime = new DateTime($tweetData->created_at);

        if($timeZoneOffset) {
            $subTimeZoneOffset ? $dateTime->sub($timeZoneOffset) : $dateTime->add($timeZoneOffset);
        }

        return $dateTime->format('H');
    }
}

try {
    if(!empty($_POST['q'])){
        $tweets = new Tweets();
        $responseData = $tweets->getTweetsByHour($_POST['q']);
    }
    else{
        http_response_code(400);
        $responseData = 'Please type in a Twitter username';
    }

    echo $responseData;

}catch(Exception $e){
    http_response_code(500);
    echo $e->getMessage();
}
?>
