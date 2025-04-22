<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PivcController;
use App\Http\Controllers\API\DataController;
use App\Http\Controllers\Cron\JobController;
use App\Http\Controllers\API\AudioController;

Route::post('/createRinnRakshaLink', [PivcController::class, 'createRinnRakshaLink']);
Route::post('/getRinnRakshaLink', [PivcController::class, 'getRinnRakshaLink']);
Route::post('/getProposalPIVCLink', [PivcController::class, 'getProposalPIVCLink']);
Route::post('/validatePIVCLink', [PivcController::class, 'validatePIVCLink']);
Route::post('/validateRinnRikshaLink', [PivcController::class, 'validateRinnRikshaLink']);
Route::post('/getGeoLocationAddress', [PivcController::class, 'getGeoLocationAddress']);
Route::post('/deviceDetails', [PivcController::class, 'deviceDetails']);
Route::post('/updateLinkResponse', [PivcController::class, 'updateLinkResponse']);
Route::post('/updateEditLinkResponse', [PivcController::class, 'updateEditLinkResponse']);
Route::post('/rinnRikshaQuestions', [PivcController::class, 'rinnRikshaQuestions']);
Route::post('/updateCompleteStatus', [PivcController::class, 'updateCompleteStatus']);

Route::post('/data/addConsentImage', [DataController::class, 'addConsentImage']);
Route::post('/data/addCapturedScreenShot', [DataController::class, 'addCapturedScreenShot']);
Route::post('/data/addCapturedImage', [DataController::class, 'addCapturedImage']);
Route::post('/data/getAllImages', [DataController::class, 'getAllImages']);
// Route::post('/data/playAudioFromPDF', [AudioController::class, 'playAudioFromPDF']);
Route::get('/data/playAudioFromPDF/{proposal_no}/{screen}', [AudioController::class, 'playAudioFromPDF']);


Route::get('/cron/generateTranscriptPDF/{case?}', [JobController::class, 'generateTranscriptPDF']);

// Route::get('/cron/generateTranscriptPDF', [JobController::class, 'generateTranscriptPDF']);
