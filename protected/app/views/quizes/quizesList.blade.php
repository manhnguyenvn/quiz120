<div class="row quiz-items-row">
	@forelse ($quizes as $quiz)
		@include('quizes/quizItem')
	@empty
		<div class="col-md-12">
            <p class="text-center">{{__('noQuizzesYet')}}</p>
        </div>
	@endforelse
</div>
