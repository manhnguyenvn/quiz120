<?php

class QuizController extends BaseController {
	const DEFAULT_QUIZZES_LIMIT = 50;
	const PER_PAGE = 10;
	const DEFAULT_ORDER_BY = 'created_at';
	const DEFAULT_ORDER_BY_TYPE = 'desc';
	public static function incrementQuizStats($quiz, $type) {
		switch($type) {
			case 'attempt':
				$statColumnName = 'attempts';
				$methodName = 'attemptedUsers';
				break;
			case 'completion':
				$statColumnName = 'completions';
				$methodName = 'completedUsers';
				break;
			case 'like':
				$statColumnName = 'likes';
				$methodName = 'likedUsers';
				break;
			case 'share':
				$statColumnName = 'shares';
				$methodName = 'sharedUsers';
				break;
			case 'comment':
				$statColumnName = 'comments';
				$methodName = 'commentedUsers';
				break;
			default:
				$statColumnName = '';
				$methodName = '';
		}
		
		if(empty($statColumnName) || empty($methodName)) {
			throw new Exception('Invalid activity');
		}
		$activityCount = $quiz->$methodName()->count();
		
		$quizStat = $quiz->stats ? $quiz->stats : new QuizStats();
		$quizStat->$statColumnName = $activityCount;
		$quiz->stats()->save($quizStat);
		return true;
	}
	public function index($options = array()) {

		$loadQuizOptions = ['limit' => self::PER_PAGE];
		$stream = 'latest';
		if(isset($options['stream'])) {
			if($options['stream'] == "popular"){
				$loadQuizOptions['order_by'] = 'attempts';
				$loadQuizOptions['order_by_type'] = 'desc';
			}
			$stream = $options['stream'];
		}

        if(isset($options['category'])) {
            $category = $options['category'];
            $loadQuizOptions['categoryId'] = $category->id;
            View::share('categoryName', $category->name);
        }

		self::_loadQuizes($loadQuizOptions);
		$titleLangKey = ($stream == "latest") ? 'latestQuizzes' : (($stream == "popular") ? 'popularQuizzes' : 'quizzes');
        if(!empty($category)) {
            $titleLangKey = $category->name;
        }
		$pageTitle = __($titleLangKey) . ' | ' . Config::get('siteConfig')['main']['siteName'];
		$pageDescription = __('hereAreSomeQuizzes');
		return View::make('quizes/index')->with(array(
			'currentPage' => 'quizesIndex',
			'title' => $pageTitle,
			'ogTitle' => $pageTitle,
			'description' => $pageDescription,
			'ogDescription' => $pageDescription,
			'isStream' . ucfirst($stream) => true,
			'mainHeading' => __($titleLangKey)
		));
	}

    public function category($categorySlug){
        $category = Category::findBySlug($categorySlug);
        if(!$category)
            return Response::notFound();
        return $this->index(['category' => $category]);
    }

	public function popular(){
		return $this->index(['stream' => 'popular']);
	}

	public function iframeList(){
		$loadQuizesOptions = array();
		$loadQuizesOptions['limit'] = Input::get('limit');
		self::_loadQuizes($loadQuizesOptions);

		$pageTitle = __('quizzes') . ' | ' . Config::get('siteConfig')['main']['siteName'];
		$pageDescription = __('hereAreSomeQuizzes');
		return View::make('quizes/iframeList')->with(array(
			'currentPage' => 'quizesIndex',
			'title' => $pageTitle,
			'ogTitle' => $pageTitle,
			'description' => $pageDescription,
			'ogDescription' => $pageDescription
		));
	}
	
	public static function _getQuizes($options = array()) {
		$orderBy = !empty($options['order_by']) ? $options['order_by'] : self::DEFAULT_ORDER_BY;
		$orderByType = !empty($options['order_by_type']) ? $options['order_by_type'] : self::DEFAULT_ORDER_BY_TYPE;

		$quizesQuery = Quiz::where('active', '=', true)->orderBy($orderBy, $orderByType);
		$limit = isset($options['limit']) ? $options['limit'] : self::DEFAULT_QUIZZES_LIMIT;
		if(!empty($options['exclude'])) {
			$quizesQuery->whereNotIn('id', array($options['exclude']));
		}
        if(!empty($options['categoryId'])) {
            $quizesQuery->where('category', $options['categoryId']);
        }
		$quizes = $quizesQuery->simplePaginate($limit);
		foreach($quizes as $key => $quiz) {
			$quizes[$key] = $quiz;
		}
        self::touchUpQuizes($quizes);
		return $quizes;
	}
	
	public static function _loadQuizes($options = array()) {
		$getQuizzesOptions = $options;
        if(!empty($options['related_to'])) {
            $getQuizzesOptions['exclude'] = $options['related_to'];
        }

		$quizes = self::_getQuizes($getQuizzesOptions);
		View::share('quizes', $quizes);
	}
		
	public function getRouteParams($quiz) {
		return QuizHelpers::viewQuizUrlParams($quiz);
	}
	
	public static function getViewQuizUrl($quiz, $result = null) {
		return QuizHelpers::viewQuizUrl($quiz, $result);
	}
	
