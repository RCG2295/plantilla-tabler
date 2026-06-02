<?php

class TemplateController{

	static public function template(){

		include "views/template.php";

	}
	
	static public function getUrlController(){

		return $_ENV['APP_URL'];

	}
	
}