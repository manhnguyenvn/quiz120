@extends('layout')

@section('foot')
@parent
<script src="{{ asset('bower_components/masonry/dist/masonry.pkgd.min.js') }}"></script>
<script src="{{ asset('bower_components/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
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

@section('content')

	@if(!empty($widgets['aboveQuizzesList']))
	<div class="quizzes-page-head-widget-section">
		@foreach($widgets['aboveQuizzesList'] as $widget)
			{{$widget['content']}}
		@endforeach
	</div>
	@endif

	<h1 class="page-header text-center">
        @if(!empty($categoryName))
            {{$categoryName}}
        @else
        {{$mainHeading}}
        @endif
    </h1>
	
	
	@include('quizes/quizesList')
	{{ $quizes->links('pagination::simple') }}
	
	@if(!empty($widgets['belowQuizzesList']))
	<div class="quizzes-page-foot-widget-section">
		@foreach($widgets['belowQuizzesList'] as $widget)
			{{$widget['content']}}
		@endforeach
	</div>
	@endif

@stop