<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

use App\Jobs\RenderJob;
use App\PasoJob;

class FetchJob extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels, DispatchesJobs;

	private $project;

	public function __construct($project)
	{
		$this->project = $project;
	}

	public function handle()
	{
		$job = PasoJob::commonInit($this->project, 'fetching');

		$todo = 0;

		foreach($this->project->files as $f) {
			if ($f->status == 'fetching')
				$todo++;
		}

		if ($todo != 0) {
			$step = ceil(100 / $todo);
			foreach($this->project->files as $f) {
				if ($f->fetchToLocal() == false) {
					$job->setError('Unable to fetch file ' . $f->original_path);
					break;
				}

				$job->percentage += $step;
				$job->save();
			}
		}

		$job->percentage = 100;
		$job->save();

		$this->project = $this->project->fresh();
		if ($this->project->status != 'failed')
			$this->dispatch(new RenderJob($this->project));
	}
}
