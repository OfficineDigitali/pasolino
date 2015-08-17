<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\PasoJob;
use App\Conf;

class RenderJob extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels;

	private $project;

	public function __construct($project)
	{
		$this->project = $project;
	}

	public function handle()
	{
		$job = PasoJob::commonInit($this->project, 'rendering');

		/*
			melt writes progresses on stderr, so I have to open a pipe on that channel.
			A simple popen() opens only stdout
		*/

		$command = sprintf('%s %s/config.mlt -consumer avformat:%s/output.%s %s -progress',
				   Conf::read('melt_path'), $this->project->folder(), $this->project->folder(), $this->project->extension, $this->project->render_string);

		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);

		$process = proc_open($command, $descriptorspec , $pipes);
		if (is_resource($process)) {
			while(!feof($pipes[2])) {
				$buffer = fread($pipes[2], 500);
				preg_match('/percentage: *([0-9]{1,2})/', $buffer, $matches);
				if (count($matches) == 2) {
					$job->percentage = $matches[1];
					$job->save();
				}

				/*
					TODO: intercept errors from melt
				*/
			}
		}

		$job->percentage = 100;
		$job->save();

		$this->project = $this->project->fresh();
		if ($this->project->status != 'failed') {
			$this->project->status = 'ready';
			$this->project->save();
		}
	}
}
