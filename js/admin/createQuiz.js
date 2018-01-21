
	
	function getResultSchema() {
		if(QuizData.type == 'scoreBased') {
			Schemas.resultSchema.minScore = origResultSchema.minScore;
		} else {
			delete Schemas.resultSchema.minScore;
		}
		return Schemas.resultSchema;
	}

	var origQuestionSchema = $.extend(true, {}, Schemas.questionSchema);
	var origResultSchema = $.extend(true, {}, Schemas.resultSchema);
	
	function getQuestionSchema() {
		if(QuizData.type == 'scoreBased') {
			delete Schemas.questionSchema.choices.items.properties.favoursResult;
			//delete Schemas.questionSchema.choices.items.properties.favouredResultWeightage;
			Schemas.questionSchema.choices.items.properties.correct = origQuestionSchema.choices.items.properties.correct;
		} else {
			Schemas.questionSchema.choices.items.properties.favoursResult = origQuestionSchema.choices.items.properties.favoursResult;
			//Schemas.questionSchema.choices.items.properties.favouredResultWeightage = origQuestionSchema.choices.items.properties.favouredResultWeightage;
			delete Schemas.questionSchema.choices.items.properties.correct;
		}
		return Schemas.questionSchema;
	}

	
	function setQuizData(newQuizData, excludeProperties) {
		excludeProperties = excludeProperties || [];
		for(var i in newQuizData) {
			if(excludeProperties.indexOf(i) < 0) {
				QuizData[i] = newQuizData[i];
			}
		}
		//debugger;
		return QuizData;
	}

	function getResultTextFromId(id) {
		var resultItem = _.findWhere(QuizData.results, {id: id});
		if(resultItem) {
			return resultItem.title;
		} else {
			return '';
		}
	}

function fixOldVersionQuizData(){
	try{
		if(typeof QuizData.questions[0].choices[0].favoursResult == "string") {
			//Is old version - has only single favoured result per choice (specified with id)
			_.each(QuizData.questions, function(question){
				_.each(question.choices, function(choice){
					choice.favoursResult = [{result : choice.favoursResult, weightage : choice.favouredResultWeightage}];
					delete choice.favouredResultWeightage;
				});
			});
		}
	} catch(e){
	}
}

fixOldVersionQuizData();
