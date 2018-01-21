<?php
class QuizUserResults extends Eloquent {
	protected $table = "quiz_user_results";
	protected $fillable = array('quiz_id', 'user_id', 'result_id');
}