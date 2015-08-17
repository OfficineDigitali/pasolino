<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conf extends Model
{
	static public function read($opt)
	{
		$c = Conf::where('name', '=', $opt)->first();
		return $c->value;
	}

	static public function getKdenliveProfiles()
	{
		$ret = [];

		$file = Conf::read('kdenlive_profiles_file');
		if (file_exists($file) == false)
			return $ret;

		$cfile = file_get_contents($file);
		$xml = new \SimpleXMLElement($cfile);

		$groups = $xml->xpath('//group');
		foreach($groups as $group) {
			$g = (object) array();
			$g->name = $group->attributes()['name'];
			$g->slug = str_slug($g->name);
			$parent_extension = $group->attributes()['extension'];
			$g->profiles = [];

			$profiles = $group->xpath('profile');
			foreach($profiles as $profile) {
				$attrs = $profile->attributes();
				$p = (object) array();
				$p->name = $attrs['name'];
				$p->slug = str_slug($p->name);
				$p->args = $attrs['args'];
				$p->haspass = (strpos($p->args, '%passes') != false);

				if ($attrs['bitrates'] != null)
					$p->bitrates = explode(',', $attrs['bitrates']);
				else if ($attrs['qualities'] != null)
					$p->bitrates = explode(',', $attrs['qualities']);
				else
					$p->bitrates = array();

				if ($attrs['audiobitrates'] != null)
					$p->audiobitrates = explode(',', $attrs['audiobitrates']);
				else if ($attrs['audioqualities'] != null)
					$p->audiobitrates = explode(',', $attrs['audioqualities']);
				else
					$p->audiobitrates = array();

				if ($attrs['extension'] != null)
					$p->extension = $attrs['extension'];
				else
					$p->extension = $parent_extension;

				$g->profiles[] = $p;
			}

			$ret[] = $g;
		}

		return $ret;
	}
}
