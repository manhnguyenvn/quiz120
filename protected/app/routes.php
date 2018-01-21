<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'home', 'uses' => 'HomeController@index'));

Route::get('quizzes', array('as' => 'quizes', 'uses' => 'QuizController@index'));
Route::get('quizzes/popular', array('as' => 'popularQuizes', 'uses' => 'QuizController@popular'));
Route::get('quizzes-iframe', array('as' => 'quizesIframe', 'uses' => 'QuizController@iframeList'));
Route::get('quizzes/{nameString}/{quizId}', array('as' => 'viewQuiz', 'uses' => 'QuizController@viewQuiz'));
Route::get('quizzes/{nameString}/{quizId}/r/{resultId}', array('as' => 'viewQuizResultLandingPage', 'uses' => 'QuizController@viewQuiz'));
Route::post('quizzes/{nameString}/{quizId}/activity/{activityType}', array('as' => 'viewQuizRecordActivity', 'uses' => 'QuizController@activity'));
Route::post('quizzes/{nameString}/{quizId}/user-results/', array('as' => 'viewQuizSaveUserResult', 'uses' => 'QuizController@saveUserResult'));
Route::post('quizzes/{nameString}/{quizId}/user-answers/', array('as' => 'viewQuizSaveUserAnswer', 'uses' => 'QuizController@saveUserAnswer'));
Route::get('pages/{nameString}.html', array('as' => 'viewPage', 'uses' => 'PageController@viewPage'));
Route::get('category/{slug}', array('as' => 'category', 'uses' => 'QuizController@category'));

Route::filter('adminAuth', function()
{
	$admin = Session::get('admin');
    /*if (!$admin && !Input::get('logmein'))
    {
        return Response::notFound();
    } else */
	
	if(!$admin) {
		if(Request::ajax()) {
			return Response::make('You have been logged out or your session has expired. Please login on another tab and try again.<br><br><a target="_blank" href="'. route('adminLogin') .'" class="btn btn-success">Login again</a></a>', 400);
		} else{
			return Redirect::route('adminLogin', ['redirect' => urlencode(Request::path())]);
		}
	}
});

Route::match(array('get', 'post'), 'admin/login', array('as' => 'adminLogin', 'uses' => 'AdminController@login'));
Route::get('admin/logout', array('as' => 'adminLogout', 'uses' => 'AdminController@logout'));
Route::group(array('before' => 'adminAuth'), function() {
	Route::get('admin', array('as' => 'admin', 'uses' => 'AdminController@index'));
	Route::get('admin/quizes/view', array('as' => 'adminViewQuizes', 'uses' => 'AdminQuizesController@listQuizes'));
	Route::match(array('GET', 'POST'), 'admin/quizes/create', array('as' => 'adminCreateQuiz', 'uses' => 'AdminQuizesController@createEdit'));
	Route::match(array('POST'), 'admin/quizes/delete', array('as' => 'adminDeleteQuiz', 'uses' => 'AdminQuizesController@delete'));
	Route::get('admin/pages/view', array('as' => 'adminViewPages', 'uses' => 'AdminPagesController@listPages'));
	Route::match(array('GET', 'POST'), 'admin/pages/create', array('as' => 'adminCreatePage', 'uses' => 'AdminPagesController@createEdit'));
	Route::match(array('GET', 'POST'), 'admin/pages/delete', array('as' => 'adminDeletePage', 'uses' => 'AdminPagesController@delete'));
	Route::match(array('GET', 'POST'), 'admin/config', array('as' => 'adminConfig', 'uses' => 'AdminConfigController@index'));
	Route::match(array('GET', 'POST'), 'admin/config/widgets', array('as' => 'adminConfigWidgets', 'uses' => 'AdminConfigController@widgets'));
	Route::match(array('GET', 'POST'), 'admin/config/languages', array('as' => 'adminConfigLanuages', 'uses' => 'AdminConfigController@languages'));
	Route::match(array('GET', 'POST'), 'admin/config/quiz', array('as' => 'adminConfigQuiz', 'uses' => 'AdminConfigController@quizConfig'));
    Route::match(array('GET', 'POST'), 'admin/change-password', array('as' => 'adminChangePassword', 'uses' => 'AdminController@changePassword'));
	Route::match(array('GET', 'POST'), 'admin/users/', array('as' => 'adminUsersHome', 'uses' => 'AdminUsersController@index'));
	Route::match(array('GET', 'POST'), 'admin/users/quiz-users', array('as' => 'adminQuizUsers', 'uses' => 'AdminUsersController@quizUsers'));
	
	Route::match(array('GET', 'POST'), 'admin/quizes/embed-codes', array('as' => 'adminQuizesEmbedCodes', 'uses' => 'AdminQuizesController@embedCodes'));
    Route::match(array('GET', 'POST'), 'admin/categories', array('as' => 'adminCategories', 'uses' => 'AdminCategoriesController@view'));
    Route::match(array('GET', 'POST', 'PATCH', 'DELETE'), 'admin/categories/addEdit', array('as' => 'adminCategoriesAddEdit', 'uses' => 'AdminCategoriesController@addEdit'));
    Route::get('admin/update', array('as' => 'update', 'uses' => 'UpdateController@index'));
});



//Media manager
Route::group(array(), function()
{
	\Route::get('media', 'W3G\MediaManager\MediaManagerController@showStandalone');
	\Route::any('media/connector', array('as' => 'mediaConnector', 'uses' => 'W3G\MediaManager\MediaManagerController@connector'));
});

Route::get('/login', array('as' => 'login', 'uses' => 'UserController@login'));
Route::get('login/fb', array('as' => 'loginWithFb', 'uses' => 'UserController@loginWithFb'));

Route::get('logout', array('as' => 'logout', 'uses' => 'UserController@logout'));