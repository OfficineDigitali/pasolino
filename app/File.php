<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

class File extends Model
{
	private function getSSHConnection()
	{
		$adapter = new SftpAdapter([
			'host' => $this->project->sender_host,
			'port' => 22,
			'username' => 'pasolino',
			'privateKey' => storage_path() . '/app/key',
			'root' => '/',
			'timeout' => 10,
			'directoryPerm' => 0755
		]);

		return new Filesystem($adapter);
	}

	private function getFinalDestination()
	{
		$name = $this->filename;
		$rand = '';
		$folder = $this->project->folder();

		while(true) {
			$path = sprintf('%s/%s%s', $folder, $rand, $name);
			if (file_exists($path) == false)
				return $path;
			$rand = str_random(4);
		}
	}

	public function project()
	{
		return $this->belongsTo('App\Project');
	}

	public function testReach()
	{
		try {
			$path = $this->original_path;

			if (file_exists($path) && is_readable($path)) {
				$this->current_path = $path;
				$this->status = 'ready';
				return true;
			}
			else {
				$filesystem = $this->getSSHConnection();
				if ($filesystem != null && $filesystem->has($path)) {
					$this->current_path = 'ssh';
					$this->status = 'fetching';
					return true;
				}
			}

			$this->status = 'failed';
			return false;
		}
		catch (Exception $e) {
			$this->status = 'failed';
			return false;
		}
	}

	public function fetch()
	{
		$contents = null;

		switch($this->current_path) {
			case 'ssh':
				$filesystem = $this->getSSHConnection();
				$contents = $filesystem->read($this->original_path);
				break;
		}

		if ($contents != null) {
			$new_path = $this->getFinalDestination();
			file_put_contents($new_path, $contents);
			$this->project->replaceFile($this, $new_path);
		}
	}
}
