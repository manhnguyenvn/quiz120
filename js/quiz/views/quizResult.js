define([
        'backbone',
        'appMan',
        'hbs!templates/quizResult',
        'views/correctAnswers'
    ],
    function( Backbone, AppMan, ResultTmpl, CorrectAnswersView  ) {
        'use strict';

        /* Return a ItemView class definition */
        return Backbone.Marionette.LayoutView.extend({

            initialize: function() {
                var self = this;
                console.log("initialize a QuizResult Layout");
            },
            regions: {
                'correctAnswers' : '.correctAnswers'
            },
            template: ResultTmpl,
            className: 'quiz-result',

            /* ui selector cache */
            ui: {},

            /* Ui events hash */
            events: {
                "click .share-on-fb" : "shareOnFb",
                "click .share-on-twitter" : "shareOnTwitter",
                "click .share-on-pinterest" : "shareOnPinterest",
                "click .share-on-google-plus" : "shareOnGooglePlus",
                "click .view-correct-ans-btn" : "viewCorrectAnswers"
            },
            shareOnFb: function(e){
                var $this = $(e.currentTarget);
                (typeof ga == "function") && ga('send', 'event', 'share', 'facebook', $this.data('url'));
                var sharerUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + $this.data('url');
                window.open(sharerUrl, "popup", "width=600px,height=300px,left=50%,top=50%");
                this.trigger('quiz:share');
            },
            shareOnTwitter: function(e){
                var $this = $(e.currentTarget);
                (typeof ga == "function") && ga('send', 'event', 'share', 'twitter', $this.data('url'));
                var sharerUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent($this.data('text')) + ' ' + encodeURIComponent($this.data('url'));
                window.open(sharerUrl, "popup", "width=600px,height=300px,left=50%,top=50%");
                this.trigger('quiz:share');
            },
            shareOnPinterest: function(e){
                var $this = $(e.currentTarget);
                (typeof ga == "function") && ga('send', 'event', 'share', 'pinterest', $this.data('url'));
                var sharerUrl = 'https://www.pinterest.com/pin/create/button/?description=' + encodeURIComponent($this.data('text')) + '&url=' + encodeURIComponent($this.data('url')) + '&media=' + $this.data('image');
                window.open(sharerUrl, "popup", "width=600px,height=300px,left=50%,top=50%");
                this.trigger('quiz:share');
            },
            shareOnGooglePlus: function(e){
                var $this = $(e.currentTarget);
                (typeof ga == "function") && ga('send', 'event', 'share', 'google-plus', $this.data('url'));
                var sharerUrl = 'https://plus.google.com/share?url=' + encodeURIComponent($this.data('url'));
                window.open(sharerUrl, "popup", "width=600px,height=300px,left=50%,top=50%");
                this.trigger('quiz:share');
            },
            viewCorrectAnswers: function(e){
                var self = this;
                var questionsAnswers = {questions: []};
                var quiz = AppMan.reqres.request('quiz');
                var questions = quiz.get('questions');
                _.each(questions, function(question) {
                    var correctChoice = question.choices.findWhere({correct: true});
                    questionsAnswers.questions.push({
                        question: question.question,
                        answer: correctChoice ? correctChoice.get('title') : "",
                        image: correctChoice ? correctChoice.get('image') : ""
                    });
                });
                var correctAnsView = new CorrectAnswersView({
                    model : new Backbone.Model(questionsAnswers)
                });
                self.correctAnswers.show(correctAnsView);
                $(e.currentTarget).hide();
            },
            onBeforeRender: function(){
            },
            /* on render callback */
            onRender: function() {
                var self = this;
                var quizConfig = AppMan.reqres.request('config:quiz');
                if(quizConfig.showSharePromptModal) {
                    setTimeout(function(){
                        self.$('#resultSharePromptModal').modal();
                    }, 8000);
                }
                self.$('#resultSharePromptModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
                (typeof ga == "function") && ga('send', 'event', 'quiz', 'got-result', self.model.get('title'));
            },
            templateHelpers: function(){
                var self = this;
                var quizConfig = AppMan.reqres.request('config:quiz');
                return {
                    socialMedia: function(){
                        return AppMan.reqres.request('config:socialMedia');
                    },
                    shareQuizLink: function(){
                        return AppMan.reqres.request('quiz:quizShareUrl', {
                            isRef : true
                        });
                    },
                    shareResultLink: function(){
                        return AppMan.reqres.request('quiz:quizShareUrl', {
                            resultId : self.model.get('id'),
                            isRef : true
                        });
                    },
                    quizTopic: function() {
                        var quiz = AppMan.reqres.request('quiz');
                        return quiz.get('topic');
                    },
                    quizUrl: function() {
                        return AppMan.reqres.request('quiz:viewQuizUrl');
                    },
                    resultImage: function() {
                        var image = self.model.get('image');
                        return image;
                    },
                    quizImage: function() {
                        var quiz = AppMan.reqres.request('quiz');
                        return quiz.get('image');
                    },
                    isScoreBased: function(){
                        var quiz = AppMan.reqres.request('quiz');
                        return ((quiz.get('type') === 'scoreBased') ? true : false);
                    },
                    quizConfig: function(){
                        var quizConfig = AppMan.reqres.request('config:quiz');
                        return quizConfig;
                    },
                    debug: function(optionalValue) {
                        console.log(this.quizConfig());
                        console.log("Current Context");
                        console.log("====================");
                        console.log(this);

                        if (optionalValue) {
                            console.log("Value");
                            console.log("====================");
                            console.log(optionalValue);
                        }
                        return true;
                    },
                    showPageLikePrompt: function(){
                        return quizConfig.showPageLikePrompt;
                    },
                    showSharePromptModal: function(){
                        return quizConfig.showSharePromptModal;
                    },
                    showFacebookComments: function(){
                        return quizConfig.showFacebookComments;
                    }
                };
            }
        });

    });
