<?php

namespace App\Http\Controllers;

use App\Jobs\ProccessCSV;
use App\Listeners\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CSVImportController extends BaseController
{
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return $this->sendError('CSV file is required.', $validator->errors());
        }

        Cache::forget('users');
        $file_path = $request->file('file')?->store('csv_files');

        $data = array_map('str_getcsv', file(Storage::path($file_path)));
        $header = array_shift($data);

        // Generate chunks
        foreach (array_chunk($data, 5) as $chunks) {
            ProccessCSV::dispatch($chunks);
        }
        event(new SendMail());
        return $this->sendResponse('CSV file is being processed.', null);
    }


    public function getUsers()
    {
        $users = Cache::get('users');
        return $this->sendResponse($users, 'Users retrieved successfully.');
    }
}
