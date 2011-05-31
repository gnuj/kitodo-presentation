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
 * Module 'indexing' for the 'dlf' extension.
 *
 * @author	Sebastian Meyer <sebastian.meyer@slub-dresden.de>
 * @copyright	Copyright (c) 2011, Sebastian Meyer, SLUB Dresden
 * @package	TYPO3
 * @subpackage	tx_dlf
 * @access	public
 */
class tx_dlf_modIndexing extends tx_dlf_module {

	protected $modPath = 'indexing/';

	protected $buttonArray = array (
		'SHORTCUT' => '',
	);

	protected $markerArray = array (
		'CSH' => '',
		'MOD_MENU' => '',
		'CONTENT' => '',
	);

	/**
	 * This holds a list of documents to index
	 *
	 * @var	tx_dlf_list
	 * @access protected
	 */
	protected $list;

	/**
	 * Builds HTML form for selecting a collection
	 *
	 * @access	protected
	 *
	 * @return	string		The HTML output
	 */
	protected function getCollList() {

		// Get all available Solr cores.
		$_cores = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dlf_solrcores.label AS label,tx_dlf_solrcores.uid AS uid',
			'tx_dlf_solrcores',
			'tx_dlf_solrcores.pid=0'.tx_dlf_helper::whereClause('tx_dlf_solrcores'),
			'',
			'',
			''
		);

