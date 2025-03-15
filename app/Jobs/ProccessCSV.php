<?php

namespace App\Jobs;

use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProccessCSV implements ShouldQueue
{
    use Queueable;

    protected $chunkRecords;
    /**
     * Create a new job instance.
     */
    public function __construct($chunkRecords)
    {
        $this->chunkRecords = $chunkRecords;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        DB::beginTransaction();
        try {

            foreach ($this->chunkRecords as $record) {
                if(empty($record) || count($record) < 4) {
                    Log::info("Invalid records");
                    return;
                }

                [$name, $email, $dob, $countryName] = $record;

                $checkDuplicate = User::where('email', $email)->exists();
                if ($checkDuplicate) {
                    Log::info("User with email: $email already exists");
                    continue;
                }

                // Create country if not exists
                $country = Country::firstOrCreate([
                    'name' => $countryName
                ]);

                // Create user
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'dob' => Carbon::parse($dob)->format('Y-m-d'),
                    'country_id' => $country->id
                ]);
            }
            DB::commit();
            $users = User::with('country')->get();
            Cache::Put('users', $users,3600);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error("Error while processing record: " . json_encode($record));
            Log::error($th->getMessage());
            Log::error($th->getTraceAsString());
        }

    }
}
