<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Ip;

class ImportIpRanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $fileName, private string $title)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = file_get_contents(public_path('uploads/ip/'.$this->fileName));
        $ips = preg_split('/[\n-]+/', $file);
        $l = count($ips);

        for ($i = 0; $i < $l; $i+=2) {
            $ip = new Ip();
            $ip->title = $this->title;
            $ip->type = 'range';
            $ip->from = trim($ips[$i]);
            $ip->to = trim($ips[$i+1]);
            $ip->save();
        }
    }
}
