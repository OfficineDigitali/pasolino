<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Jobs\TestJob;
use App\Conf;
use App\Project;

class MainController extends Controller
{
	public function index()
	{
		$data['projects'] = Project::orderBy('created_at', 'desc')->get();
		$data['kdenlive_profiles'] = Conf::getKdenliveProfiles();

		$confs = array();
		$dbconfs = Conf::get();
		foreach($dbconfs as $c)
			$confs[$c->name] = $c->value;
		$data['confs'] = $confs;

		return view('homepage', $data);
	}

	public function show($id)
	{
		$project = Project::findOrFail($id);
		return response()->download($project->folder() . '/output.' . $project->extension);
	}

	public function store(Request $request)
	{
		$project = new Project();
		$project->title = $request->input('title');
		$project->sender_host = $_SERVER['REMOTE_ADDR'];
		$project->status = 'undefined';
		$project->extension = $request->input('extension');

		$render_string = $request->input('args');

		if ($request->has('bitrate')) {
			$bt = $request->input('bitrate');
			if (strpos($render_string, '%bitrate') != false)
				$render_string = str_replace('%bitrate', $bt, $render_string);
			else if (strpos($render_string, '%quality') != false)
				$render_string = str_replace('%quality', $bt, $render_string);
		}
		if ($request->has('audiobitrate')) {
			$at = $request->input('audiobitrate');
			if (strpos($render_string, '%audiobitrate') != false)
				$render_string = str_replace('%audiobitrate', $at, $render_string);
			else if (strpos($render_string, '%audioquality') != false)
				$render_string = str_replace('%audioquality', $at, $render_string);
		}
		if ($request->has('passes')) {
			$p = $request->input('passes');
			if (strpos($render_string, '%passes') != false)
				$render_string = str_replace('%passes', $p, $render_string);
		}

		$project->render_string = $render_string;

		$project->save();

		$test = false;

		if ($request->hasFile('mlt') && $request->file('mlt')->isValid()) {
			/*
				For the sake of prudency, after mkdir(0777) I enforce another
				chmod(0777) on the folder (required for the correct execution of melt)
			*/
			mkdir($project->folder(), 0777);
			chmod($project->folder(), 0777);

			if ($request->file('mlt')->move($project->folder(), 'config.mlt')) {
				$this->dispatch(new TestJob($project));
				$test = true;
			}
		}

		if ($test == false) {
			$project->status = 'failed';
			$project->save();
		}

		return redirect(url('/'));
	}

	private function deleteFolder($dir)
	{
		$files = array_diff(scandir($dir), array('.','..'));

		foreach ($files as $file)
			(is_dir("$dir/$file")) ? $this->deleteFolder("$dir/$file") : unlink("$dir/$file");

		return rmdir($dir);
	}

	public function destroy($id)
	{
		$project = Project::findOrFail($id);

		foreach($project->files as $file)
			$file->delete();

		foreach($project->jobs as $jobs)
			$jobs->delete();

		$this->deleteFolder($project->folder());
		$project->delete();
	}
}
