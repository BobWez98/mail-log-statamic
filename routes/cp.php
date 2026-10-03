<?php

declare(strict_types=1);

use BobWez98\MailLogStatamic\Http\Controllers\MailLogController;
use BobWez98\MailLogStatamic\Http\Middleware\AuthorizeMailLog;
use Illuminate\Support\Facades\Route;

Route::prefix('mail-log')
    ->name('mail-log-statamic.')
    ->middleware(AuthorizeMailLog::class)
    ->controller(MailLogController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/{mailLog}', 'show')->whereNumber('mailLog')->name('show');
        Route::get('/{mailLog}/preview', 'preview')->whereNumber('mailLog')->name('preview');
    });
