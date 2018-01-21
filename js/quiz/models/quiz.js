define([
	'backbone'
],
function( Backbone ) {
    'use strict';

	/* Return a model class definition */
	return Backbone.Model.extend({
		initialize: function() {
			console.log("initialize a Quiz model");
		},
		idAttribute: 'id',

		defaults: {},
		url: function(){
			return this.get('viewQuizUrl');
		},
		getActivityRootUrl: function(){
			return this.url() + '/activity/';
		},
		getActivityUrl: function(activity){
			return this.getActivityRootUrl() + activity;
		},
		getUserAnswerUrl: function(){
			return this.url() + '/user-answers';
		},
		getUserResultUrl: function(){
			return this.url() + '/user-results';
		},
		recordActivity: function(activityType){
			var activityUrl = this.getActivityUrl(activityType);
			$.post(activityUrl).success(function(response){
				
			}).fail(function(){
			});
		},
		recordUserAnswer: function(questionId, choiceId) {
			$.post(this.getUserAnswerUrl(), {questionId: questionId, choiceId: choiceId}).success(function(response){
				
			}).fail(function(){
			});
		},
		recordUserResult: function(resultId) {
			$.post(this.getUserResultUrl(), {resultId: resultId}).success(function(response){
				
			}).fail(function(){
			});
		}
    });
});
