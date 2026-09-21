<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\ActionScheduler;

/**
 * GovPack action Scheduler extension that modifies it for our needs
 */
class ActionScheduler {

	/**
	 * Add Hooks for Messing with ActionScheduler Loader
	 */
	public static function hooks(): void {
		add_filter( 'action_scheduler_store_class', [ __CLASS__, 'replace_store' ] );
		//add_filter( 'action_scheduler_queue_runner_concurrent_batches', [ __CLASS__, 'as_concurrent_batches' ] );
		add_filter( 'action_scheduler_logger_class', [ __CLASS__, 'replace_logger' ] );
		add_filter( 'action_scheduler_failure_period', [ __CLASS__, 'set_timeout_seconds' ] );
		add_filter( 'action_scheduler_queue_runner_time_limit', [ __CLASS__, 'increase_time_limit' ] );
	}

	/**
	 * Force Action Scheduler to only use 1 Queue.
	 *
	 * @param int $concurrent_batches How many Batches/Queues.
	 *
	 * @psalm-return 1
	 */
	public static function as_concurrent_batches( $concurrent_batches = 1 ): int { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return 1;
	}

	/**
	 * Action added to replace the defaukt store used by Action Scheduler.
	 *
	 * @param string $current_store Current Store used By Action Scheduler.
	 *
	 * @psalm-return '\Govpack\ActionScheduler\Store'
	 */
	public static function replace_store( $current_store ): string { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		remove_filter( 'action_scheduler_store_class', [ 'ActionScheduler_DataController', 'set_store_class' ], 100 );
		return '\Govpack\ActionScheduler\Store';
	}

	/**
	 * Action added to replace the defaukt logger used by Action Scheduler.
	 *
	 * @param string $current_logging_class Current Store used By Action Scheduler.
	 *
	 * @psalm-return 'ActionScheduler_DBLogger'
	 */
	public static function replace_logger( $current_logging_class ): string { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return 'ActionScheduler_DBLogger';
	}
	
	public static function increase_time_limit( $time_limit ): int { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return 59;
	}
	

	/**
	 * Action added to set how long to wait for an action to timeout.
	 *
	 * @param integer $seconds Seconds to wait for the action.
	 *
	 * @psalm-return 10
	 */
	public static function set_timeout_seconds( $seconds ): int { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return 10;
	}
}
