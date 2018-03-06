<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171218_101537_freeform_RemoveAssetsForeignKey extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
	    craft()
            ->db
            ->createCommand()
            ->dropForeignKey(
                'freeform_unfinalized_files',
                'assetId'
            );

		return true;
	}
}
