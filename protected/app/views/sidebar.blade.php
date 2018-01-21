@if(!empty($widgets['sideBar']))
	@foreach($widgets['sideBar'] as $widget)
		<div class="sidebar-item">
		{{$widget['content']}}
		</div>
	@endforeach
@endif

{{--
@if(@$currentPage == 'viewQuiz')
@include('quizes/quizesList')
@endif
--}}