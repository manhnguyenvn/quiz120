@extends('layout')
@section('content')
@if(!empty($quizInactive))
    <div class="alert alert-danger clearfix" style="margin-top: 30px;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4><strong>Hey Admin!</strong></h4>
        <div class="btn btn-red pull-right" data-dismiss="alert"><span>Okay</span></div>
        <strong>This Quiz is currently inactive.</strong>
        <p>Only you could view this.</p>
    </div>
@endif
<script>
    var QuizData = {{ json_encode($quiz) }};
    var QuizMeta = {
        viewQuizUrl: '{{QuizHelpers::viewQuizUrl($quiz)}}'
    };
    @if(!empty($quizResultId))
        var quizResultId = "{{$quizResultId}}";
    @endif
    @if(!empty($sharedUserId))
        var quizSharedUserId = '{{$sharedUserId}}';
    @endif
</script>
<div id="quizHeader"></div>
<div class="row quiz-progress-row">
    <div class="col-md-12">
        <div id="quizProgress"></div>
    </div>
</div>
<div id="quizCanvasContainer">
    <div class="text-center" style="margin-top: 20px;">
        @if(!empty($widgets['aboveQuizQuestion']))
            <div class="below-quiz-widget-section">
                @foreach($widgets['aboveQuizQuestion'] as $widget)
                    {{$widget['content']}}
                @endforeach
            </div>
        @endif

    </div>
    <div id="quizCanvas">
        <br><br>
        <h4 class="text-center">{{__('loadingQuiz')}}</h4>
        <div id="quizLoadingSpinner" style="position: relative; margin-top: 20px; height: 80px;"></div>
        <br>
    </div>
</div>

@if(!empty($widgets['belowQuiz']))
<div class="below-quiz-widget-section">
	@foreach($widgets['belowQuiz'] as $widget)
		{{$widget['content']}}
	@endforeach
</div>
@endif
@if(!empty($config['quiz']['showFacebookComments']) && $config['quiz']['showFacebookComments'] != "false")
<div style="margin-top: 40px;">
    <h3>{{__('comments')}}</h3>
    <div class="fb-comments" data-href="{{QuizHelpers::viewQuizUrl($quiz)}}" data-width="100%" data-numposts="10" data-colorscheme="light"></div>
</div>
@endif

<div id="belowQuizMoreQuizzesBlock">
	<h3 class="text-center"><strong>{{__('youMayAlsoLike')}}</strong></h3>
	@include('quizes/quizesList')
	@if(is_array($quizes) && count($quizes))
        <div class="text-center">
            <br/>
            <a href="{{route('quizes')}}" class="btn btn-primary"><span>{{__('viewMoreQuizzes')}}</span></a>
        </div>
    @endif
</div>

@stop

@section('foot')
@parent
<script src="{{ URL::asset('bower_components/spinjs/spin.js')}}"></script>
<script>
	(function(){
		var spinner = new Spinner({
			zIndex: 999,
			color: '#888'
		}).spin();
		$('#quizLoadingSpinner').append(spinner.el);
	})();
</script>

<script src="{{ asset('bower_components/masonry/dist/masonry.pkgd.min.js') }}"></script>
<script src="{{ asset('bower_components/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>

@if(App::isLocal())
<script data-main="{{ asset('js/quiz/init.js')}}" src="{{ asset('bower_components/requirejs/require.js')}}"></script>
@else
<script data-main="{{ asset('js/quiz/bundle.min.js')}}" src="{{ asset('bower_components/requirejs/require.js')}}"></script>
@endif

<script>
	$(function(){
		var $container = $('.quiz-items-row');
		imagesLoaded($container, function(){
			  var masonry = new Masonry( $container[0], {
				  itemSelector: '.quiz-item'
				});
		});
	});
</script>
@stop