<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Sebastian Meyer <sebastian.meyer@slub-dresden.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

/**
 * Hooks and helper for the 't3lib_TCEforms' library.
 *
 * @author	Sebastian Meyer <sebastian.meyer@slub-dresden.de>
 * @copyright	Copyright (c) 2011, Sebastian Meyer, SLUB Dresden
 * @package	TYPO3
 * @subpackage	tx_dlf
 * @access	public
 */
class tx_dlf_tceforms {

	/**
	 * Helper to get flexform's items array for plugin "tx_dlf_collection"
	 *
	 * @access	public
	 *
	 * @param	array		&$params: An array with parameters
	 * @param	t3lib_TCEforms		&$pObj: The parent object
	 *
	 * @return	void
	 */
	public function itemsProcFunc_collectionList(&$params, &$pObj) {

		if ($params['row']['pi_flexform']) {

			$pi_flexform = t3lib_div::xml2array($params['row']['pi_flexform']);

			$pages = $pi_flexform['data']['sDEF']['lDEF']['pages']['vDEF'];

			// There is a strange behavior where the uid from the flexform is prepended by the table's name and appended by its title.
			// i.e. instead of "18" it reads "pages_18|Title"
			if (!t3lib_div::testInt($pages)) {

				$_parts = explode('|', $pages);

				$pages = array_pop(explode('_', $_parts[0]));

			}

			if ($pages > 0) {

				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'label,uid',
					'tx_dlf_collections',
					'pid='.intval($pages).' AND (sys_language_uid IN (-1,0) OR l18n_parent=0)'.tx_dlf_helper::whereClause('tx_dlf_collections'),
					'',
					'label',
					''
				);

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($result) > 0) {

					while ($resArray = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {

						$params['items'][] = $resArray;

					}

				}

			}

		}

	}

	/**
	 * Helper to get flexform's items array for plugin "tx_dlf_oai"
	 *
	 * @access	public
	 *
	 * @param	array		&$params: An array with parameters
	 * @param	t3lib_TCEforms		&$pObj: The parent object
	 *
	 * @return	void
	 */
	public function itemsProcFunc_libraryList(&$params, &$pObj) {

		if ($params['row']['pi_flexform']) {

			$pi_flexform = t3lib_div::xml2array($params['row']['pi_flexform']);

			$pages = $pi_flexform['data']['sDEF']['lDEF']['pages']['vDEF'];

			// There is a strange behavior where the uid from the flexform is prepended by the table's name and appended by its title.
			// i.e. instead of "18" it reads "pages_18|Title"
			if (!t3lib_div::testInt($pages)) {

				$_parts = explode('|', $pages);

				$pages = array_pop(explode('_', $_parts[0]));

			}

			if ($pages > 0) {

				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'label,uid',
					'tx_dlf_libraries',
					'pid='.intval($pages).' AND (sys_language_uid IN (-1,0) OR l18n_parent=0)'.tx_dlf_helper::whereClause('tx_dlf_libraries'),
					'',
					'label',
					''
				);

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($result) > 0) {

					while ($resArray = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {

						$params['items'][] = $resArray;

					}

				}

			}

		}

	}

	/**
	 * Helper to get flexform's items array for plugin "tx_dlf_toolbox"
	 *
	 * @access	public
	 *
	 * @param	array		&$params: An array with parameters
	 * @param	t3lib_TCEforms		&$pObj: The parent object
	 *
	 * @return	void
	 */
	public function itemsProcFunc_toolList(&$params, &$pObj) {

		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/plugins/toolbox/tools'] as $class => $label) {

			$params['items'][] = array ($GLOBALS['LANG']->sL($label), $class);

		}

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dlf/hooks/class.tx_dlf_tceforms.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dlf/hooks/class.tx_dlf_tceforms.php']);
}

?>