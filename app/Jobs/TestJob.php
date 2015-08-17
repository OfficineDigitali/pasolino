<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

use App\Jobs\FetchJob;
use App\PasoJob;
use App\File;

class TestJob extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels, DispatchesJobs;

	private $project;

	public function __construct($project)
	{
		$this->project = $project;
	}

	public function handle()
	{
		$job = PasoJob::commonInit($this->project, 'testing');

		$f = $this->project->folder() . '/config.mlt';
		$c = file_get_contents($f);
		$xml = new \SimpleXMLElement($c);

		if ($xml == null) {
			$job->setError('Unable to read the XML file');
			return;
		}
		else {
			$root_node = $xml->xpath('//mlt[@root]');
			$root_folder = $root_node[0]->attributes()['root'];

			$files1 = $xml->xpath('//property[@name="resource"]');
			$files2 = $xml->xpath('//kdenlive_producer[@resource]');
			$files3 = $xml->xpath('//metaproperty[@name="meta.attr.url"]');
			$step = ceil(100 / (count($files1) + count($files2) + count($files3)));

			foreach($files1 as $f) {
				if ($this->handleFile($root_folder, $f) == false)
					$job->setError('Unable to retrieve file ' . $f);

				$job->percentage += $step;
				$job->save();
			}

			foreach($files2 as $f) {
				$resource = $f->attributes()['resource'];
				if ($this->handleFile($root_folder, $resource) == false)
					$job->setError('Unable to retrieve file ' . $resource);

				$job->percentage += $step;
				$job->save();
			}

			foreach($files3 as $f) {
				if ($this->handleFile($root_folder, $f) == false)
					$job->setError('Unable to retrieve file ' . $f);

				$job->percentage += $step;
				$job->save();
			}
		}

		$job->percentage = 100;
		$job->save();

		$this->project = $this->project->fresh();
		if ($this->project->status != 'failed')
			$this->dispatch(new FetchJob($this->project));
	}

	private function handleFile($root_folder, $f)
	{
		/*
			The "black" identifier is just an abstract reference, not a real file:
			we skip analysis for it
		*/
		if ($f == 'black')
			return true;

		/*
			This is to manage eventual files embedded multiple times: we track only
			one, and eventually will substitute once all occurrences in the mlt file
			when required
		*/
		$path = $this->guessPath($root_folder, $f);
		$test_count = File::where('project_id', '=', $this->project->id)->where('original_path', '=', $path)->first();
		if ($test_count != null) {
			$test_count->references = $test_count->references + 1;
			$test_count->save();
			return true;
		}

		$file = new File();
		$file->project_id = $this->project->id;
		$file->filename = basename($f);
		$file->original_path = $path;
		$file->references = 1;

		$ret = $file->testReach();
		$file->save();
		return $ret;
	}

	private function guessPath($folder, $file)
	{
		if (substr($file, 0, 1) != '/')
			return $folder . '/' . $file;
		else
			return $file;
	}
}
