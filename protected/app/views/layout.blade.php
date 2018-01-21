<!DOCTYPE html>
<html lang="{{App::getLocale()}}">

<head>

   @section('head')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		@if(!empty($config['main']['favicon']))
			<link rel="shortcut icon" href="{{ htmlspecialchars(asset($config['main']['favicon'])) }}">
		@endif

    @if(!empty($title))
        <title>{{ htmlspecialchars($title) }}</title>
    @endif

    <meta property="og:type" content="website">
    @if(!empty($ogTitle))
        <meta property="og:title" content="{{ htmlspecialchars($ogTitle) }}">
        <meta name="twitter:title" content="{{ htmlspecialchars($ogTitle) }}">
    @endif


    @if(!empty($ogImage))
        <meta property="og:image" content="{{ htmlspecialchars($ogImage) }}">
        <meta name="twitter:image" content="{{ htmlspecialchars($ogImage) }}" />
        <meta name="twitter:card" content="photo" />
    @endif

    @if(!empty($ogUrl))
        <meta property="og:url" content="{{ htmlspecialchars($ogUrl) }}">
        <meta name="twitter:url" content="{{ htmlspecialchars($ogUrl) }}" />
    @endif

    @if(!empty($description))
        <meta name="description" content="{{ htmlspecialchars($description) }}">
    @endif
    @if(!empty($ogDescription))
        <meta property="og:description" content="{{ $ogDescription }}" />
    @endif

    @if(!empty($config['main']['siteName']))
        <meta property="og:site_name" content="{{ htmlspecialchars($config['main']['siteName']) }}">
    @endif

    @if(!empty($canonicalUrl))
        <link rel="canonical" href="{{htmlspecialchars($canonicalUrl)}}" />
    @endif
    <!-- Custom CSS -->
	@if(App::isLocal())
		<link href="{{asset('/css/main.css')}}" rel="stylesheet">
	@else
		<link href="{{asset('/css/main.min.css')}}" rel="stylesheet">
	@endif

    @if(App::isLocal())
    <link href="{{asset('/themes/modern/style.css')}}" rel="stylesheet">
    @else
    <link href="{{asset('/themes/modern/style.min.css')}}" rel="stylesheet">
    @endif

	@if($languageDirection == 'rtl')
		@include('partials.rtlCss')
	@endif

	@if(!empty($navbarColor))
		<style>
			@include('partials.themeCss')
		</style>
	@endif

    <!-- Custom Fonts -->
    <link href="{{asset('/font-awesome-4.1.0/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">

   	<!-- jQuery Version 1.11.0 -->
   	@if(App::isLocal())
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js')}}"></script>
    @else
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    @endif
    
    <script>
        var BASE_PATH = '{{ url('') }}';
        var ASSET_BASE_PATH = '{{ asset('') }}';
        window.asset = function(path) {
            path = path || '';
            return path.match(/^http[s]?:\/\/.*$/) ? path : ASSET_BASE_PATH + path;
        }
		var SiteMainConfig = {{@$mainConfigJSON}};
		var SiteQuizConfig = {{@$quizConfigJSON}};
		SiteQuizConfig.showSharePromptModal = (SiteQuizConfig.showSharePromptModal === "true") ? true : false;
		SiteQuizConfig.showPageLikePrompt = (SiteQuizConfig.showPageLikePrompt === "true") ? true : false;
		SiteQuizConfig.showFacebookComments = (SiteQuizConfig.showFacebookComments === "true") ? true : false;
		var User = {
			isLoggedIn: function(){
				return (!$.isEmptyObject(this.data));
			},
			setData: function(data){
				this.data = data;
				if(this.isLoggedIn()){
					$('body').trigger('loggedIn');
				}
			}
		};
		User.data = {{$userData or '{}'}};
        @if(!empty($categories))
            window.Categories = {{json_encode($categories)}};
        @endif
	</script>

	<script>
		var languageStrings = {{json_encode($languageStrings)}};
		var defaultLanguageStrings = {{json_encode($defaultLanguageStrings)}};
		//Translation
		function __(key){
			if(languageStrings.hasOwnProperty(key)){
				return languageStrings[key];
			} else if (defaultLanguageStrings.hasOwnProperty(key)){
				return defaultLanguageStrings[key];
			} else {
				return key;
			}
		}
	</script>
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<script>
		
	  window.fbAsyncInit = function() {
		FB.init({
		  appId      : '<?php echo @$config['main']['social']['facebook']['appId'];?>',
		  xfbml      : true,
		  version    : 'v2.1',
			cookie : true
		});

		$('body').trigger('fb-api-loaded');
		FB.Event.subscribe('auth.statusChange', function(response) {
		  // do something with response
			if(response.status === "connected") {
				$('body').trigger('loggedIn:fb');
			}
		});
	  };

	  (function(d, s, id){
			 var js, fjs = d.getElementsByTagName(s)[0];
			 if (d.getElementById(id)) {return;}
			 js = d.createElement(s); js.id = id;
			 js.src = "//connect.facebook.net/{{$languageFbCode}}/sdk.js";
			 fjs.parentNode.insertBefore(js, fjs);
		})(document, 'script', 'facebook-jssdk');
	</script>

	@if(!empty($config['main']['customCode']['head']))
		{{$config['main']['customCode']['head']}}
	@endif

	@show
	
