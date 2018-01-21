define([
	'backbone',
	'underscore',
	'appMan',
	'quizMaster',
	'models/quizProgress',
	'models/question',
	'views/quizProgress',
	'views/question',
	'models/quizResult',
	'views/quizFinish'
],
function( Backbone, _, AppMan, QuizMaster, QuizProgress, QuestionModel, QuizProgressView, QuestionView, QuizResultModel, QuizFinishView) {
    'use strict';

    var canvasContainer = $('#quizCanvasContainer');
    var topmenu = $('#topmenu');
    function scrollToTop() {
        $(window).scrollTop(canvasContainer.offset().top - (topmenu.outerHeight() + 10));
    }
	
	function stripSlashes(url) {
		return url.replace(/\/+$/, "");
	}

	function isScoreBased(){
		if(AppMan.reqres.request('quiz').get('type') == 'scoreBased') {
			return true;
		}else {
			return false;
		}
	}
	function ifLoggedIn(callback) {
		if(User.isLoggedIn()) {
			callback();
		} else {
			$('body').on('loggedIn', function(){
				callback();
			});
		}
	}
	
	function prepareLoginPromptMessage() {
		var siteQuizConfig = AppMan.reqres.request('config:quiz');
		var quizSettings = AppMan.reqres.request('quiz').get('settings') || {};
		var loginPromptMessage;
		var globalLoginPromptMessage = (quizSettings.forceLogin === 'on-quiz-start') ? siteQuizConfig.loginPromptMessageOnQuizStart : ((quizSettings.forceLogin === 'before-result') ? siteQuizConfig.loginPromptMessageBeforeResult : '');
		//If login prompt message is specified 
		if(quizSettings.loginPromptMessage && quizSettings.loginPromptMessage.length) {
			loginPromptMessage = quizSettings.loginPromptMessage;
		} else if(globalLoginPromptMessage){
			loginPromptMessage = globalLoginPromptMessage;
		} else {
			if(quizSettings.forceLogin === 'on-quiz-start') {
				loginPromptMessage = 'Please login to start the quiz!';
			} else if(quizSettings.forceLogin === 'before-result'){
				loginPromptMessage = 'Please login to view your result!';
			} else {
				loginPromptMessage = 'Please login to proceed!';
			}
		}
		AppMan.reqres.setHandler('quiz:loginPromptMessage', function(){
			return loginPromptMessage;
		});
	}
	var QuizController = Backbone.Marionette.Controller.extend({

		initialize: function( options ) {
			console.log('initialize a Quiz Controller');
			var self = this;
			App.quizController = this;
			this.quiz = options.quiz;
			this.quizMaster = new QuizMaster({
				quiz: this.quiz
			});
			this.finishedQuestions = {};
			self.currentQuestion = -1;
			AppMan.reqres.setHandler('user', function(){
				return User;
			});
			AppMan.reqres.setHandler('config:socialMedia', function(){
				if(!window.SiteMainConfig) {
					return {};
				}
				return window.SiteMainConfig.social || {};
			});
			AppMan.reqres.setHandler('config:quiz', function(){
				if(!window.SiteQuizConfig) {
					return {};
				}
				return window.SiteQuizConfig || {};
			});
			AppMan.reqres.setHandler('quiz', function(){
				return App.quizController.quiz;
			});
			AppMan.reqres.setHandler('quiz:meta', function(property){
				if(property) {
					return App.quizMeta[property];
				} else {
					return App.quizMeta;
				}
			});
			AppMan.reqres.setHandler('quiz:viewQuizUrl', function(){
				var url, 
					user = AppMan.reqres.request('user');
				url = AppMan.reqres.request('quiz:meta', 'viewQuizUrl');
				return url;
			});
			AppMan.reqres.setHandler('quiz:quizShareUrl', function(options){
				options = options || {};
				var url = AppMan.reqres.request('quiz:viewQuizUrl'),
					user = AppMan.reqres.request('user');
				if(options.resultId) {
					url = url.replace(/\/+$/,'') + '/r/' + options.resultId;
				}
				var params = [];
				if(options.isRef && user.isLoggedIn()) {
					params.push('ref-by=' + user.data.id);
				}
				if(user.isLoggedIn() && typeof FB != "undefined") {
					params.push('user-fb-id=' + FB.getUserID());
				}
				url += '?' + params.join('&');
				return url;
			});
			AppMan.reqres.setHandler('quiz:resultData', function(resultId){
				if(!resultId) {
					return false;
				}
				return _.findWhere(self.quiz.get('results'), {id: resultId});
			});
			AppMan.reqres.setHandler('quiz:questions', function(){
				return self.quiz.get('questions');
			});
			AppMan.reqres.setHandler('quiz:totalQuestions', function(){
				return self.quiz.get('questions').length;
			});
			AppMan.reqres.setHandler('quiz:get-question', function(index){
				var questions = AppMan.reqres.request('quiz:questions');
				if(questions[index]) {
					return questions[index];
				}else {
					return false;
				}
				/*var question = _.findWhere(questions, {id: index});
				if(question) {
					return question;
				}else {
					return false;
				}*/
			});
			AppMan.reqres.setHandler('quiz:next-question', function(){
				var questions = AppMan.reqres.request('quiz:questions');
				var nextQuesion = AppMan.reqres.request('quiz:get-question', self.currentQuestion + 1);
				return (nextQuesion || false);
			});
			AppMan.on('quiz:skipped-question', function(question){
				AppMan.trigger('quiz:finished-question', question);
				AppMan.command.execute('quiz:next-question');
			});
			AppMan.on('quiz:answered-question', function(question, choiceId){
				AppMan.trigger('quiz:finished-question', question);
				AppMan.command.execute('quiz:next-question');
			});
			AppMan.on('quiz:finished-question', function(question){
				self.finishedQuestions[question.index] = true;
			});
			
			prepareLoginPromptMessage();
		},
		viewQuiz: function(){
			var self = this;
			function populateProgressStages(){
				var questions = AppMan.reqres.request('quiz:questions');
				var stages = [{stage: __('start'), completed: true, questionId: -1, index: -1}];
				if(self.currentQuestion == -1) {
					stages[0].active = 1;
				}
				
				//Marking start and finial states
				stages[0].isStart = true;
				stages[stages.length - 1].isFinish = true;
				var stagePos = 1,
					stage = {};
				_.each(questions, function(question) {
					var index = stagePos-1;
					stage = {stage: stagePos, questionId: question.id, index: index};
					if(self.finishedQuestions[question.index]) {
						stage.completed = true;
					} else {
						stage.completed = false;
					}
					if(self.currentQuestion == question.index) {
						stage.active = true;
					} else {
						stage.active = false;
					}
					stages.push(stage);
					stagePos++;
				});
				var onFinishedStage = (self.currentQuestion >= questions.length) ? true : false;
				stages.push({stage: __('finish'), completed: AppMan.reqres.request('quiz:is-finished') ? true : false, active: onFinishedStage});
				return stages;
			}
			this.quiz.settings = this.quiz.settings || {};
			
			AppMan.on('quiz:start', function(){
				var user = AppMan.reqres.request('user');
				var quizSettings = self.quiz.get('settings') || {};
				
				if(quizSettings.forceLogin === 'on-quiz-start' && !User.isLoggedIn()) {
					AppMan.trigger('login-required', AppMan.reqres.request('quiz:loginPromptMessage'));
					$('body').on('loggedIn', function(){
						AppMan.trigger('quiz:start');
					});
					return;
				}
				
				var stages = populateProgressStages();
				var quizProgress = new QuizProgress({
					stages: stages
				});
				App.quizProgressView = new QuizProgressView({
					model: quizProgress
				});
				App.quizProgress.show(App.quizProgressView).$el.parents('.quiz-progress-row').show();
				AppMan.on('quiz:question:change', function(){
					quizProgress.set({
						stages: populateProgressStages()
					});
				});
				App.quizProgressView.on('quiz:show-question', function(questionIndex){
					questionIndex = (questionIndex === -1) ? 0 : questionIndex;
					//If question not yet finished, dont show - the user has to do all the previous questions to reach this one
					if(!self.finishedQuestions[questionIndex]) {
						return;
					}
					//If the quiz is score based, user should not attempt the question again as it will help them fix their scores.
					if(isScoreBased()) {
						return;
					}
					var question = AppMan.reqres.request('quiz:get-question', questionIndex);
					self.currentQuestion = questionIndex;
					AppMan.command.execute('quiz:show-question', question);
				});
				AppMan.command.execute('quiz:next-question');
				AppMan.trigger('quiz:started');
			});
			AppMan.on('quiz:finish', function(){
				var quizSettings = self.quiz.get('settings') || {};
				function onFinish(){
					App.quizProgressView.model.set({
						stages: populateProgressStages()
					});
					var result = new QuizResultModel(self.quizMaster.prepareResult());
					var quizFinishView = new QuizFinishView({
						model: result
					});
					quizFinishView.on('quiz:share', function(){
						AppMan.trigger('quiz:share');
					});
					var quizSettings = self.quiz.get('settings') || {};
					App.quizCanvas.show(quizFinishView);
					scrollToTop();
					AppMan.trigger('quiz:finished');
					AppMan.trigger('quiz:got-result', result);
				}
				if(quizSettings.forceLogin === 'before-result' && !User.isLoggedIn()) {
					AppMan.trigger('login-required', AppMan.reqres.request('quiz:loginPromptMessage'));
					$('body').on('loggedIn', function(){
						onFinish();
					});
					return;
				} else {
					onFinish();
				}
			});
			AppMan.command.setHandler('quiz:next-question', function(){
				self.currentQuestion++;
				var nextQuestion = AppMan.reqres.request('quiz:get-question', self.currentQuestion);
				if(nextQuestion) {
					AppMan.command.execute('quiz:show-question', nextQuestion);
				} else {
					AppMan.trigger('quiz:finish');
				}
			});
			AppMan.command.setHandler('quiz:show-question', function(question){
				var newQuestion = new QuestionModel(question);
				var newQuestionView = new QuestionView({
					model: newQuestion
				});
				newQuestionView.on('quiz:skipped-question', function(question){
					AppMan.trigger('quiz:skipped-question', question);
				});
				newQuestionView.on('quiz:answered-question', function(questionJSON, choiceId){
					function proceedAfterAnswering(){
						AppMan.trigger('quiz:answered-question', questionJSON, choiceId);
					}
					if(isScoreBased()){
						newQuestionView.showAnswerResponse(choiceId);
						newQuestionView.on('quiz:proceed-after-answer-response', function(question){
							proceedAfterAnswering();
						});
					} else{
						proceedAfterAnswering();
					}
				});
				AppMan.trigger('quiz:question:change');
				App.quizCanvas.show(newQuestionView);
				scrollToTop();
			});
			
			AppMan.on('login-required', function(message){
				AppMan.command.execute('promptLogin', message);
			});
			AppMan.command.setHandler('promptLogin', function(message){
				$('body').trigger('prompt-login', message);
			});
			
			AppMan.on('quiz:started', function(){
				setTimeout(function(){
                    scrollToTop();
                }, 200);
				ifLoggedIn(function(){
					self.quiz.recordActivity('attempt');
				});
			});
			
			AppMan.on('quiz:finished', function(){
				ifLoggedIn(function(){
					self.quiz.recordActivity('completion');
				});
			});
			
			AppMan.on('quiz:like', function(){
				ifLoggedIn(function(){
					self.quiz.recordActivity('like');
				});
			});
			
			AppMan.on('quiz:share', function(){
				ifLoggedIn(function(){
					self.quiz.recordActivity('share');
				});
			});
			
			AppMan.on('quiz:comment', function(){
				ifLoggedIn(function(){
					self.quiz.recordActivity('comment');
				});
			});
			
			AppMan.on('quiz:answered-question', function(question, choiceId){
				ifLoggedIn(function(){
					self.quiz.recordUserAnswer(question.id, choiceId);
				});
			});
			AppMan.on('quiz:got-result', function(result){
				var resultId = result.get('id');
				ifLoggedIn(function(){
					self.quiz.recordUserResult(resultId);
				});
			});
			
			AppMan.command.execute('fb', function(){
				FB.Event.subscribe('edge.create', function fbLikeCallback(url){
					url = stripSlashes(url);
					if(url === stripSlashes(AppMan.reqres.request('quiz:viewQuizUrl'))) {
						AppMan.trigger('quiz:like');
					}
				});
				
				FB.Event.subscribe('comment.create', function fbLikeCallback(response){
					url = stripSlashes(response.href);
					if(url === stripSlashes(AppMan.reqres.request('quiz:viewQuizUrl'))) {
						AppMan.trigger('quiz:comment');
					}
				});
			});
			
			//console.log(a,b);
		},
		all: function(){
			//alert('default route fired');
		}
	});

	return QuizController;
});