		// Get all available collections.
		$_collections = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dlf_collections.label AS label,tx_dlf_collections.uid AS uid',
			'tx_dlf_collections',
			'tx_dlf_collections.fe_cruser_id=0 AND tx_dlf_collections.pid='.intval($this->id).tx_dlf_helper::whereClause('tx_dlf_collections'),
			'',
			'tx_dlf_collections.label ASC',
			''
		);

		// TODO: Ändern!
		$this->doc->form = '<form name="'.$this->prefixId.'[form]" id="tx-dlf-modIndexing-form" action="" method="post">';

		$form = '<label for="tx-dlf-modIndexing-id">Kollektion:</label>';

		$form .= '<select id="tx-dlf-modIndexing-collection" name="'.$this->prefixId.'[collection]">';

		$form .= '<option value="0">Alle</option>';

		while ($_collection = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($_collections)) {

			$form .= '<option value="'.$_collection['uid'].'">'.htmlspecialchars($_collection['label']).'</option>';

		}

		$form .= '</select><br />';

		$form .= '<select id="tx-dlf-modIndexing-core" name="'.$this->prefixId.'[core]">';

		while ($_core = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($_cores)) {

			$form .= '<option value="'.$_core['uid'].'">'.htmlspecialchars($_core['label']).'</option>';

		}

		$form .= '</select><br />';

		$form .= '<input type="hidden" name="CMD" value="reindexDocs" />';

		$form .= '<input type="submit" name="'.$this->prefixId.'[submit]" value="'.$GLOBALS['LANG']->getLL('form.submit').'" />';

		return $form;

	}

	/**
	 * Builds HTML form for selecting a file
	 *
	 * @access	protected
	 *
	 * @return	string		The HTML output
	 */
	protected function getFileForm() {

		// Get all available Solr cores.
		$_cores = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dlf_solrcores.label AS label,tx_dlf_solrcores.uid AS uid',
			'tx_dlf_solrcores',
			'tx_dlf_solrcores.pid=0'.tx_dlf_helper::whereClause('tx_dlf_solrcores'),
			'',
			'',
			''
		);

		// TODO: Ändern!
		$this->doc->form = '<form name="'.$this->prefixId.'[form]" id="tx-dlf-modIndexing-form" action="" method="post">';

		$form = '<label for="tx-dlf-modIndexing-id">METS-Datei:</label>';

		$form .= '<input type="text" id="tx-dlf-modIndexing-id" name="'.$this->prefixId.'[id]" value="" /><br />';

		$form .= '<select id="tx-dlf-modIndexing-core" name="'.$this->prefixId.'[core]">';

		while ($_core = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($_cores)) {

			$form .= '<option value="'.$_core['uid'].'">'.htmlspecialchars($_core['label']).'</option>';

		}

		$form .= '</select><br />';

		$form .= '<input type="hidden" name="CMD" value="indexFile" />';

		$form .= '<input type="submit" name="'.$this->prefixId.'[submit]" value="'.$GLOBALS['LANG']->getLL('form.submit').'" />';

		return $form;

	}

	/**
	 * Iterates through list of documents and indexes them
	 *
	 * @access	protected
	 *
	 * @return	void
	 */
	protected function indexLoop() {

		// Get document from list.
		list ($uid, $title) = $this->list->remove(0);

		$this->list->save();

		// Save document to database and index.
		$doc = tx_dlf_document::getInstance($uid, 0, TRUE);

		if (!$doc->save($doc->pid, $this->data['core'])) {

			$_message = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				htmlspecialchars(sprintf($GLOBALS['LANG']->getLL('flash.documentNotSaved'), $title, $uid)),
				$GLOBALS['LANG']->getLL('flash.error', TRUE),
				t3lib_FlashMessage::ERROR,
				TRUE
			);

			t3lib_FlashMessageQueue::addMessage($_message);

		}

		// Give feedback about progress.
		$_message = t3lib_div::makeInstance(
			't3lib_FlashMessage',
			htmlspecialchars(sprintf($GLOBALS['LANG']->getLL('flash.documentsToGo'), $this->list->count)),
			$GLOBALS['LANG']->getLL('flash.running', TRUE),
			t3lib_FlashMessage::INFO,
			TRUE
		);

		$this->markerArray['CONTENT'] .= $_message->render();

		// Start next loop.
		$this->markerArray['CONTENT'] .= '<script type="text/javascript">window.location.href=unescape("'.t3lib_div::rawUrlEncodeJS(t3lib_div::locationHeaderUrl(t3lib_div::linkThisScript(array ('id' => $this->id, 'CMD' => 'indexLoop', $this->prefixId => array ('core' => $this->data['core']), 'random' => uniqid())))).'");</script>';

		$this->printContent();

		exit;

	}

	/**
	 * Main function of the module
	 *
	 * @access	public
	 *
	 * @return	void
	 */
	public function main() {

		// Is the user allowed to access this page?
		$access = is_array($this->pageInfo) || $GLOBALS['BE_USER']->isAdmin();

		if ($this->id && $access) {

			// Increase max_execution_time and max_input_time for large documents.
			if (!ini_get('safe_mode')) {

				ini_set('max_execution_time', '0');

				ini_set('max_input_time', '-1');

			}

			switch ($this->CMD) {

				case 'indexFile':

					if (!empty($this->data['id']) && !empty($this->data['core'])) {

						// Save document to database and index.
						$doc = tx_dlf_document::getInstance($this->data['id'], 0, TRUE);

						if (!$doc->save($this->id, $this->data['core'])) {

							$_message = t3lib_div::makeInstance(
								't3lib_FlashMessage',
								htmlspecialchars(sprintf($GLOBALS['LANG']->getLL('flash.fileNotSaved'), $this->data['id'])),
								$GLOBALS['LANG']->getLL('flash.error', TRUE),
								t3lib_FlashMessage::ERROR,
								FALSE
							);

							t3lib_FlashMessageQueue::addMessage($_message);

						}

					}

					break;

				case 'reindexDocs':

					if (!empty($this->data['core'])) {

						if (!empty($this->data['collection'])) {

							// Get all documents in this collection.
							$_result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
								'tx_dlf_documents.title AS title,tx_dlf_documents.uid AS uid',
								'tx_dlf_documents',
								'tx_dlf_relations',
								'tx_dlf_collections',
								'AND tx_dlf_documents.partof=0 AND tx_dlf_documents.pid='.intval($this->id).' AND tx_dlf_collections.uid='.intval($this->data['collection']).tx_dlf_helper::whereClause('tx_dlf_documents').tx_dlf_helper::whereClause('tx_dlf_collections'),
								'',
								'',
								''
							);

						} else {

							// Get all documents.
							$_result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'tx_dlf_documents.title AS title,tx_dlf_documents.uid AS uid',
								'tx_dlf_documents',
								'tx_dlf_documents.partof=0 AND tx_dlf_documents.pid='.intval($this->id).tx_dlf_helper::whereClause('tx_dlf_documents'),
								'',
								'',
								''
							);

						}

						// Save them as a list object in user's session.
						$elements = array ();

						while ($resArray = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($_result)) {

							$elements[] = array ($resArray['uid'], $resArray['title']);

						}

						$this->list = t3lib_div::makeInstance('tx_dlf_list', $elements);

						// Start index looping.
						if ($this->list->count) {

							$this->indexLoop();

						}

					}

					break;

				case 'indexLoop':

					// Refresh user's session to prevent session timeout.
					$_beuser = tx_dlf_helper::getBeUser();

					$_beuser->fetchUserSession();

					// Get document list from user's session.
					$this->list = t3lib_div::makeInstance('tx_dlf_list');

					// Continue index looping.
					if ($this->list->count && !empty($this->data['core'])) {

						$this->indexLoop();

					} else {

						$_message = t3lib_div::makeInstance(
							't3lib_FlashMessage',
							$GLOBALS['LANG']->getLL('flash.seeLog', TRUE),
							$GLOBALS['LANG']->getLL('flash.done', TRUE),
							t3lib_FlashMessage::OK,
							TRUE
						);

						t3lib_FlashMessageQueue::addMessage($_message);

					}

					break;

			}


			$this->markerArray['CONTENT'] .= t3lib_FlashMessageQueue::renderFlashMessages();

			switch ($this->MOD_SETTINGS['function']) {

				case 'indexFile':

					$this->markerArray['CONTENT'] .= $this->getFileForm();

					break;

				case 'reindexDoc':

					$this->markerArray['CONTENT'] .= $this->getDocList();

					break;

				case 'reindexDocs':

					$this->markerArray['CONTENT'] .= $this->getCollList();

					break;

			}

		} else {

			// TODO: Ändern!
			$this->markerArray['CONTENT'] .= 'You are not allowed to access this page or have not selected a page, yet.';

		}

		$this->printContent();

	}

	/**
	 * Sets the module's MOD_MENU configuration
	 *
	 * @access	protected
	 *
	 * @return	void
	 */
	protected function setMOD_MENU() {

		$this->MOD_MENU = array (
			'function' => array (
				'indexFile' => $GLOBALS['LANG']->getLL('function.indexFile'),
				//'reindexDoc' => $GLOBALS['LANG']->getLL('function.reindexDoc'),
				'reindexDocs' => $GLOBALS['LANG']->getLL('function.reindexDocs'),
			)
		);

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dlf/modules/indexing/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dlf/modules/indexing/index.php']);
}

$SOBE = t3lib_div::makeInstance('tx_dlf_modIndexing');

$SOBE->main();

?>