</head>

<body>
	<div id="fb-root"></div>

        <div class="body_wrap @if(!empty($currentPage))page-{{$currentPage}}@endif">
    	<div class="body-container container-fluid modern-touch">
           
            @include('header')
            <div class="row">
            	<div class="col-md-8 col-sm-7 col-xs-12 main-content-col @if($languageDirection == 'rtl') pull-right @endif">
				@yield('content')
				</div>

				<div class="col-md-4 col-sm-5 col-xs-12 sidebar-col @if($languageDirection == 'rtl') pull-left @endif">
					@include('sidebar')
			   </div>
            </div>
            @if(!empty($widgets['commonFooterSection']))
            <div class="row">
            	<div class="col-md-12">
            		<div class="common-foot-widget-section">
						@foreach($widgets['commonFooterSection'] as $widget)
							{{$widget['content']}}
						@endforeach
					</div>
            	</div>
            </div>
			@endif

            <!-- /.container-fluid -->

        </div>
            <div class="container-fluid">
                <div class="row footer-row">
                    <div class="col-md-12">
                        @include('footer')
                    </div>
                </div>
            </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

   <div id="loginModal" class="modal fade">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header text-center">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">{{__('closeBtn')}}</span></button>
			<h4 class="modal-title">{{__('loginBtn')}} / {{__('signUpBtn')}}</h4>
		  </div>
		  <div class="modal-body">
		  	<h4 class="login-prompt-message">{{__('loginBtn')}} / {{__('signUpBtn')}}</h4>
			<div class="login-panel">
				<ul>
				  <li>
					<div class="btn btn-fb" data-action="loginWithFB"><span>{{__('loginWithFB')}}</span></div>
				  </li>
				</ul>
			</div>
			<div class="logging-in-msg">
				<h4 class="text-center">{{__('loggingYouIn')}}</h4>
			</div>
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
   
   @section('foot')
   <!-- Logging in -->
	<script>
		(function(){
			window.loginWithFb = function(){
				FB.login(function(response) {
				   if (response.authResponse) {
					 FB.api('/me', function(response) {
						console.log('Logged in as ' + response.name + '.');
					 });
				   } else {
					 $('#loginError').removeClass('hide').html('<div class="alert alert-error">' + __('fbPermissionError') + '</div>');
				   }
				}, {scope: 'email'});
			}
			
			var body = $('body');
			body.on('click', '[data-action="loginWithFB"]', function(e){
				loginWithFb();
				e.preventDefault();
			});
			body.on('loggedIn', function(){
				var loginModal = $('#loginModal');
				loginModal.modal('hide');
			});
			body.on('loggedIn:fb', function(){
				if(!User.isLoggedIn()) {
					var loginModal = $('#loginModal');
					loginModal.addClass("logging-in");
					$.get(BASE_PATH + '/login/fb').success(function(response){
						User.setData(response.user);
					}).fail(function(response){
						$('body').trigger('login:error', response);
					}).always(function(){
						loginModal.removeClass("logging-in");
					});
				}
			});
			body.on('prompt-login', function(e, message){
				var loginModal = $('#loginModal');
				loginModal.find('.login-prompt-message').html(message);
				loginModal.modal('show');
			});
		})();
		
	</script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{{ asset('/themes/modern/js/libs/modernizr.min.js') }}"></script>
	<script src="{{ asset('/themes/modern/js/libs/bootstrap.min.js') }}"></script>

	   @if(!empty($config['main']['customCode']['foot']))
		   {{$config['main']['customCode']['foot']}}
	   @endif

	@show
</body>

</html>
