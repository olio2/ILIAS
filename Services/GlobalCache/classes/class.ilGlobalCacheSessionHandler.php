<?php

/**
 * Class ilGlobalCacheSessionHandler
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheSessionHandler extends SessionHandler {

	/**
	 * @param string $save_path
	 * @param string $session_id
	 *
	 * @return bool
	 */
	public function open($save_path, $session_id) {
		unset($save_path);
		$ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_SESSION_HANLDER);
		$ilGlobalCache->set($session_id, NULL);

		return true;
	}


	/**
	 * @return bool
	 */
	public function close() {
		return true;
	}


	/**
	 * @param int $session_id
	 *
	 * @return bool
	 */
	public function destroy($session_id) {
		$ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_SESSION_HANLDER);
		$ilGlobalCache->delete($session_id);

		return true;
	}


	/**
	 * @param int $maxlifetime
	 *
	 * @return bool
	 */
	public function gc($maxlifetime) {
		return true;
	}


	/**
	 * @param string $session_id
	 *
	 * @return bool
	 */
	public function read($session_id) {
		$ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_SESSION_HANLDER);

		return $ilGlobalCache->get($session_id);
	}


	/**
	 * @param string $session_id
	 * @param string $session_data
	 *
	 * @return bool
	 */
	public function write($session_id, $session_data) {
		$ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_SESSION_HANLDER);

		return $ilGlobalCache->set($session_id, $session_data);
	}
}

?>
