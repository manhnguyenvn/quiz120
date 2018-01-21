<div class="footer">
	&copy; {{$config['main']['siteName']}}
	@if(!empty($widgets['footer']))
		@foreach($widgets['footer'] as $widget)
			{{$widget['content']}}
		@endforeach
	@endif

</div>