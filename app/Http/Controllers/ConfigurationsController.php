<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Conf;

class ConfigurationsController extends Controller
{
	private function saveConf($request, $name)
	{
		$conf = Conf::where('name', '=', $name)->firstOrFail();
		$conf->value = $request->input($name);
		$conf->save();
	}

	public function store(Request $request)
	{
		$this->saveConf($request, 'melt_path');
		$this->saveConf($request, 'kdenlive_profiles_file');
		return redirect('/');
	}
}
