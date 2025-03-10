<?php
use Webman\Route;

// OOS 路由配置
Route::group('/oos', function () {
    // 上传相关路由
    Route::post('/upload', [plugin\oos\app\controller\FileController::class, 'upload']);
    Route::post('/upload/chunk', [plugin\oos\app\controller\FileController::class, 'uploadChunk']);
    Route::post('/upload/merge', [plugin\oos\app\controller\FileController::class, 'mergeChunks']);
    
    // 下载相关路由
    Route::get('/download/{id}', [plugin\oos\app\controller\FileController::class, 'download']);
    Route::get('/preview/{id}', [plugin\oos\app\controller\FileController::class, 'preview']);
    
    // 文件管理路由
    Route::get('/file/list', [plugin\oos\app\controller\FileController::class, 'list']);
    Route::get('/file/info/{id}', [plugin\oos\app\controller\FileController::class, 'info']);
    Route::post('/file/delete', [plugin\oos\app\controller\FileController::class, 'delete']);
    
    // 图片处理路由
    Route::get('/image/{id}/resize/{width}/{height}', [plugin\oos\app\controller\ImageController::class, 'resize']);
    Route::get('/image/{id}/watermark', [plugin\oos\app\controller\ImageController::class, 'watermark']);
    Route::get('/image/{id}/thumbnail/{size}', [plugin\oos\app\controller\ImageController::class, 'thumbnail']);
    
    // 管理路由
    Route::group('/admin', function () {
        Route::get('/stats', [plugin\oos\app\controller\AdminController::class, 'stats']);
        Route::get('/files/orphan', [plugin\oos\app\controller\AdminController::class, 'orphanFiles']);
        Route::post('/files/clean', [plugin\oos\app\controller\AdminController::class, 'cleanFiles']);
    })->middleware([plugin\oos\app\middleware\AdminAuth::class]);
});