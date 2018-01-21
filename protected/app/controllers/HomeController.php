<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function index()
	{
		QuizController::_loadQuizes(['limit' => QuizController::PER_PAGE]);

		$pageTitle = Config::get('siteConfig')['main']['siteTitle'];
		$pageDescription = Config::get('siteConfig')['main']['siteDescription'];
		return View::make('home')->with(array(
			'title' => $pageTitle,
			'ogTitle' => $pageTitle,
			'description' => $pageDescription,
			'ogDescription' => $pageDescription
		));
	}

}
