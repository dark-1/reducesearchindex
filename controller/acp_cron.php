<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\controller;

/**
 * @ignore
 */
use dark1\reducesearchindex\core\consts;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\config\config;
use phpbb\cron\manager as cron_manager;

/**
 * Reduce Search Index [RSI] ACP controller Cron.
 */
class acp_cron extends acp_base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\cron\manager					$cron_manager	Cron manager
	 */
	public function __construct(language $language, log $log, request $request, template $template, user $user, config $config, cron_manager $cron_manager)
	{
		parent::__construct($language, $log, $request, $template, $user);

		$this->config		= $config;
		$this->cron_manager	= $cron_manager;
	}

	/**
	 * Display the options a user can configure for Cron Mode.
	 *
	 * @return void
	 * @access public
	 */
	public function handle()
	{
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_on_submit();

			// Set the options the user configured
			$this->config->set('dark1_rsi_auto_reduce_sync_enable', $this->request->variable('dark1_rsi_auto_reduce_sync_enable', 0));
			$this->config->set('dark1_rsi_auto_reduce_sync_gc', ($this->request->variable('dark1_rsi_auto_reduce_sync_gc', 0)) * 86400);

			$this->success_form_on_submit();
		}

		// Run Cron Task
		if ($this->request->is_set_post('runcrontask'))
		{
			$this->check_form_on_submit();

			$cron_task = $this->cron_manager->find_task('dark1.reducesearchindex.cron.auto_reduce_sync');
			$cron_task->run();

			$this->success_form_on_submit();
		}

		// Set output variables for display in the template
		$this->template->assign_vars([
			'RSI_ENABLE_CRON'		=> $this->config['dark1_rsi_auto_reduce_sync_enable'],
			'RSI_CRON_INTERVAL'		=> ($this->config['dark1_rsi_auto_reduce_sync_gc'] / 86400),
			'RSI_CRON_LAST_RUN'		=> $this->user->format_date($this->config['dark1_rsi_auto_reduce_sync_last_gc'], consts::TIME_FORMAT, true),
			'RSI_CRON_NEXT_RUN'		=> $this->user->format_date($this->config['dark1_rsi_auto_reduce_sync_last_gc'] + $this->config['dark1_rsi_auto_reduce_sync_gc'], consts::TIME_FORMAT, true),
		]);
	}
}
