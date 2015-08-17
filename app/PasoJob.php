<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasoJob extends Model
{
	public function project()
	{
		return $this->belongsTo('App\Project');
	}

	public function file()
	{
		return $this->belongsTo('App\File');
	}

	static public function commonInit($project, $type)
	{
		PasoJob::where('project_id', '=', $project->id)->where('type', '=', $type)->delete();

		$job = new PasoJob();
		$job->type = $type;
		$job->project_id = $project->id;
		$job->percentage = 0;
		$job->save();

		$project->status = $type;
		$project->save();

		return $job;
	}

	public function setError($message)
	{
		$this->error = $message;
		$this->save();

		$this->project->status = 'failed';
		$this->project->save();
	}
}
