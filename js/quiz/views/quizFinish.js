define([
	'backbone',
	'underscore',
	'views/choices',
	'hbs!templates/quizFinish',
	'views/quizResult'
],
function( Backbone, _, ChoicesView, FinishTmpl, QuizResultView) {
    'use strict';

	/* Return a ItemView class definition */
	return Backbone.Marionette.LayoutView.extend({

		initialize: function(options) {
			console.log('initialize a QuizFinish LayoutView');
			
		},
		   	
		template: FinishTmpl,
		regions: {
			result: '.quiz-result'
		},

    	/* ui selector cache */
    	ui: {},

		/* Ui events hash */
		events: {
			
		},
		/* on render callback */
		onRender: function() {
			var self = this;
			this.resultView = new QuizResultView({
				model: this.model
			});
			this.resultView.on('quiz:share', function(){
				self.trigger('quiz:share');
			});
			var self = this;
			this.$('.loading-anim').html('<div class="spinner-pulse"></div>');
			this.resultView.on('render', function(){
				setTimeout(function(){
					self.$('.quiz-preparing-result').hide();
					self.$('.quiz-result').removeClass('hide');
                    AppMan.command.execute('fb', function() {
                        setTimeout(function(){
                            FB.XFBML.parse(self.el);
                        }, 1000);
                    });
				}, 3000);
			});
			this.result.show(this.resultView);
			(typeof ga == "function") && ga('send', 'event', 'quiz', 'finish');
		}
	});

});
