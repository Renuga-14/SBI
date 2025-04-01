<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PivcController;
use App\Http\Controllers\API\DataController;

Route::post('/createRinnRakshaLink', [PivcController::class, 'createRinnRakshaLink']);
Route::post('/getRinnRakshaLink', [PivcController::class, 'getRinnRakshaLink']);
Route::post('/validateRinnRikshaLink', [PivcController::class, 'validateRinnRikshaLink']);
Route::post('/getGeoLocationAddress', [PivcController::class, 'getGeoLocationAddress']);
Route::post('/deviceDetails', [PivcController::class, 'deviceDetails']);
Route::post('/updateLinkResponse', [PivcController::class, 'updateLinkResponse']);
Route::post('/updateEditLinkResponse', [PivcController::class, 'updateEditLinkResponse']);
Route::post('/rinnRikshaQuestions', [PivcController::class, 'rinnRikshaQuestions']);

Route::post('/data/addConsentImage', [DataController::class, 'addConsentImage']);
Route::post('/data/addCapturedScreenShot', [DataController::class, 'addCapturedScreenShot']);
Route::post('/data/addCapturedImage', [DataController::class, 'addCapturedImage']);

