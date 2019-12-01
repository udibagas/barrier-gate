<?php

namespace App\Console\Commands;

use App\AccessLog;
use Illuminate\Console\Command;

class DeleteSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshot:delete {age}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus snapshot';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = AccessLog::whereRaw('DATEDIFF(NOW(), created_at) >= :age AND (snapshot_in != "" OR snapshot_out != "")', [
            ':age' => $this->argument('age')
        ])->get();

        foreach ($data as $d)
        {
            if ($d->snapshot_in && file_exists('./public/'.$d->snapshot_in))
            {
                $this->info('Delete file '. $d->snapshot_in);

                try {
                    unlink('./public/'.$d->snapshot_in);
                    $this->info('File '. $d->snapshot_in. ' telah dihapus');
                    ParkingTransaction::where('snapshot_in', $d->snapshot_in)->update(['snapshot_in' => '']);
                } catch (\Exception $e) {
                    $this->error('Gagal menghapus file ' . $d->snapshot_in . '. '. $e->getMessage());
                }
            }

            if ($d->snapshot_out && file_exists('./public/'.$d->snapshot_out)) {
                $this->info('Delete file '. $d->snapshot_out);

                try {
                    unlink('./public/'.$d->snapshot_out);
                    $this->info('File '. $d->snapshot_out. ' telah dihapus');
                    ParkingTransaction::where('snapshot_out', $d->snapshot_out)->update(['snapshot_out' => '']);
                } catch (\Exception $e) {
                    $this->error('Gagal menghapus file ' . $d->snapshot_out . '. '. $e->getMessage());
                }
            }
        }

        $years = scandir('./public/snapshot');

        foreach ($years as $year)
        {
            if ($year == '.' || $year == '..' || !is_dir('./public/snapshot/'.$year)) {
                continue;
            }

            $months = scandir('./public/snapshot/'.$year);

            foreach($months as $month)
            {
                if ($month == '.' || $month == '..' || !is_dir('./public/snapshot/'.$year.'/'.$month)) {
                    continue;
                }

                $days = scandir('./public/snapshot/'.$year.'/'.$month);

                foreach ($days as $day)
                {
                    if ($day == '.' || $day == '..' || !is_dir('./public/snapshot/'.$year.'/'.$month.'/'.$day)) {
                        continue;
                    }

                    $hours = scandir('./public/snapshot/'.$year.'/'.$month.'/'.$day);

                    foreach ($hours as $hour)
                    {
                        if ($hour == '.' || $hour == '..' || !is_dir('./public/snapshot/'.$year.'/'.$month.'/'.$day.'/'.$hour)) {
                            continue;
                        }

                        try {
                            rmdir('./public/snapshot/'.$year.'/'.$month.'/'.$day.'/'.$hour);
                            $this->info('Delete directory public/snapshot/'.$year.'/'.$month.'/'.$day.'/'.$hour);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    try {
                        rmdir('./public/snapshot/'.$year.'/'.$month.'/'.$day);
                        $this->info('Delete directory public/snapshot/'.$year.'/'.$month.'/'.$day);
                        } catch (\Exception $e) {
                        continue;
                    }
                }

                try {
                    rmdir('./public/snapshot/'.$year.'/'.$month);
                    $this->info('Delete directory public/snapshot/'.$year.'/'.$month);
                } catch (\Exception $e) {
                    continue;
                }
            }

            try {
                rmdir('./public/snapshot/'.$year);
                $this->info('Delete directory public/snapshot/'.$year);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
