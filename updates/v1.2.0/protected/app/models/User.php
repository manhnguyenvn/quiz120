<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Jitheshgopan\Leaderboard\Traits\Boardable;
use Traits\UserLeaderboardTrait;
class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, Boardable, UserLeaderboardTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token', 'email');


    public function profiles()
    {
        return $this->hasMany('Profile');
    }

	public function referrals()
    {
        return $this->hasOne('UserReferrals');
    }

    public function getPointsAttribute($value)
    {
        return $this->getPoints();
    }

    public function toArrayWithPoints() {
        $arr = $this->toArray();
        try {
            $arr['points'] = $this->getPoints();
        } catch(Exception $e){}
        return $arr;
    }
}
