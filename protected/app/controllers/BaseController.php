<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    public static function loadCategories() {
        $categories = new \Illuminate\Database\Eloquent\Collection();
        try {
            $categories = Category::all();
        } catch(Exception $e) {
            //Discard query exception.. categories table not found.. discard
        }

        View::share('categories', $categories);
    }

}
