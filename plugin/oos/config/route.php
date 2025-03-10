<?php

use Webman\Route;
use plugin\oos\app\middleware\AdminAuth;

// 文件上传和管理接口
Route::post('/oos/upload', [plugin\oos\app\controller\FileController::class, 'upload']);
Route::post('/oos/upload/chunk', [plugin\oos\app\controller\FileController::class, 'uploadChunk']);
Route::post('/oos/upload/merge', [plugin\oos\app\controller\FileController::class, 'mergeChunks']);
Route::get('/oos/download/{id:\d+}', [plugin\oos\app\controller\FileController::class, 'download']);
Route::get('/oos/preview/{id:\d+}', [plugin\oos\app\controller\FileController::class, 'preview']);
Route::get('/oos/file/list', [plugin\oos\app\controller\FileController::class, 'list']);
Route::get('/oos/file/info/{id:\d+}', [plugin\oos\app\controller\FileController::class, 'info']);
Route::post('/oos/file/delete', [plugin\oos\app\controller\FileController::class, 'delete']);

// 图片处理接口
Route::get('/oos/image/{id:\d+}/resize/{width:\d+}/{height:\d+}', [plugin\oos\app\controller\ImageController::class, 'resize']);
Route::get('/oos/image/{id:\d+}/watermark', [plugin\oos\app\controller\ImageController::class, 'watermark']);
Route::get('/oos/image/{id:\d+}/thumbnail/{size}', [plugin\oos\app\controller\ImageController::class, 'thumbnail']);

// 管理员接口
Route::group('/oos/admin', function () {
    Route::get('/stats', [plugin\oos\app\controller\AdminController::class, 'stats']);
    Route::get('/files/orphan', [plugin\oos\app\controller\AdminController::class, 'orphanFiles']);
    Route::post('/files/clean', [plugin\oos\app\controller\AdminController::class, 'cleanFiles']);
})->middleware(AdminAuth::class);