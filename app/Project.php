<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
	public function files()
	{
		return $this->hasMany('App\File');
	}

	public function jobs()
	{
		return $this->hasMany('App\PasoJob')->orderBy('created_at', 'asc');
	}

	public function folder()
	{
		return storage_path() . '/' . $this->id;
	}

	private function tokenizePath($path)
	{
		$ret = array();
		$dir = $path;

		$name = basename($path);
		$dir = dirname($path);

		do {
			array_unshift($ret, $name);
			$path = $dir;
			$name = basename($path);
			$dir = dirname($path);
		} while($path != $dir);

		return $ret;
	}

	public function replaceFile($file, $newpath)
	{
		$done = false;

		$conf_file = $this->folder() . '/config.mlt';
		$mlt = file_get_contents($conf_file);

		/*
			The "original_path" of files is always saved as absolute, but in the mlt
			file they may appear as relative to a given root. In this way we look for
			the effective path in the file. Note that all occurrences of the path are
			substituted: eventual duplicated references are already tracked in the
			parsing phase (cfr. TestJob)
		*/

		$path = $file->original_path;
		$path_arr = $this->tokenizePath($path);
		$references = 0;

		while(count($path_arr) != 0 && $references != $file->references) {
			if (strpos($mlt, $path) != false) {
				$references += substr_count($mlt, $path);
				$mlt = str_replace($path, $newpath, $mlt);
			}

			array_shift($path_arr);
			$path = join('/', $path_arr);
		}

		if ($references == $file->references)
			file_put_contents($conf_file, $mlt);
	}
}
