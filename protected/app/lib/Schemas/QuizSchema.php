<?php
	namespace Schemas;
	
	class QuizSchema extends BaseSchema {
		public function getSchema(){
			$schemaPath = $this->getSchemaPath('Quiz');
			if(\File::exists($schemaPath)) {
				return \File::get($schemaPath);
			} else{
				throw new \Exception('Schema file not found');
			}
		}
	}