	/*
	Save referrer - Increment the referrals of the referring user identified by query param 'ref-by'
	*/
	public static function saveReferrer() {
		try{
			//Saving user referral against the user who referred me
			if($referrerId = Input::get('ref-by')) {
				$userReferral = UserReferrals::firstOrNew(array('user_id' => $referrerId));
				$userReferral->referrals = (empty($userReferral->referrals) ? 0 : $userReferral->referrals) + 1;
				$userReferral->save();
			}
		}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			//return Response::notFound('Quiz not found');
		} catch(Illuminate\Database\QueryException $e) {
		}
	}
	
	public function viewQuiz($nameString, $quizId = null, $resultId = null) {
		self::saveReferrer();
		try {
			$sharedUserId = Input::get('user-fb-id');
			$quiz = Quiz::findOrFail($quizId);
            self::_loadQuizes(array('related_to' => $quizId));
			//$quiz = Quiz::decodeQuizJson($quiz);
			$quiz->viewQuizUrl = self::getViewQuizUrl($quiz);
            if(!$quiz->active) {
                View::share('quizInactive', true);
                if(!Session::get('admin')) {
                    App::abort(404);
                }
            }
			$ogTitle = $quiz->topic;
			if($resultId) {
				foreach($quiz->results as $res) {
					if($res->id == $resultId) {
						$result = $res;
					}
				}
				if(!empty($result)) {
					$ogImage = @$quiz->ogImages->$resultId;
					if(!empty($result->title)) {
						$ogTitle = __('iGot') . ' "' . $result->title . '" | ' . $quiz->topic;;
					}
				}
				View::share('quizResultId', $resultId);
			}
			$ogUrl = $canonicalUrl = !isset($result) ? $quiz->viewQuizUrl : self::getViewQuizUrl($quiz, $result);
			$ogImage = URL::asset(!empty($ogImage) ? $ogImage : @$quiz->ogImages->main);
			return View::make('quizes/viewQuiz')->with(array(
				'quiz' => $quiz,
				'viewQuizUrl' => self::getViewQuizUrl($quiz),
				'currentPage' => 'viewQuiz',
				'sharedUserId' => $sharedUserId,
				'ogImage' => $ogImage,
				'ogTitle' => $ogTitle,
				'ogUrl' => $ogUrl,
				'title' => $quiz->topic,
				'ogDescription' => $quiz->description,
				'description' => $quiz->description,
                'canonicalUrl' => $canonicalUrl
			));
		}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return Response::notFound('Quiz not found');
		}
	}
	
	public function activity($nameString, $quizId = null, $activityType){
		try {
			$quiz = Quiz::findOrFail($quizId);
			$user = Auth::user();
			if(!$user) {
				return Response::json(array('error' => 'Not logged in') , 400);
			}
			$quizUserActivity = QuizUserActivity::firstOrNew(array('user_id' => $user->id, 'quiz_id' => $quiz->id, 'type' => $activityType));
			if($quizUserActivity->created_at) {
				$quizUserActivity->touch();
			} else {
				$quizUserActivity->save();
			}
			self::incrementQuizStats($quiz, $activityType);
			return Response::json(array('message' => 'Activity recorded'));
		}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return Response::notFound('Quiz not found');
		}
	}
	
	public function saveUserResult($nameString, $quizId = null){
		try {
			$quiz = Quiz::findOrFail($quizId);
			$user = Auth::user();
			$resultId = Input::get('resultId');
			if(!$user) {
				return Response::json(array('error' => 'Not logged in') , 400);
			}
			$quizUserResult = QuizUserResults::firstOrNew(array('user_id' => $user->id, 'quiz_id' => $quiz->id));
			$quizUserResult->result_id = $resultId;
			$quizUserResult->save();
			return Response::json(array('message' => 'Activity recorded'));
		}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return Response::notFound('Quiz not found');
		}
	}
	
	public function saveUserAnswer($nameString, $quizId = null){
		try {
			$quiz = Quiz::findOrFail($quizId);
			$user = Auth::user();
			$questionId = Input::get('questionId');
			$choiceId = Input::get('choiceId');
			if(!$user) {
				return Response::json(array('error' => 'Not logged in') , 400);
			}
			$quizUserAnswer = QuizUserAnswers::firstOrNew(array('user_id' => $user->id, 'quiz_id' => $quiz->id, 'question_id' => $questionId, 'answer_id' => $choiceId));
			$quizUserAnswer->save();
			return Response::json(array('message' => 'Activity recorded'));
		}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return Response::notFound('Quiz not found');
		} catch(Exception $e) {
			return Response::notFound($e->getMessage());
		}
	}

    public static function touchUpQuizes(&$quizes) {
        /*foreach ($quizes as $key => $quiz) {
            self::touchUpQuiz($quizes[$key]);
        }*/
    }

    public static function touchUpQuiz(&$quiz) {
        /*$quiz->image = !$quiz->image ? : asset($quiz->image);
        $ogImages = new stdClass();
        foreach ($quiz->ogImages as $key => $image) {
            $ogImages->$key = !$quiz->ogImages->$key ? : asset($quiz->ogImages->$key);
        }
        $quiz->ogImages = json_encode($ogImages);
        return $quiz;*/
    }
}