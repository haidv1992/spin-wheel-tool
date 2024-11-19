<?php

namespace Dbiz\SpinWheelTool\Console\Commands;

use Illuminate\Console\Command;

class PublishSpinWheelTool extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'spinwheeltool:publish';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish the SpinWheelTool configuration and assets';
	
	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		// Publish configuration
		$this->call('vendor:publish', [
			'--provider' => 'Dbiz\SpinWheelTool\ToolServiceProvider',
			'--tag' => 'config',
		]);
		
		// Publish migrations
		$this->call('vendor:publish', [
			'--provider' => 'Dbiz\SpinWheelTool\ToolServiceProvider',
			'--tag' => 'migrations',
		]);
		
		// Publish assets
		$this->call('vendor:publish', [
			'--provider' => 'Dbiz\SpinWheelTool\ToolServiceProvider',
			'--tag' => 'assets',
		]);
		
		$this->info('SpinWheelTool configuration and assets published successfully.');
		
		return 0;
	}
}
