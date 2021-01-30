<?php

return [
	app\middleware\Auth::class,
	//'logedcheck' => \app\middleware\logedCheck::class,
	app\middleware\AdminLoginCookie::class,
];