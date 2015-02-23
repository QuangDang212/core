<?php
/**
 * ownCloud
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC;


use OCP\IConfig;
use OCP\IDateTimeZone;
use OCP\ISession;

class DateTimeZone implements IDateTimeZone {
	/** @var IConfig */
	protected $config;

	/** @var ISession */
	protected $session;

	/**
	 * Constructor
	 *
	 * @param IConfig $config
	 * @param ISession $session
	 */
	public function __construct(IConfig $config, ISession $session) {
		$this->config = $config;
		$this->session = $session;
	}

	/**
	 * Get the timezone of the current user, based on his session information and config data
	 *
	 * @return \DateTimeZone
	 */
	public function getTimeZone() {
		$timeZone = $this->config->getUserValue($this->session->get('user_id'), 'core', 'timezone', null);
		if ($timeZone === null) {
			$timeZone = 'UTC';
			if ($this->session->exists('timezone')) {
				$offsetHours = $this->session->get('timezone');
				$timeZone = $this->getTimeZoneFromOffset($offsetHours);
			}
		}

		try {
			return new \DateTimeZone($timeZone);
		} catch (\Exception $e) {
			\OCP\Util::writeLog('datetimezone', 'Failed to created DateTimeZone "' . $timeZone . "'", \OCP\Util::DEBUG);
			return new \DateTimeZone('UTC');
		}
	}

	protected function getTimeZoneFromOffset($offset) {
		if (is_int($offset)) {
			// Note: the timeZone name is the inverse to the offset,
			// so a positive offset means negative timeZone
			// and the other way around.
			if ($offset == 0 || $offset > 14 || $offset < -12) {
				return 'UTC';
			} else if ($offset > 0) {
				return 'Etc/GMT-' . $offset;
			} else  {
				return 'Etc/GMT+' . abs($offset);
			}
		}

		switch ($offset) {
			case -4.5:
				return 'America/Caracas';
			case -3.5:
				return 'Canada/Newfoundland';
			case 3.5:
				return 'Asia/Tehran';
			case 4.5:
				return 'Asia/Kabul';
			case 5.5:
				return 'Asia/Kolkata';
			case 5.75:
				return 'Asia/Kathmandu';
			case 9.5:
				return 'Australia/Darwin';
			default:
				return 'UTC';
		}
	}
}
