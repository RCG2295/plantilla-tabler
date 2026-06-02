<?php
session_destroy();
header('Location: ' . TemplateController::getUrlController() . '/login');
exit;
