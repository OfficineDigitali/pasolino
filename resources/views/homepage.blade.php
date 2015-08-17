<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Pasolino</title>
		<link rel="stylesheet" href="{{ url('css/bootstrap.min.css') }}" media="screen">
		<link rel="stylesheet" href="{{ url('css/bootstrap-theme.min.css') }}" media="screen">
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta name="csrf-token" content="{{ csrf_token() }}">
	</head>

	<body>
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<ul class="nav navbar-nav">
					<li><a href="{{ url('/') }}">Pasolino</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right hidden-xs">
					<li><a href="#" data-toggle="modal" data-target="#confModal">Configurations</a></li>
					<li><a href="#" data-toggle="modal" data-target="#newModal">New Project</a></li>
				</ul>
			</div>
		</nav>

		<!-- list of existing projects -->

		<div class="container">
			@if(count($projects) == 0)
			<div class="jumbotron">
				<h1>Doing Nothing!</h1>
				<p>There are no projects currently in the queue. Load a new one by clicking "New Project" on the top right corner, or the button below.</p>
				<p><a class="btn btn-primary btn-lg" href="#" data-toggle="modal" data-target="#newModal">New Project</a></p>
			</div>
			@else
				@foreach($projects as $project)
				<div class="row">
					<input type="hidden" name="project_id" value="{{ $project->id }}">

					<div class="page-header">
						<h1><span class="glyphicon glyphicon-<?php if($project->status == 'ready') echo 'ok'; else if($project->status == 'failed') echo 'remove'; else echo 'refresh' ?>" aria-hidden="true"></span> {{ $project->title }}</h1>
					</div>

					@foreach($project->jobs as $job)
					<div class="col-md-2">
						<p>{{ $job->type }}</p>
					</div>
					<div class="col-md-10">
						@if($job->error != '')
						<div class="alert alert-danger">{{ $job->error }}</div>
						@else
						<div class="progress">
							<div class="progress-bar <?php if($job->percentage == 100) echo 'progress-bar-success'; else echo 'progress-bar-info' ?>" role="progressbar" aria-valuenow="{{ $job->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $job->percentage }}%;">
								<span class="sr-only">{{ $job->percentage }}% Complete</span>
							</div>
						</div>
						@endif
					</div>
					@endforeach

					<div class="col-md-12 text-right">
						@if($project->status == 'ready')
						<a href="{{ url('projects/' . $project->id) }}" class="btn btn-info">Download</a>
						@endif
						<button type="button" class="btn btn-danger remove-project">Remove</button>
					</div>
				</div>
				@endforeach
			@endif
		</div>

		<!-- creation of a new project -->

		<div class="modal fade" id="newModal" tabindex="-1" role="dialog" aria-labelledby="newModal" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="sellsModalLabel">New Project</h4>
					</div>

					<form class="form-horizontal" id="newProject" method="POST" action="{{ url('projects') }}" enctype="multipart/form-data">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="modal-body">
							<div class="form-group">
								<label class="control-label col-md-2">Title</label>
								<div class="col-md-10">
									<input class="form-control" type="text" name="title">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-2">MLT File</label>
								<div class="col-md-10">
									<input type="file" name="mlt">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-2">Profile</label>
								<div class="col-md-10">
									<div class="row">
										<div class="col-md-4">
											<select multiple class="form-control" id="group-select" size="10" autocomplete="off">
												@foreach($kdenlive_profiles as $group)
												<option value="{{ $group->slug }}">{{ $group->name }}</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-4">
											@foreach($kdenlive_profiles as $group)
											<select multiple class="form-control profile-select" id="{{ $group->slug }}" size="10" autocomplete="off">
												@foreach($group->profiles as $profile)
												<option value="{{ $profile->slug }}">{{ $profile->name }}</option>
												@endforeach
											</select>
											@endforeach
										</div>

										<div class="col-md-4">
											@foreach($kdenlive_profiles as $group)
												@foreach($group->profiles as $profile)
												<div id="{{ $profile->slug }}" class="profile-opts">
													<input type="hidden" name="extension" value="{{ $profile->extension }}">
													<input type="hidden" name="args" value="{{ $profile->args }}">

													@if(count($profile->bitrates) != 0)
													<div class="form-group">
														<label class="control-label col-md-4">Video</label>
														<div class="col-md-8">
															<select class="form-control bitrates" name="bitrate" autocomplete="off">
																@foreach($profile->bitrates as $bt)
																<option value="{{ $bt }}">{{ $bt }}</option>
																@endforeach
															</select>
														</div>
													</div>
													@endif

													@if(count($profile->audiobitrates) != 0)
													<div class="form-group">
														<label class="control-label col-md-4">Audio</label>
														<div class="col-md-8">
															<select class="form-control audiobitrates" name="audiobitrate" autocomplete="off">
																@foreach($profile->audiobitrates as $at)
																<option value="{{ $at }}">{{ $at }}</option>
																@endforeach
															</select>
														</div>
													</div>
													@endif

													@if($profile->haspass == true)
													<div class="form-group">
														<label class="control-label col-md-4">Passes</label>
														<div class="col-md-8">
															<select class="form-control passes" name="passes" autocomplete="off">
																<option value="1">1 pass</option>
																<option value="2">2 passes</option>
																<option value="3">3 passes</option>
															</select>
														</div>
													</div>
													@endif
												</div>
												@endforeach
											@endforeach
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Start</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- configuration panel -->

		<div class="modal fade" id="confModal" tabindex="-1" role="dialog" aria-labelledby="confModal" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="sellsModalLabel">Configurations</h4>
					</div>

					<form class="form-horizontal" id="configurations" method="POST" action="{{ url('configurations') }}" enctype="multipart/form-data">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="modal-body">
							<div class="form-group">
								<label class="control-label col-md-4">melt program path</label>
								<div class="col-md-8">
									<input class="form-control" type="text" name="melt_path" value="{{ $confs['melt_path'] }}">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-4">Kdenlive profiles.xml path</label>
								<div class="col-md-8">
									<input class="form-control" type="text" name="kdenlive_profiles_file" value="{{ $confs['kdenlive_profiles_file'] }}">
								</div>
							</div>

							<hr/>

							<div class="form-group">
								<div class="col-md-12">
									<p>To enable SFTP on your computer and permit automatic files fetching, you need to create a new user `pasolino` with proper permissions and authorize the following key to the remote access (e.g. add the following line to the /home/pasolino/.ssh/authorized_keys file)</p>
									<textarea readonly class="form-control">{{ file_get_contents(storage_path() . '/app/key.pub') }}</textarea>
								</div>
							</div>
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<br />

		<script type="text/javascript" src="{{ url('js/jquery-2.1.1.min.js') }}"></script>
		<script type="text/javascript" src="{{ url('js/bootstrap.min.js') }}"></script>
		<script type="text/javascript" src="{{ url('js/pasolino.js') }}"></script>
	</body>
</html>
