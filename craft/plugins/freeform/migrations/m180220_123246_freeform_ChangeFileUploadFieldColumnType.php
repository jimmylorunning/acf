<?php

namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m180220_123246_freeform_ChangeFileUploadFieldColumnType extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
	    $ids = craft()->db
            ->createCommand()
            ->select('id')
            ->from('freeform_fields')
            ->where('type = :type', ['type' => 'file'])
            ->queryColumn();

	    foreach ($ids as $id) {
            $column = Freeform_SubmissionRecord::getFieldColumnName($id);
            $this->alterColumn('freeform_submissions', $column, 'text null');
        }

		return true;
	}
}
