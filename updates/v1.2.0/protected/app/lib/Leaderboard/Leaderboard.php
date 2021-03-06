<?php
/**
 * Created by PhpStorm.
 * User: jitheshgopan
 * Date: 04/04/15
 * Time: 4:43 PM
 */

namespace App\Leaderboard;
class Leaderboard {

    public $initialized = false;
    public function initialize() {
        if($this->initialized) {
            return;
        }
        $leaderboardEvents = \Config::get('leaderboard.events');
        $this->initialized = true;
        $this->setupEvents($leaderboardEvents);
    }

    public function setupEvents($leaderboardEvents) {
        $config = \Config::get('siteConfig');
        if(empty($config['leaderboard'])) {
           return;
        }
        $leaderboardConfig = $config['leaderboard'];
        $scoresForEvents = $leaderboardConfig['scores'];
        foreach ($leaderboardEvents as $event) {
            call_user_func($event['setHandler'], function($user = null) use($scoresForEvents, $event){
                if(!$user) {
                    $user = \Auth::user();
                }
                $user->reward($scoresForEvents[$event['id']]);
            });
        }

    }
}