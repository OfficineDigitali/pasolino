<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Conf;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		Model::unguard();

		Conf::create([
			'name' => 'melt_path',
			'value' => '/usr/bin/melt'
		]);

		Conf::create([
			'name' => 'melt_profiles_path',
			'value' => '/usr/share/mlt/profiles/'
		]);

		if (file_exists('/usr/share/kdenlive/export/profiles.xml'))
			$kdenlive_profiles_file = '/usr/share/kdenlive/export/profiles.xml';
		else if (file_exists('/usr/share/kde4/apps/kdenlive/export/profiles.xml'))
			$kdenlive_profiles_file = '/usr/share/kde4/apps/kdenlive/export/profiles.xml';
		else
			$kdenlive_profiles_file = '/unable/to/automatically/find/profiles.xml';

		Conf::create([
			'name' => 'kdenlive_profiles_file',
			'value' => $kdenlive_profiles_file;
		]);

		/*
			This is to generate the SSH key to be able to fetch contents from the
			operator's computer
		*/
		$rsa = new \Crypt_RSA();
		$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_OPENSSH);
		extract($rsa->createKey());

		$pub_path = storage_path() . '/app/key.pub';
		$priv_path = storage_path() . '/app/key';
		file_put_contents($pub_path, $publickey);
		file_put_contents($priv_path, $privatekey);

		Model::reguard();
	}
}
